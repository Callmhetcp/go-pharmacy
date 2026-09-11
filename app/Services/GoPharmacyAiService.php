<?php

namespace App\Services;

use App\Models\Product;
use App\Services\GeminiService;
use Illuminate\Support\Collection;

class GoPharmacyAiService
{
    public function findProducts(string $message): Collection
{
    $stopWords = [
        'do',
        'does',
        'did',
        'you',
        'have',
        'has',
        'is',
        'are',
        'there',
        'any',
        'some',
        'the',
        'a',
        'an',
        'for',
        'me',
        'please',
        'can',
        'could',
        'i',
        'we',
        'want',
        'need',
        'available',
        'available?',
    ];

    $terms = collect(
        preg_split('/\s+/', strtolower(trim($message)))
    )
        ->map(fn ($term) => preg_replace('/[^a-z0-9-]/', '', $term))
        ->filter()
        ->reject(fn ($term) => in_array($term, $stopWords))
        ->unique()
        ->values();

    return Product::query()
        ->with(['category', 'inventory'])
        ->where('is_active', true)
        ->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                $query
                    ->orWhere('name', 'like', '%' . $term . '%')
                    ->orWhere('generic_name', 'like', '%' . $term . '%')
                    ->orWhere('brand', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            }
        })
        ->limit(5)
        ->get();
}

public function searchProducts(array $filters): Collection
{
    $query = Product::query()
        ->with(['category', 'inventory'])
        ->where('is_active', true);

    if (!empty($filters['search'])) {
        $search = trim($filters['search']);
        $query->where(function ($query) use ($search) {
            $query
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('generic_name', 'like', '%' . $search . '%')
                ->orWhere('brand', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhereHas('category', function ($categoryQuery) use ($search) {
                    $categoryQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
        });
    }

    if (isset($filters['max_price'])) {
        $query->where('price', '<=', $filters['max_price']);
    }

    if (isset($filters['min_price'])) {
        $query->where('price', '>=', $filters['min_price']);
    }

    if (isset($filters['prescription_required'])) {
        $query->where(
            'requires_prescription',
            $filters['prescription_required']
        );
    }

    return $query
        ->limit(10)
        ->get();
}

public function extractProductFilters(
    string $message,
    GeminiService $gemini
): array {
    $prompt = <<<PROMPT
You are helping Go Pharmacy understand a customer's product search request.

Extract only the following information from the customer's message:

- search: the product, medicine, symptom, condition, brand, or category they are asking about
- min_price: minimum price in Nigerian Naira, or null
- max_price: maximum price in Nigerian Naira, or null
- prescription_required: true if they specifically require a prescription product, false if they specifically want a non-prescription product, otherwise null

Return ONLY valid JSON using exactly this structure:

{
    "search": string or null,
    "min_price": number or null,
    "max_price": number or null,
    "prescription_required": true or false or null
}

Do not recommend a medicine.
Do not diagnose the customer.
Do not invent information.
Do not include markdown.
Do not include explanations.

Customer message:
{$message}
PROMPT;

    return $gemini->generateJson($prompt);
}

public function isProductQuestion(string $message): bool
{
    $productTerms = Product::query()
        ->where('is_active', true)
        ->get([
            'name',
            'generic_name',
            'brand',
        ])
        ->flatMap(function (Product $product) {
            return [
                $product->name,
                $product->generic_name,
                $product->brand,
            ];
        })
        ->filter()
        ->map(fn ($term) => strtolower($term))
        ->values();

    $searchTerms = [
        'medicine',
        'medicines',
        'drug',
        'drugs',
        'tablet',
        'tablets',
        'capsule',
        'capsules',
        'syrup',
        'cream',
        'ointment',
        'pain',
        'fever',
        'cough',
        'cold',
        'allergy',
        'vitamin',
        'vitamins',
        'supplement',
        'supplements',
        'prescription',
        'prescriptions',
        'product',
        'products',
        'buy',
        'purchase',
        'price',
        'cost',
        'available',
        'stock',
        'have',
    ];

    $message = strtolower($message);

    foreach ($searchTerms as $term) {
        if (str_contains($message, $term)) {
            return true;
        }
    }

    foreach ($productTerms as $term) {
        if (str_contains($message, $term)) {
            return true;
        }

        $words = preg_split('/\s+/', $term);

        foreach ($words as $word) {
            if (strlen($word) >= 4 && str_contains($message, $word)) {
                return true;
            }
        }
    }

    return false;
}
    public function buildProductContext(Collection $products): string
    {
        if ($products->isEmpty()) {
            return 'No matching products were found in the Go Pharmacy database.';
        }

        return $products
            ->map(function (Product $product) {
                $availableQuantity = $product->inventory?->available_quantity ?? 0;

                return implode("\n", [
                    'Product: ' . $product->name,
                    'Price: ₦' . number_format((float) $product->price, 2),
                    'Available stock: ' . $availableQuantity,
                    'Prescription required: ' . (
                        $product->requires_prescription ? 'Yes' : 'No'
                    ),
                    'Selling unit: ' . $product->sellingUnitLabel(),
                ]);
            })
            ->implode("\n\n");
    }
}