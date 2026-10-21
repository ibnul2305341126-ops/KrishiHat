<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_submit_an_offer_and_it_is_saved_without_creating_an_order(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->productFor($farmer);

        $this->actingAs($buyer)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Make Offer')
            ->assertSee('Message Farmer / Chat with Farmer')
            ->assertSee('name="price"', false)
            ->assertSee('max="' . $product->stock . '"', false)
            ->assertSee(route('offers.store', $product));

        $this->post(route('offers.store', $product), ['price' => '42.50', 'quantity' => 3])
            ->assertRedirect();

        $offer = Offer::query()->sole();
        $this->assertSame($buyer->id, $offer->buyer_id);
        $this->assertSame($farmer->id, $offer->farmer_id);
        $this->assertSame($product->id, $offer->product_id);
        $this->assertSame('42.50', $offer->initial_price);
        $this->assertSame('42.50', $offer->current_price);
        $this->assertSame(3, $offer->quantity);
        $this->assertSame(Offer::STATUS_PENDING, $offer->status);
        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $offer->id,
            'actor_id' => $buyer->id,
            'action' => 'offered',
            'price' => '42.50',
        ]);
        $this->assertDatabaseCount('orders', 0);

        $this->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('My price offers')
            ->assertSee('Pending')
            ->assertSee('Negotiation history');

        $this->actingAs($farmer)->delete(route('products.destroy', $product))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_offer_price_quantity_and_own_product_rules_are_enforced(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->productFor($farmer, 4);

        $this->actingAs($buyer)->post(route('offers.store', $product), ['price' => '0', 'quantity' => 1])
            ->assertSessionHasErrors('price');
        $this->post(route('offers.store', $product), ['price' => '40', 'quantity' => 5])
            ->assertSessionHasErrors('quantity');
        $this->post(route('offers.store', $product), ['price' => '40.001', 'quantity' => 1])
            ->assertSessionHasErrors('price');

        $this->assertDatabaseCount('offers', 0);

        $sameOwnerBuyer = User::factory()->create(['role' => 'buyer']);
        $ownProduct = $this->productFor($sameOwnerBuyer);
        $this->actingAs($sameOwnerBuyer)->post(route('offers.store', $ownProduct), ['price' => '40', 'quantity' => 1])
            ->assertForbidden();
        $this->actingAs($farmer)->post(route('offers.store', $product), ['price' => '40', 'quantity' => 1])
            ->assertForbidden();
        $this->assertDatabaseCount('offers', 0);
    }

    public function test_product_detail_hides_buyer_actions_for_owners_and_prompts_guests_to_register_or_log_in(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $product = $this->productFor($farmer);

        $this->actingAs($farmer)->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Make Offer')
            ->assertDontSee('Message Farmer / Chat with Farmer');

        auth()->logout();
        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Log in as a buyer to make an offer or chat privately with this farmer.')
            ->assertSee(route('login'))
            ->assertSee(route('register'));
    }

    public function test_farmer_sees_and_responds_only_to_offers_for_their_products(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'name' => 'Farmer One']);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer', 'name' => 'Buyer One']);
        $otherBuyer = User::factory()->create(['role' => 'buyer', 'name' => 'Buyer Two']);
        $product = $this->productFor($farmer, 8, 'Farmer One Rice');
        $otherProduct = $this->productFor($otherFarmer, 8, 'Farmer Two Beans');
        $ownOffer = $this->offerFor($buyer, $product, 2, '35.00');
        $otherOffer = $this->offerFor($otherBuyer, $otherProduct, 1, '20.00');

        $this->actingAs($farmer)->get(route('farmer.dashboard'))
            ->assertOk()
            ->assertSee('Buyer price offers')
            ->assertSee('Farmer One Rice')
            ->assertSee('Buyer One')
            ->assertDontSee('Farmer Two Beans')
            ->assertDontSee('Buyer Two');

        $this->patch(route('offers.farmer.respond', $otherOffer), [
            'action' => 'accept',
        ])->assertForbidden();

        $this->patch(route('offers.farmer.respond', $ownOffer), [
            'action' => 'counter',
            'counter_price' => '0',
        ])->assertSessionHasErrors('counter_price');

        $this->patch(route('offers.farmer.respond', $ownOffer), [
            'action' => 'counter',
            'counter_price' => '37.50',
        ])->assertRedirect();

        $ownOffer->refresh();
        $this->assertSame(Offer::STATUS_COUNTERED, $ownOffer->status);
        $this->assertSame('37.50', $ownOffer->current_price);
        $this->assertDatabaseHas('offer_events', [
            'offer_id' => $ownOffer->id,
            'actor_id' => $farmer->id,
            'action' => 'countered',
            'price' => '37.50',
        ]);
        $this->assertSame(Offer::STATUS_PENDING, $otherOffer->fresh()->status);
    }

    public function test_buyer_sees_only_own_offers_and_can_accept_or_reject_counter_offers_or_withdraw_pending(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->productFor($farmer, 20);
        $ownOffer = $this->offerFor($buyer, $product, 2, '35.00');
        $foreignOffer = $this->offerFor($otherBuyer, $product, 1, '30.00');

        $foreignOffer->update(['status' => Offer::STATUS_COUNTERED, 'current_price' => '32.00']);
        $this->actingAs($buyer)->get(route('buyer.dashboard'))
            ->assertOk()
            ->assertSee('Offer #' . $ownOffer->id)
            ->assertDontSee('Offer #' . $foreignOffer->id);

        $this->patch(route('offers.withdraw', $foreignOffer))->assertForbidden();
        $this->patch(route('offers.counter-response', $foreignOffer), ['decision' => 'accept'])
            ->assertForbidden();

        $counterToAccept = $this->offerFor($buyer, $product, 2, '34.00', Offer::STATUS_COUNTERED, '36.00');
        $this->patch(route('offers.counter-response', $counterToAccept), ['decision' => 'accept'])->assertRedirect();
        $this->assertSame(Offer::STATUS_ACCEPTED, $counterToAccept->fresh()->status);
        $this->assertDatabaseHas('offer_events', ['offer_id' => $counterToAccept->id, 'action' => 'counter_accepted']);

        $counterToReject = $this->offerFor($buyer, $product, 1, '31.00', Offer::STATUS_COUNTERED, '33.00');
        $this->patch(route('offers.counter-response', $counterToReject), ['decision' => 'reject'])->assertRedirect();
        $this->assertSame(Offer::STATUS_REJECTED, $counterToReject->fresh()->status);
        $this->assertDatabaseHas('offer_events', ['offer_id' => $counterToReject->id, 'action' => 'counter_rejected']);

        $pendingToWithdraw = $this->offerFor($buyer, $product, 1, '29.00');
        $this->patch(route('offers.withdraw', $pendingToWithdraw))->assertRedirect();
        $this->assertSame(Offer::STATUS_WITHDRAWN, $pendingToWithdraw->fresh()->status);
        $this->assertDatabaseHas('offer_events', ['offer_id' => $pendingToWithdraw->id, 'action' => 'withdrawn']);
    }

    public function test_farmer_can_accept_or_reject_pending_offers_and_duplicate_transitions_are_blocked(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->productFor($farmer, 10);
        $acceptedOffer = $this->offerFor($buyer, $product, 2, '38.00');
        $rejectedOffer = $this->offerFor($buyer, $product, 1, '25.00');

        $this->actingAs($farmer)->patch(route('offers.farmer.respond', $acceptedOffer), ['action' => 'accept'])
            ->assertRedirect();
        $this->assertSame(Offer::STATUS_ACCEPTED, $acceptedOffer->fresh()->status);
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock);

        $this->patch(route('offers.farmer.respond', $acceptedOffer), ['action' => 'reject'])
            ->assertSessionHasErrors('offer');

        $this->patch(route('offers.farmer.respond', $rejectedOffer), ['action' => 'reject'])
            ->assertRedirect();
        $this->assertSame(Offer::STATUS_REJECTED, $rejectedOffer->fresh()->status);
        $this->assertDatabaseHas('offer_events', ['offer_id' => $acceptedOffer->id, 'action' => 'accepted']);
        $this->assertDatabaseHas('offer_events', ['offer_id' => $rejectedOffer->id, 'action' => 'rejected']);
    }

    public function test_stock_is_rechecked_before_acceptance_and_accepted_offers_cannot_exceed_stock(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->productFor($farmer, 5);
        $firstOffer = $this->offerFor($buyer, $product, 4, '30.00');
        $secondOffer = $this->offerFor($buyer, $product, 2, '28.00');

        $this->actingAs($farmer)->patch(route('offers.farmer.respond', $firstOffer), ['action' => 'accept'])
            ->assertRedirect();
        $this->patch(route('offers.farmer.respond', $secondOffer), ['action' => 'accept'])
            ->assertSessionHasErrors('offer');
        $this->assertSame(Offer::STATUS_PENDING, $secondOffer->fresh()->status);

        $unavailableProduct = $this->productFor($farmer, 2, 'Unavailable stock crop');
        $unavailableOffer = $this->offerFor($buyer, $unavailableProduct, 2, '20.00');
        $unavailableProduct->update(['stock' => 0]);
        $this->patch(route('offers.farmer.respond', $unavailableOffer), ['action' => 'accept'])
            ->assertSessionHasErrors('offer');
        $this->assertSame(Offer::STATUS_PENDING, $unavailableOffer->fresh()->status);
    }

    private function productFor(User $farmer, int $stock = 10, string $name = 'Fresh produce'): Product
    {
        return Product::create([
            'farmer_id' => $farmer->id,
            'name' => $name,
            'category' => 'Vegetables',
            'price' => 50,
            'unit' => 'kg',
            'stock' => $stock,
        ]);
    }

    private function offerFor(
        User $buyer,
        Product $product,
        int $quantity,
        string $price,
        string $status = Offer::STATUS_PENDING,
        ?string $currentPrice = null,
    ): Offer {
        $offer = Offer::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'farmer_id' => $product->farmer_id,
            'initial_price' => $price,
            'current_price' => $currentPrice ?? $price,
            'quantity' => $quantity,
            'status' => $status,
        ]);

        OfferEvent::create([
            'offer_id' => $offer->id,
            'actor_id' => $buyer->id,
            'actor_role' => 'buyer',
            'action' => 'offered',
            'price' => $price,
        ]);

        if ($status === Offer::STATUS_COUNTERED) {
            OfferEvent::create([
                'offer_id' => $offer->id,
                'actor_id' => $product->farmer_id,
                'actor_role' => 'farmer',
                'action' => 'countered',
                'price' => $currentPrice,
            ]);
        }

        return $offer;
    }
}
