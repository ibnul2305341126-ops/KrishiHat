<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Offer;
use App\Models\Product;
use App\Notifications\WorkflowNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->user()->id;
        $conversations = Conversation::with(['buyer','farmer','product','offer','messages' => fn ($q) => $q->latest()->limit(1)])
            ->where(fn ($q) => $q->where('buyer_id',$id)->orWhere('farmer_id',$id))->latest('updated_at')->get();
        return view('conversations.index', compact('conversations'));
    }

    public function startProduct(Request $request, Product $product) { return $this->start($request, $product, null); }
    public function startOffer(Request $request, Offer $offer)
    {
        abort_unless(in_array($request->user()->id, [$offer->buyer_id,$offer->farmer_id], true), 403);
        return $this->start($request, $offer->product, $offer);
    }

    private function start(Request $request, Product $product, ?Offer $offer)
    {
        abort_unless(in_array($request->user()->role, ['buyer','farmer'], true), 403);
        abort_if($request->user()->id === $product->farmer_id && $offer === null, 403);
        $offer ??= null;
        $buyerId = $offer?->buyer_id ?? $request->user()->id;
        abort_unless($request->user()->role === 'buyer' || $offer !== null, 403);
        abort_unless($product->farmer_id === ($offer?->farmer_id ?? $product->farmer_id), 403);
        $key = $offer ? 'offer:'.$offer->id : 'product:'.$product->id.':buyer:'.$buyerId;
        $conversation = Conversation::firstOrCreate(['context_key' => $key], [
            'buyer_id' => $buyerId, 'farmer_id' => $product->farmer_id, 'product_id' => $product->id, 'offer_id' => $offer?->id,
        ]);
        abort_unless($conversation->includes($request->user()->id), 403);
        return redirect()->route('conversations.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->includes($request->user()->id), 403);
        $conversation->load(['buyer','farmer','product.photos','offer','messages.sender']);
        return view('conversations.show', compact('conversation'));
    }

    public function messages(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->includes($request->user()->id), 403);
        $data = $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->where('id', '>', (int) ($data['after'] ?? 0))
            ->orderBy('id')
            ->limit(100)
            ->get();

        return response()->json($messages->map(fn (ConversationMessage $message) => [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender->name,
            'sent_at' => $message->created_at->format('M j, Y g:i A'),
            'body' => $message->body,
            'attachment_url' => $message->attachment_path
                ? route('conversations.attachments.show', $message, false)
                : null,
        ]));
    }

    public function send(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->includes($request->user()->id), 403);
        $data = $request->validate(['body' => ['nullable','string','max:5000'], 'attachment' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']]);
        if (blank($data['body'] ?? null) && !$request->hasFile('attachment')) throw ValidationException::withMessages(['body' => 'Write a message or attach an image.']);
        $path = $request->file('attachment')?->store('chat-attachments', 'local');
        DB::transaction(function () use ($request,$conversation,$data,$path) {
            $message = $conversation->messages()->create(['sender_id' => $request->user()->id, 'body' => $data['body'] ?? null, 'attachment_path' => $path]);
            $conversation->touch();
            $recipientId = $request->user()->id === $conversation->buyer_id ? $conversation->farmer_id : $conversation->buyer_id;
            $recipient = \App\Models\User::findOrFail($recipientId);
            $recipient->notify(new WorkflowNotification('New message from '.$request->user()->name, route('conversations.show',$conversation)));
        });
        return redirect()->route('conversations.show',$conversation)->with('success','Message sent.');
    }

    public function attachment(Request $request, ConversationMessage $message)
    {
        $conversation = $message->conversation;
        $diagnostic = [
            'message_id' => $message->id,
            'conversation_id' => $conversation->id,
            'user_id' => $request->user()->id,
            'request_url' => $request->getSchemeAndHttpHost().$request->getPathInfo(),
        ];

        if (! $conversation->includes($request->user()->id)) {
            if (app()->environment('local')) {
                Log::warning('Chat attachment request denied', $diagnostic + ['status' => 403]);
            }
            abort(403);
        }

        $path = $message->attachment_path;
        if (! is_string($path) || preg_match('~\Achat-attachments/[A-Za-z0-9._-]+\z~D', $path) !== 1) {
            if (app()->environment('local')) {
                Log::warning('Chat attachment path rejected', $diagnostic + ['status' => 404]);
            }
            abort(404);
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($path)) {
            if (app()->environment('local')) {
                Log::warning('Chat attachment file missing', $diagnostic + ['status' => 404]);
            }
            abort(404);
        }

        $mimeType = $disk->mimeType($path);
        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            if (app()->environment('local')) {
                Log::warning('Chat attachment MIME rejected', $diagnostic + ['status' => 404, 'mime_type' => $mimeType]);
            }
            abort(404);
        }

        if (app()->environment('local')) {
            Log::info('Chat attachment served', $diagnostic + [
                'status' => 200,
                'mime_type' => $mimeType,
                'bytes' => $disk->size($path),
            ]);
        }

        return $disk->response($path, basename($path), [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ], 'inline');
    }
}
