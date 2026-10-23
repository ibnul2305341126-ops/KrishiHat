<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Notifications\WorkflowNotification;

class OfferController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_unless($request->user()->role === 'buyer', 403);
        abort_if($product->farmer_id === $request->user()->id, 403);

        $minOrder = (int) ($product->min_order_quantity ?? 1);
        $data = $request->validate([
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99'],
            'quantity' => ['required', 'integer', 'min:' . $minOrder, 'max:' . $product->stock],
        ], [
            'quantity.min' => "এই পণ্যটিতে দরপ্রস্তাবের সর্বনিম্ন পরিমাণ {$minOrder} {$product->unit} হতে হবে।",
        ]);

        DB::transaction(function () use ($request, $product, $data): Offer {
            $lockedProduct = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $this->assertAvailableQuantity($lockedProduct, (int) $data['quantity']);

            $offer = Offer::create([
                'product_id' => $lockedProduct->id,
                'buyer_id' => $request->user()->id,
                'farmer_id' => $lockedProduct->farmer_id,
                'initial_price' => $data['price'],
                'current_price' => $data['price'],
                'quantity' => $data['quantity'],
                'status' => Offer::STATUS_PENDING,
            ]);

            $this->recordEvent($offer, $request, 'offered', $data['price']);
            $offer->farmer->notify(new WorkflowNotification('New price offer for '.$lockedProduct->name, route('farmer.dashboard').'#farmer-offers'));
            return $offer;
        }, 3);

        return back()->with('success', 'Your offer was sent to the farmer.');
    }

    public function respondAsFarmer(Request $request, Offer $offer)
    {
        abort_unless($request->user()->role === 'farmer', 403);
        abort_unless($offer->farmer_id === $request->user()->id, 403);

        $data = $request->validate([
            'action' => ['required', Rule::in(['accept', 'reject', 'counter'])],
            'counter_price' => [
                Rule::requiredIf($request->input('action') === 'counter'),
                'nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:99999999.99',
            ],
        ]);

        DB::transaction(function () use ($request, $offer, $data): void {
            $lockedOffer = Offer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedOffer->farmer_id === $request->user()->id, 403);
            $this->requireStatus($lockedOffer, Offer::STATUS_PENDING);

            if ($data['action'] !== 'reject') {
                $product = Product::query()->whereKey($lockedOffer->product_id)->lockForUpdate()->firstOrFail();
                $this->assertAvailableQuantity($product, $lockedOffer->quantity, $lockedOffer->id);
            }

            if ($data['action'] === 'accept') {
                $lockedOffer->update(['status' => Offer::STATUS_ACCEPTED]);
                $this->recordEvent($lockedOffer, $request, 'accepted', $lockedOffer->current_price);
            } elseif ($data['action'] === 'reject') {
                $lockedOffer->update(['status' => Offer::STATUS_REJECTED]);
                $this->recordEvent($lockedOffer, $request, 'rejected', $lockedOffer->current_price);
            } else {
                $lockedOffer->update([
                    'current_price' => $data['counter_price'],
                    'status' => Offer::STATUS_COUNTERED,
                ]);
                $this->recordEvent($lockedOffer, $request, 'countered', $data['counter_price']);
            }
        }, 3);

        $offer->buyer->notify(new WorkflowNotification('The farmer responded to your offer for '.$offer->product->name, route('buyer.dashboard').'#my-offers'));

        return back()->with('success', match ($data['action']) {
            'accept' => 'Offer accepted. No order has been placed.',
            'reject' => 'Offer rejected.',
            default => 'Counter-offer sent to the buyer.',
        });
    }

    public function respondToCounterOffer(Request $request, Offer $offer)
    {
        abort_unless($request->user()->role === 'buyer', 403);
        abort_unless($offer->buyer_id === $request->user()->id, 403);

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accept', 'reject'])],
        ]);

        DB::transaction(function () use ($request, $offer, $data): void {
            $lockedOffer = Offer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedOffer->buyer_id === $request->user()->id, 403);
            $this->requireStatus($lockedOffer, Offer::STATUS_COUNTERED);

            if ($data['decision'] === 'accept') {
                $product = Product::query()->whereKey($lockedOffer->product_id)->lockForUpdate()->firstOrFail();
                $this->assertAvailableQuantity($product, $lockedOffer->quantity, $lockedOffer->id);

                $status = Offer::STATUS_ACCEPTED;
                $action = 'counter_accepted';
            } else {
                $status = Offer::STATUS_REJECTED;
                $action = 'counter_rejected';
            }

            $lockedOffer->update(['status' => $status]);
            $this->recordEvent($lockedOffer, $request, $action, $lockedOffer->current_price);
        }, 3);

        $offer->farmer->notify(new WorkflowNotification('The buyer responded to your counter-offer for '.$offer->product->name, route('farmer.dashboard').'#farmer-offers'));

        return back()->with('success', $data['decision'] === 'accept'
            ? 'Counter-offer accepted. No order has been placed.'
            : 'Counter-offer rejected.');
    }

    public function withdraw(Request $request, Offer $offer)
    {
        abort_unless($request->user()->role === 'buyer', 403);
        abort_unless($offer->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($request, $offer): void {
            $lockedOffer = Offer::query()->whereKey($offer->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedOffer->buyer_id === $request->user()->id, 403);
            $this->requireStatus($lockedOffer, Offer::STATUS_PENDING);

            $lockedOffer->update(['status' => Offer::STATUS_WITHDRAWN]);
            $this->recordEvent($lockedOffer, $request, 'withdrawn', $lockedOffer->current_price);
        }, 3);

        return back()->with('success', 'Offer withdrawn.');
    }

    private function assertAvailableQuantity(Product $product, int $quantity, ?int $excludeOfferId = null): void
    {
        $acceptedOffers = Offer::query()
            ->where('product_id', $product->id)
            ->where('status', Offer::STATUS_ACCEPTED)
            ->whereDoesntHave('order')
            ->when($excludeOfferId, fn ($query) => $query->whereKeyNot($excludeOfferId))
            ->sum('quantity');

        if ($product->stock - $acceptedOffers < $quantity) {
            throw ValidationException::withMessages([
                'offer' => 'There is not enough available stock for this offer.',
            ]);
        }
    }

    private function requireStatus(Offer $offer, string $expected): void
    {
        if ($offer->status !== $expected) {
            throw ValidationException::withMessages([
                'offer' => 'This offer has already changed and cannot be acted on again.',
            ]);
        }
    }

    private function recordEvent(Offer $offer, Request $request, string $action, ?string $price): void
    {
        OfferEvent::create([
            'offer_id' => $offer->id,
            'actor_id' => $request->user()->id,
            'actor_role' => $request->user()->role,
            'action' => $action,
            'price' => $price,
        ]);
    }
}
