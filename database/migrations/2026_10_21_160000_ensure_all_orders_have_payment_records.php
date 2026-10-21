<?php

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders') || !Schema::hasTable('payments')) {
            return;
        }

        $orders = Order::with('items')->get();

        foreach ($orders as $order) {
            $hasPayment = Payment::where('payable_type', Order::class)
                ->where('payable_id', $order->id)
                ->exists();

            if (!$hasPayment) {
                $isCompleted = ($order->payment_status === Order::PAYMENT_STATUS_PAID || $order->status === Order::STATUS_DELIVERED);
                $prefix = $isCompleted ? 'KH-TXN-CMP' : ($order->payment_method === 'bank_transfer' ? 'KH-TXN-BNK' : 'KH-TXN-ORD');

                Payment::create([
                    'payable_type' => Order::class,
                    'payable_id' => $order->id,
                    'user_id' => $order->buyer_id,
                    'payment_type' => Payment::TYPE_ORDER,
                    'payment_method' => $order->payment_method ?: Payment::METHOD_COD,
                    'amount' => (float) $order->total_amount,
                    'currency' => 'BDT',
                    'transaction_id' => Payment::generateTransactionId($prefix),
                    'status' => $isCompleted ? Payment::STATUS_COMPLETED : Payment::STATUS_PENDING,
                    'paid_at' => $isCompleted ? ($order->updated_at ?? now()) : null,
                    'notes' => "অফিসিয়াল লেনদেন ও সিকিউরিটি রেকর্ড: অর্ডার #{$order->id} (ট্র্যাকিং: {$order->tracking_code})",
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep audit records intact
    }
};
