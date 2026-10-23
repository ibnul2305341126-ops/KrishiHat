<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTrackingEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.orders.index');
        }

        if ($user->role === 'farmer') {
            return redirect()->route('farmer.dashboard');
        }

        abort_unless($user->role === 'buyer', 403, 'শুধুমাত্র ক্রেতারা তাদের অর্ডার তালিকা দেখতে পারেন।');

        $orders = Order::with(['items.product', 'trackingEvents'])
            ->where('buyer_id', $user->id)
            ->latest()
            ->get();

        foreach ($orders as $order) {
            $this->ensureTrackingCodeAndInitialEvent($order);
        }

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.orders.show', $order);
        }

        if ($user->role === 'farmer' && $order->items()->where('farmer_id', $user->id)->exists()) {
            return redirect()->route('farmer.orders.show', $order);
        }

        abort_unless($user->role === 'buyer' && $order->buyer_id === $user->id, 403, 'এই অর্ডারের বিস্তারিত তথ্য দেখার অনুমতি আপনার নেই।');

        $this->ensureTrackingCodeAndInitialEvent($order);
        $order->load(['items.product.farmer', 'trackingEvents.actor']);

        return view('orders.show', compact('order'));
    }

    public function trackingData(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        $isBuyer = $user->id === $order->buyer_id;
        $isFarmer = $order->items()->where('farmer_id', $user->id)->exists();
        $isAdmin = $user->role === 'admin';

        abort_unless($isBuyer || $isFarmer || $isAdmin, 403);

        $this->ensureTrackingCodeAndInitialEvent($order);
        $order->load(['trackingEvents.actor']);

        return response()->json([
            'order_id' => $order->id,
            'tracking_code' => $order->tracking_code,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'driver_name' => $order->driver_name,
            'driver_phone' => $order->driver_phone,
            'vehicle_number' => $order->vehicle_number,
            'current_location_text' => $order->current_location_text,
            'current_latitude' => $order->current_latitude,
            'current_longitude' => $order->current_longitude,
            'estimated_delivery_at' => $order->estimated_delivery_at?->format('M j, Y g:i A'),
            'updated_at' => $order->updated_at->format('M j, Y g:i A'),
            'events' => $order->trackingEvents->map(fn ($event) => [
                'id' => $event->id,
                'status' => $event->status,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'time' => $event->created_at->format('M j, Y · g:i A'),
                'actor' => $event->actor?->name ?? 'System',
            ]),
        ]);
    }

    public function farmerShow(Request $request, Order $order)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($order->items()->where('farmer_id', $request->user()->id)->exists(), 403);

        $this->ensureTrackingCodeAndInitialEvent($order);
        $order->load([
            'buyer',
            'items' => fn ($query) => $query->where('farmer_id', $request->user()->id)->with('product'),
            'trackingEvents.actor',
        ]);

        $statuses = Order::knownStatuses();
        $canUpdateStatus = count($statuses) > 1 && ! $order->items()->where('farmer_id', '!=', $request->user()->id)->exists();

        return view('farmer.orders.show', compact('order', 'statuses', 'canUpdateStatus'));
    }

    public function updateFarmerStatus(Request $request, Order $order)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($order->items()->where('farmer_id', $request->user()->id)->exists(), 403);
        abort_if($order->items()->where('farmer_id', '!=', $request->user()->id)->exists(), 403);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(Order::knownStatuses())],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'driver_name' => ['nullable', 'string', 'max:100'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
        ]);

        $status = $validated['status'];
        $oldStatus = $order->status;

        $updateData = ['status' => $status];
        if (!empty($validated['driver_name'])) $updateData['driver_name'] = $validated['driver_name'];
        if (!empty($validated['driver_phone'])) $updateData['driver_phone'] = $validated['driver_phone'];
        if (!empty($validated['vehicle_number'])) $updateData['vehicle_number'] = $validated['vehicle_number'];
        if (!empty($validated['location'])) $updateData['current_location_text'] = $validated['location'];

        $order->update($updateData);

        $defaultTitles = [
            Order::STATUS_PENDING => 'অর্ডার গৃহীত হয়েছে (Order Placed)',
            Order::STATUS_CONFIRMED => 'অর্ডার নিশ্চিত করা হয়েছে (Order Confirmed)',
            Order::STATUS_PROCESSING => 'ফসল বাছাই ও প্যাকেজিং চলছে (Processing & Packing)',
            Order::STATUS_SHIPPED => 'সরবরাহকারী গাড়িতে লোড হয়েছে (Shipped / In Transit)',
            Order::STATUS_DELIVERED => 'ডেলিভারি সম্পন্ন হয়েছে (Delivered)',
            Order::STATUS_CANCELLED => 'অর্ডার বাতিল করা হয়েছে (Cancelled)',
        ];

        $eventTitle = $validated['title'] ?? ($defaultTitles[$status] ?? 'Status updated to ' . ucfirst($status));
        $eventDesc = $validated['description'] ?? ("Order status updated from " . ucfirst($oldStatus) . " to " . ucfirst($status) . ".");

        $order->recordTrackingEvent(
            $status,
            $eventTitle,
            $eventDesc,
            $validated['location'] ?? $order->current_location_text ?? $order->delivery_address,
            actorId: $request->user()->id
        );

        return redirect()->route('farmer.orders.show', $order)->with('success', 'Order status updated.');
    }

    private function ensureTrackingCodeAndInitialEvent(Order $order): void
    {
        if (! $order->tracking_code) {
            $order->tracking_code = Order::generateTrackingCode((int) $order->id);
            $order->saveQuietly();
        }

        if ($order->trackingEvents()->count() === 0) {
            $order->recordTrackingEvent(
                $order->status,
                'অর্ডার গ্রহণ করা হয়েছে (Order Placed)',
                'অর্ডার সফলভাবে ডাটাবেজে রেকর্ড করা হয়েছে।',
                $order->delivery_address
            );
        }
    }
}