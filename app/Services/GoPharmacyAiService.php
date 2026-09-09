<?php

namespace App\Services;

use App\Models\Product;
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

    $message = strtolower($message);

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