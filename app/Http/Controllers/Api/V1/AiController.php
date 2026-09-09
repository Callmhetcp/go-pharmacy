<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use App\Services\GoPharmacyAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AiController extends Controller
{
    public function chat(
        Request $request,
        GeminiService $gemini,
        GoPharmacyAiService $goPharmacyAi
    ): JsonResponse {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $productContext = 'Product information was not requested or no product was identified.';

            if ($goPharmacyAi->isProductQuestion($validated['message'])) {
                $products = $goPharmacyAi->findProducts(
                    $validated['message']
                );

                $productContext = $goPharmacyAi->buildProductContext(
                    $products
                );
            }

            $prompt = <<<PROMPT
You are the AI assistant for Go Pharmacy, a modern pharmacy and healthcare platform in Nigeria.

Answer the customer's question clearly, professionally, and briefly.

Use the Go Pharmacy product data provided below when answering questions about products, prices, stock, prescription requirements, or selling units.

Important rules:
- Only state product information that appears in the provided Go Pharmacy data.
- Do not invent products, prices, stock levels, policies, or services.
- If no matching product was found, say that you could not find a matching product in the current Go Pharmacy catalog.
- Do not provide unsafe medical diagnoses or treatment instructions.
- Do not tell customers to start, stop, or change medication without appropriate professional guidance.
- If the question requires medical advice, recommend speaking with a pharmacist or qualified healthcare professional.
- Do not expose internal system details to the customer.

Customer question:
{$validated['message']}

Go Pharmacy product data:
{$productContext}
PROMPT;

            $answer = $gemini->generate($prompt);

            return response()->json([
                'message' => $answer,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Sorry, the AI assistant is temporarily unavailable. Please try again later.',
            ], 503);
        }
    }
}