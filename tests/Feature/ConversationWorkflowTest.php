<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConversationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_jpeg_attachment_url_from_page_and_polling_serves_the_stored_jpeg_bytes(): void
    {
        Storage::fake('local');
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Tomatoes',
            'category' => 'Vegetables',
            'price' => 50,
            'unit' => 'kg',
            'stock' => 5,
        ]);
        $conversation = Conversation::create([
            'buyer_id' => $buyer->id,
            'farmer_id' => $farmer->id,
            'product_id' => $product->id,
            'context_key' => 'jpeg-attachment-test',
        ]);

        $this->actingAs($buyer)->post(route('conversations.messages.store', $conversation), [
            'attachment' => UploadedFile::fake()->image('harvest.jpeg'),
        ])->assertRedirect();

        $message = ConversationMessage::sole();
        $disk = Storage::disk('local');
        $this->assertTrue($disk->exists($message->attachment_path));
        $this->assertSame('image/jpeg', $disk->mimeType($message->attachment_path));
        $storedBytes = $disk->get($message->attachment_path);
        $this->assertStringStartsWith("\xFF\xD8\xFF", $storedBytes);

        $attachmentUrl = route('conversations.attachments.show', $message, false);
        $this->assertSame('/conversation-messages/'.$message->id.'/attachment', $attachmentUrl);
        $this->assertStringStartsWith('chat-attachments/', $message->attachment_path);
        $this->actingAs($farmer)
            ->getJson(route('conversations.messages.index', $conversation).'?after=0')
            ->assertOk()
            ->assertJsonPath('0.attachment_url', $attachmentUrl);

        $page = $this->get(route('conversations.show', $conversation))->assertOk();
        $page->assertSee('href="'.$attachmentUrl.'"', false);
        $page->assertSee('src="'.$attachmentUrl.'"', false);

        $image = $this->get($attachmentUrl);
        $image->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $image->assertHeader('Content-Disposition', 'inline; filename='.basename($message->attachment_path));
        $image->assertStreamedContent($storedBytes);
    }

    public function test_buyer_can_message_product_farmer_and_only_participants_can_read_or_send(): void
    {
        Storage::fake('local');
        $farmer=User::factory()->create(['role'=>'farmer']); $buyer=User::factory()->create(['role'=>'buyer']); $intruder=User::factory()->create(['role'=>'buyer']);
        $product=Product::create(['farmer_id'=>$farmer->id,'name'=>'Potatoes','category'=>'Vegetables','price'=>50,'unit'=>'kg','stock'=>5]);
        $this->actingAs($buyer)->get(route('products.show',$product))
            ->assertOk()
            ->assertSee('Message Farmer / Chat with Farmer')
            ->assertSee(route('conversations.start-product',$product));
        $this->post(route('conversations.start-product',$product))->assertRedirect();
        $conversation=Conversation::sole();
        $this->post(route('conversations.messages.store',$conversation), [
            'body'=>'Hello',
            'attachment'=>UploadedFile::fake()->image('private.png'),
            'sender_id'=>$farmer->id,
            'buyer_id'=>$farmer->id,
            'farmer_id'=>$intruder->id,
            'role'=>'farmer',
        ])->assertRedirect();
        $message=ConversationMessage::sole();
        $this->assertSame($buyer->id, $message->sender_id);
        $this->assertSame($buyer->id, $conversation->fresh()->buyer_id);
        $this->assertSame($farmer->id, $conversation->fresh()->farmer_id);
        $this->assertDatabaseHas('notifications',['notifiable_id'=>$farmer->id]);
        Storage::disk('local')->assertExists($message->attachment_path);
        $this->actingAs($farmer)
            ->getJson(route('conversations.messages.index', $conversation) . '?after=0')
            ->assertOk()
            ->assertJsonPath('0.body', 'Hello')
            ->assertJsonPath('0.sender_id', $buyer->id)
            ->assertJsonPath('0.attachment_url', route('conversations.attachments.show', $message, false));
        $this->getJson(route('conversations.messages.index', $conversation) . '?after=' . $message->id)
            ->assertOk()
            ->assertExactJson([]);
        $imageResponse = $this->get(route('conversations.attachments.show', $message));
        $imageResponse->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertHeader('Content-Disposition', 'inline; filename=' . basename($message->attachment_path));
        $imageResponse->assertStreamedContent(Storage::disk('local')->get($message->attachment_path));
        $showResponse = $this->get(route('conversations.show',$conversation));
        $showResponse->assertOk()->assertSee('Hello');
        $showResponse->assertSee(route('conversations.attachments.show', $message, false), false);
        $showResponse->assertSee('data-chat-image-open', false);
        $this->actingAs($intruder)->get(route('conversations.show',$conversation))->assertForbidden();
        $this->getJson(route('conversations.messages.index', $conversation))->assertForbidden();
        $this->get(route('conversations.attachments.show',$message))->assertForbidden();
        $this->post(route('conversations.messages.store',$conversation),['body'=>'No'])->assertForbidden();
        $missingFileMessage = $conversation->messages()->create([
            'sender_id' => $buyer->id,
            'body' => null,
            'attachment_path' => 'chat-attachments/missing.png',
        ]);
        $this->actingAs($farmer)->get(route('conversations.attachments.show', $missingFileMessage))->assertNotFound();
        $this->actingAs($intruder)->get(route('conversations.attachments.show', $missingFileMessage))->assertForbidden();
        $unsafePathMessage = $conversation->messages()->create([
            'sender_id' => $buyer->id,
            'body' => null,
            'attachment_path' => 'chat-attachments/../products/private.png',
        ]);
        $this->actingAs($farmer)->get(route('conversations.attachments.show', $unsafePathMessage))->assertNotFound();
        $this->actingAs($farmer)->delete(route('products.destroy',$product))->assertRedirect()->assertSessionHas('error');
    }

    public function test_offer_conversation_is_bound_to_its_actual_participants(): void
    {
        $farmer=User::factory()->create(['role'=>'farmer']); $buyer=User::factory()->create(['role'=>'buyer']); $other=User::factory()->create(['role'=>'farmer']);
        $product=Product::create(['farmer_id'=>$farmer->id,'name'=>'Potatoes','category'=>'Vegetables','price'=>50,'unit'=>'kg','stock'=>5]);
        $offer=Offer::create(['product_id'=>$product->id,'buyer_id'=>$buyer->id,'farmer_id'=>$farmer->id,'initial_price'=>30,'current_price'=>30,'quantity'=>1,'status'=>'pending']);
        $this->actingAs($other)->post(route('conversations.start-offer',$offer))->assertForbidden();
        $this->actingAs($buyer)->post(route('conversations.start-offer',$offer))->assertRedirect();
        $conversation = Conversation::sole();
        $this->assertSame($farmer->id, $conversation->farmer_id);
        $this->get(route('conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Current offer total')
            ->assertSee('30.00')
            ->assertSee(route('offers.withdraw', $offer), false);
        $this->actingAs($farmer)->get(route('conversations.show', $conversation))
            ->assertOk()
            ->assertSee(route('offers.farmer.respond', $offer), false)
            ->assertSee('Accept offer')
            ->assertSee('Counter price');
    }

    public function test_non_image_or_oversized_chat_attachments_are_rejected(): void
    {
        $farmer=User::factory()->create(['role'=>'farmer']); $buyer=User::factory()->create(['role'=>'buyer']);
        $product=Product::create(['farmer_id'=>$farmer->id,'name'=>'Potatoes','category'=>'Vegetables','price'=>50,'unit'=>'kg','stock'=>5]);
        $conversation=Conversation::create(['buyer_id'=>$buyer->id,'farmer_id'=>$farmer->id,'product_id'=>$product->id,'context_key'=>'test-chat']);
        $this->actingAs($buyer)->post(route('conversations.messages.store',$conversation), [
            'body'=>'Attached file','attachment'=>UploadedFile::fake()->create('notes.txt',10,'text/plain'),
        ])->assertSessionHasErrors('attachment');
        $this->post(route('conversations.messages.store',$conversation), [
            'body'=>'Large image','attachment'=>UploadedFile::fake()->image('large.png')->size(5121),
        ])->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('conversation_messages',0);
    }
}
