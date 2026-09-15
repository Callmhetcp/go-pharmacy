<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiConversationController extends Controller
{
    /**
     * Display AI conversations requiring pharmacist attention.
     */
    public function index(Request $request): Response
    {
        $conversations = AiConversation::query()
            ->with([
                'user:id,name,email,phone',
                'messages' => function ($query) {
                    $query
                        ->latest()
                        ->limit(1);
                },
            ])
            ->whereIn('status', [
                'waiting_for_pharmacist',
                'with_pharmacist',
            ])
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render(
            'Admin/AiConversations/Index',
            [
                'conversations' => $conversations,
            ]
        );
    }

    /**
     * Display a pharmacist conversation with its complete message history.
     */
    public function show(AiConversation $conversation): Response
    {
        $conversation->load([
            'user:id,name,email,phone',
            'assignedTo:id,name,email',
            'messages' => function ($query) {
                $query->orderBy('created_at');
            },
        ]);

        return Inertia::render(
            'Admin/AiConversations/Show',
            [
                'conversation' => $conversation,
            ]
        );
    }

    /**
     * Send a pharmacist reply to an AI conversation.
     */
    public function reply(Request $request, AiConversation $conversation)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $wasWaitingForPharmacist =
            $conversation->status === 'waiting_for_pharmacist';

        if ($wasWaitingForPharmacist) {
            $conversation->messages()->create([
                'sender_type' => 'system',
                'sender_id' => null,
                'message' => 'A Go Pharmacy pharmacist has joined the conversation.',
            ]);
        }

        $conversation->messages()->create([
            'sender_type' => 'pharmacist',
            'sender_id' => $request->user()->id,
            'message' => trim($validated['message']),
        ]);

        $conversation->update([
            'status' => 'with_pharmacist',
            'assigned_to' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.ai.conversations.show', $conversation)
            ->with('success', 'Reply sent successfully.');
    }

   /**
     * End pharmacist handoff and return the conversation to AI.
     */
    public function end(AiConversation $conversation)
    {
        $conversation->messages()->create([
            'sender_type' => 'system',
            'sender_id' => null,
            'message' => 'The Go Pharmacy pharmacist has ended the conversation. You can continue with the AI assistant.',
        ]);

        $conversation->update([
            'status' => 'active',
            'assigned_to' => null,
        ]);

        return redirect()
            ->route('admin.ai.conversations.index')
            ->with(
                'success',
                'Conversation ended. The customer is back with the AI assistant.'
            );
    }
}