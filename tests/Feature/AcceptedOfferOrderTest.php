<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptedOfferOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_owner_can_convert_an_accepted_offer_once_using_agreed_values(): void
    {
        [$buyer,$product,$offer] = $this->setupOffer(Offer::STATUS_ACCEPTED);
        $this->actingAs($buyer)->post(route('offers.order',$offer), ['delivery_address'=>'Dhaka','phone'=>'01700000000'])->assertRedirect();
        $order = Order::sole();
        $this->assertEquals(27.50,(float)$order->items->first()->unit_price);
        $this->assertSame(3,$order->items->first()->quantity);
        $this->assertSame(6,$product->fresh()->stock);
        $this->actingAs($buyer)->post(route('offers.order',$offer), ['delivery_address'=>'Dhaka','phone'=>'01700000000'])->assertRedirect(route('orders.show',$order));
        $this->assertDatabaseCount('orders',1);
    }

    public function test_wrong_buyer_nonaccepted_offer_and_insufficient_stock_are_rejected(): void
    {
        [$buyer,$product,$accepted] = $this->setupOffer(Offer::STATUS_ACCEPTED);
        $other=User::factory()->create(['role'=>'buyer']);
        $this->actingAs($other)->post(route('offers.order',$accepted), ['delivery_address'=>'X','phone'=>'1'])->assertForbidden();
        $pending = Offer::create(['product_id'=>$product->id,'buyer_id'=>$buyer->id,'farmer_id'=>$product->farmer_id,'initial_price'=>20,'current_price'=>20,'quantity'=>1,'status'=>Offer::STATUS_PENDING]);
        $this->actingAs($buyer)->post(route('offers.order',$pending), ['delivery_address'=>'X','phone'=>'1'])->assertSessionHasErrors('offer');
        $product->update(['stock'=>1]);
        $this->post(route('offers.order',$accepted), ['delivery_address'=>'X','phone'=>'1'])->assertSessionHasErrors('offer');
        $this->assertDatabaseCount('orders',0);
    }

    private function setupOffer(string $status): array
    {
        $farmer=User::factory()->create(['role'=>'farmer']); $buyer=User::factory()->create(['role'=>'buyer']);
        $product=Product::create(['farmer_id'=>$farmer->id,'name'=>'Potatoes','category'=>'Vegetables','price'=>50,'unit'=>'kg','stock'=>9]);
        $offer=Offer::create(['product_id'=>$product->id,'buyer_id'=>$buyer->id,'farmer_id'=>$farmer->id,'initial_price'=>30,'current_price'=>'27.50','quantity'=>3,'status'=>$status]);
        return [$buyer,$product,$offer];
    }
}
