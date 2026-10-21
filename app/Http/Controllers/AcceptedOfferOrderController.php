<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptedOfferOrderController extends Controller
{
    public function store(Request $request, Offer $offer)
    {
        abort_unless($request->user()->role === 'buyer' && $offer->buyer_id === $request->user()->id, 403);
        $data = $request->validate(['delivery_address' => ['required','string','max:2000'], 'phone' => ['required','string','max:30']]);

        $order = DB::transaction(function () use ($request, $offer, $data) {
            $locked = Offer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->buyer_id === $request->user()->id, 403);
            if ($existing = Order::where('accepted_offer_id', $locked->id)->first()) return $existing;
            if ($locked->status !== Offer::STATUS_ACCEPTED) throw ValidationException::withMessages(['offer' => 'Only accepted offers can be ordered.']);
            $product = Product::query()->whereKey($locked->product_id)->lockForUpdate()->firstOrFail();
            $otherAcceptedQuantity = Offer::query()->where('product_id', $product->id)->where('status', Offer::STATUS_ACCEPTED)
                ->whereKeyNot($locked->id)->whereDoesntHave('order')->sum('quantity');
            if ($product->stock - $otherAcceptedQuantity < $locked->quantity) throw ValidationException::withMessages(['offer' => 'The agreed quantity is no longer available.']);
            $subtotal = round((float) $locked->current_price * $locked->quantity, 2);
            $order = Order::create(['buyer_id' => $locked->buyer_id, 'accepted_offer_id' => $locked->id, 'total_amount' => $subtotal,
                'status' => Order::INITIAL_STATUS, 'delivery_address' => $data['delivery_address'], 'phone' => $data['phone']]);
            $order->update(['tracking_code' => Order::generateTrackingCode((int) $order->id)]);
            $order->recordTrackingEvent(
                Order::INITIAL_STATUS,
                'দরদাম অনুযায়ী অর্ডার সম্পন্ন (Offer Order Placed)',
                'গৃহীত দরদাম অনুযায়ী সফলভাবে অর্ডার প্লেস করা হয়েছে।',
                $data['delivery_address'],
                actorId: $request->user()->id
            );
            OrderItem::create(['order_id' => $order->id, 'product_id' => $product->id, 'farmer_id' => $locked->farmer_id,
                'quantity' => $locked->quantity, 'unit_price' => $locked->current_price, 'subtotal' => $subtotal]);
            $product->decrement('stock', $locked->quantity);

            \App\Models\Payment::create([
                'payable_type' => Order::class,
                'payable_id' => $order->id,
                'user_id' => $locked->buyer_id,
                'payment_type' => \App\Models\Payment::TYPE_ORDER,
                'payment_method' => \App\Models\Payment::METHOD_COD,
                'amount' => $subtotal,
                'currency' => 'BDT',
                'transaction_id' => \App\Models\Payment::generateTransactionId('KH-OFR'),
                'status' => \App\Models\Payment::STATUS_PENDING,
                'notes' => "গৃহীত দরদাম (Offer #{$locked->id}) অনুযায়ী সফল অর্ডার ও পেমেন্ট অডিট রেকর্ড।",
            ]);

            return $order;
        }, 3);

        return redirect()->route('orders.show', $order)->with('success', 'Accepted offer order placed.');
    }
}
