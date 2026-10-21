<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Services\SslCommerzPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected SslCommerzPaymentService $gatewayService;

    public function __construct(SslCommerzPaymentService $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * Interactive Sandbox Payment Simulator for university project demonstration.
     */
    public function sandboxSimulator(Request $request)
    {
        $tranId = $request->query('tran_id');
        $payment = Payment::where('transaction_id', $tranId)->firstOrFail();

        $payable = $payment->payable;

        return view('payment.sandbox_simulator', compact('payment', 'payable'));
    }

    /**
     * Execute simulated sandbox transaction action.
     */
    public function executeSandboxAction(Request $request)
    {
        $validated = $request->validate([
            'tran_id' => 'required|string',
            'action' => 'required|in:success,fail,cancel',
            'channel' => 'nullable|string',
        ]);

        $payload = [
            'tran_id' => $validated['tran_id'],
            'val_id' => 'SIMULATED_VAL_' . $validated['tran_id'],
            'card_type' => strtoupper($validated['channel'] ?? 'BKASH_SANDBOX'),
            'bank_tran_id' => 'SIM_BANK_' . strtoupper(substr(md5(uniqid()), 0, 10)),
        ];

        $res = $this->gatewayService->processCallback($payload, $validated['action']);

        $payment = Payment::where('transaction_id', $validated['tran_id'])->first();
        if ($payment && $payment->payable instanceof Order) {
            $redirectRoute = route('orders.show', $payment->payable_id);
        } else {
            $redirectRoute = route('plans.index');
        }

        if ($validated['action'] === 'success') {
            return redirect($redirectRoute)->with('success', 'ডিজিটাল পেমেন্ট সফলভাবে সম্পন্ন ও সার্ভারে যাচাই করা হয়েছে! ট্রানজেকশন আইডি: ' . $validated['tran_id']);
        } elseif ($validated['action'] === 'cancel') {
            return redirect($redirectRoute)->with('info', 'পেমেন্ট বাতিল করা হয়েছে। আপনি অন্য কোনো মেথডে পুনরায় চেষ্টা করতে পারেন।');
        } else {
            return redirect($redirectRoute)->with('error', 'পেমেন্ট ব্যর্থ হয়েছে। অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স আছে কিনা যাচাই করুন।');
        }
    }

    /**
     * SSLCOMMERZ gateway success return callback.
     */
    public function sslcommerzSuccess(Request $request)
    {
        $res = $this->gatewayService->processCallback($request->all(), 'success');

        if (!$res['success']) {
            return redirect()->route('cart.index')->with('error', $res['message'] ?? 'পেমেন্ট ভেরিফিকেশন ব্যর্থ হয়েছে।');
        }

        $payment = $res['payment'];
        if ($payment->payable instanceof Order) {
            return redirect()->route('orders.show', $payment->payable_id)
                ->with('success', 'পেমেন্ট সফলভাবে যাচাই হয়েছে! ট্রানজেকশন আইডি: ' . $payment->transaction_id);
        }

        return redirect()->route('plans.index')->with('success', 'সাবস্ক্রিপশন সফলভাবে সক্রিয় করা হয়েছে!');
    }

    /**
     * SSLCOMMERZ gateway fail callback.
     */
    public function sslcommerzFail(Request $request)
    {
        $this->gatewayService->processCallback($request->all(), 'fail');

        $tranId = $request->input('tran_id');
        $payment = Payment::where('transaction_id', $tranId)->first();

        if ($payment && $payment->payable instanceof Order) {
            return redirect()->route('orders.show', $payment->payable_id)
                ->with('error', 'পেমেন্ট ব্যর্থ হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন বা ক্যাশ অন ডেলিভারি বেছে নিন।');
        }

        return redirect()->route('plans.index')->with('error', 'সাবস্ক্রিপশন পেমেন্ট ব্যর্থ হয়েছে।');
    }

    /**
     * SSLCOMMERZ gateway cancel callback.
     */
    public function sslcommerzCancel(Request $request)
    {
        $this->gatewayService->processCallback($request->all(), 'cancel');

        $tranId = $request->input('tran_id');
        $payment = Payment::where('transaction_id', $tranId)->first();

        if ($payment && $payment->payable instanceof Order) {
            return redirect()->route('orders.show', $payment->payable_id)
                ->with('info', 'পেমেন্ট বাতিল করা হয়েছে।');
        }

        return redirect()->route('plans.index')->with('info', 'সাবস্ক্রিপশন পেমেন্ট বাতিল করা হয়েছে।');
    }

    /**
     * SSLCOMMERZ IPN (Instant Payment Notification) listener.
     */
    public function sslcommerzIpn(Request $request)
    {
        $status = strtolower($request->input('status', ''));
        $intended = in_array($status, ['valid', 'validated']) ? 'success' : 'fail';

        $res = $this->gatewayService->processCallback($request->all(), $intended);

        return response()->json(['status' => $res['success'] ? 'OK' : 'ERROR', 'message' => $res['message'] ?? 'IPN received']);
    }
}
