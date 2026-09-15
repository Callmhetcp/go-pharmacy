<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConversationController extends Controller
{
    /**
     * Display AI conversations requiring pharmacist attention.
     */
    public function index(Request $request): JsonResponse
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
            ->paginate(
                $request->integer('per_page', 15)
            )
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    /**
     * Display a single AI conversation with its complete message history.
     */
    public function show(AiConversation $conversation): JsonResponse
    {
        $conversation->load([
            'user:id,name,email,phone',
            'assignedTo:id,name,email',
            'messages' => function ($query) {
                $query->orderBy('created_at');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }

    /**
     * Store a pharmacist reply in an AI conversation.
     */
    public function storeMessage(
        Request $request,
        AiConversation $conversation
    ): JsonResponse {
        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'pharmacist',
            'sender_id' => $request->user()->id,
            'message' => trim($validated['message']),
        ]);

        $conversation->update([
            'status' => 'with_pharmacist',
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ], 201);
    }
}