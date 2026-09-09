<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    public function generate(string $prompt): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model');

        if (!$apiKey) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        if (!$model) {
            throw new RuntimeException('Gemini model is not configured.');
        }

        $response = Http::timeout(60)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' .
                $model .
                ':generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt,
                                ],
                            ],
                        ],
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API request failed: ' . $response->body()
            );
        }

        $answer = $response->json('candidates.0.content.parts.0.text');

        if (!is_string($answer) || trim($answer) === '') {
            throw new RuntimeException(
                'Gemini returned an empty response.'
            );
        }

        return trim($answer);
    }
}