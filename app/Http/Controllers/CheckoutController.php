<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    // Checkout page
    public function index()
    {
        $user = auth()->user();
        if ($user->role !== 'buyer') {
            return redirect()->route('cart.index')
                ->with('error', 'অর্ডার সম্পন্ন করতে ক্রেতা (Buyer) অ্যাকাউন্টে লগইন করুন। খামারি বা অ্যাডমিন অ্যাকাউন্ট দিয়ে সরাসরি চেকআউট করা সম্ভব নয়।');
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'তোমার Cart খালি।');
        }

        $products = Product::whereIn('id', array_keys($cart))->get();

        if ($products->count() !== count($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Cart-এর কোনো পণ্য আর পাওয়া যাচ্ছে না।');
        }

        $total = 0;

        foreach ($products as $product) {
            $total += $product->price * (int) $cart[$product->id];
        }

        return view('checkout', compact('products', 'cart', 'total'));
    }

    // Place order
    public function store(Request $request)
    {
        abort_unless($request->user()->role === 'buyer', 403);

        $data = $request->validate([
            'delivery_address' => 'required|string|max:2000',
            'phone' => 'required|string|max:30',
            'payment_method' => 'nullable|string|in:cod,sslcommerz,bkash,nagad,card,bank_transfer',
            'bank_name' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'bank_account_no' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
            'bank_tran_ref' => 'required_if:payment_method,bank_transfer|nullable|string|max:100',
        ]);

        $paymentMethod = $data['payment_method'] ?? 'cod';

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('error', 'তোমার Cart খালি।');
        }

        $order = DB::transaction(function () use ($cart, $data, $request, $paymentMethod) {
            $products = Product::whereIn('id', array_keys($cart))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart-এর কোনো পণ্য পাওয়া যাচ্ছে না।',
                ]);
            }

            $total = 0;
            $items = [];

            foreach ($products as $product) {
                $quantity = filter_var(
                    $cart[$product->id] ?? null,
                    FILTER_VALIDATE_INT
                );

                $minOrder = (int) ($product->min_order_quantity ?? 1);
                if (
                    !$quantity ||
                    $quantity < $minOrder
                ) {
                    throw ValidationException::withMessages([
                        'cart' => "{$product->name} পণ্যের সর্বনিম্ন ক্রয়ের পরিমাণ {$minOrder} {$product->unit} পূরণ করা হয়নি।",
                    ]);
                }

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => $product->name .
                            ' পণ্যের পর্যাপ্ত Stock নেই।',
                    ]);
                }

                if (!$product->farmer_id) {
                    throw ValidationException::withMessages([
                        'cart' => $product->name .
                            ' পণ্যের Farmer নির্ধারিত নেই।',
                    ]);
                }

                $subtotal = round(
                    (float) $product->price * $quantity,
                    2
                );

                $total += $subtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $initialPaymentStatus = ($paymentMethod === 'cod')
                ? Order::PAYMENT_STATUS_UNPAID
                : Order::PAYMENT_STATUS_PENDING;

            $order = Order::create([
                'buyer_id' => $request->user()->id,
                'total_amount' => $total,
                'status' => Order::INITIAL_STATUS,
                'payment_method' => $paymentMethod,
                'payment_status' => $initialPaymentStatus,
                'delivery_address' => $data['delivery_address'],
                'phone' => $data['phone'],
            ]);

            $order->update([
                'tracking_code' => Order::generateTrackingCode((int) $order->id),
            ]);

            $order->recordTrackingEvent(
                Order::INITIAL_STATUS,
                'অর্ডার গ্রহণ করা হয়েছে (Order Placed)',
                'ক্রেতা সফলভাবে অর্ডার প্লেস করেছেন। পেমেন্ট পদ্ধতি: ' . $order->payment_method_label,
                $data['delivery_address'],
                actorId: $request->user()->id
            );

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'farmer_id' => $item['product']->farmer_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $item['product']->decrement(
                    'stock',
                    $item['quantity']
                );
            }

            // Create initial payment audit record
            if ($paymentMethod === 'cod') {
                \App\Models\Payment::create([
                    'payable_type' => Order::class,
                    'payable_id' => $order->id,
                    'user_id' => $request->user()->id,
                    'payment_type' => \App\Models\Payment::TYPE_ORDER,
                    'payment_method' => \App\Models\Payment::METHOD_COD,
                    'amount' => $total,
                    'currency' => 'BDT',
                    'transaction_id' => \App\Models\Payment::generateTransactionId('KH-COD'),
                    'status' => \App\Models\Payment::STATUS_PENDING,
                    'notes' => 'ক্যাশ অন ডেলিভারি — পণ্য ডেলিভারি সম্পন্ন হলে নগদ অর্থ গ্রহণ সাপেক্ষে পরিশোধিত হবে।',
                ]);
            } elseif ($paymentMethod === 'bank_transfer') {
                \App\Models\Payment::create([
                    'payable_type' => Order::class,
                    'payable_id' => $order->id,
                    'user_id' => $request->user()->id,
                    'payment_type' => \App\Models\Payment::TYPE_ORDER,
                    'payment_method' => \App\Models\Payment::METHOD_BANK_TRANSFER,
                    'amount' => $total,
                    'currency' => 'BDT',
                    'transaction_id' => $data['bank_tran_ref'] ?? \App\Models\Payment::generateTransactionId('KH-BNK'),
                    'status' => \App\Models\Payment::STATUS_PENDING,
                    'payment_details' => [
                        'bank_name' => $data['bank_name'] ?? null,
                        'bank_account_no' => $data['bank_account_no'] ?? null,
                        'bank_tran_ref' => $data['bank_tran_ref'] ?? null,
                    ],
                    'notes' => 'ম্যানুয়াল ব্যাংক ট্রান্সফার — অ্যাডমিন ভেরিফিকেশন সাপেক্ষে কার্যকর হবে।',
                ]);
            }

            return $order;
        });

        session()->forget('cart');

        // If online gateway method selected (SSLCOMMERZ, bKash, Nagad, Card)
        if (in_array($paymentMethod, ['sslcommerz', 'bkash', 'nagad', 'card'], true)) {
            $gatewayService = new \App\Services\SslCommerzPaymentService();
            $session = $gatewayService->initiateOrderPayment($order, $paymentMethod);
            if (!empty($session['redirect_url'])) {
                return redirect($session['redirect_url']);
            }
        }

        $noticeMsg = ($paymentMethod === 'bank_transfer')
            ? 'আপনার অর্ডার গ্রহণ করা হয়েছে এবং ব্যাংক ট্রান্সফার ভেরিফিকেশনের জন্য অপেক্ষমান। Order ID: #' . $order->id . ' | ট্র্যাকিং কোড: ' . $order->tracking_code
            : 'আপনার Order সফলভাবে সম্পন্ন হয়েছে! Order ID: #' . $order->id . ' | ট্র্যাকিং কোড: ' . $order->tracking_code;

        return redirect()->route('orders.show', $order)->with('success', $noticeMsg);
    }
}