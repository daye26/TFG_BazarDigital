<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CatalogSearchService
{
    public function findProductByExactBarcode(string $query): ?Product
    {
        $trimmedQuery = trim($query);
        $digitsQuery = $this->digitsOnly($trimmedQuery);

        if ($trimmedQuery === '' || $digitsQuery === '' || $trimmedQuery !== $digitsQuery) {
            return null;
        }

        return Product::query()
            ->where('is_active', true)
            ->where('barcode', $digitsQuery)
            ->first();
    }

    public function search(
        string $query,
        ?int $categoryId = null,
        string $productSort = 'default',
        ?int $productLimit = null,
        ?int $categoryLimit = null,
    ): array {
        $normalizedQuery = $this->normalize($query);
        $digitsQuery = $this->digitsOnly($query);

        if ($normalizedQuery === '' && $digitsQuery === '') {
            return [
                'normalizedQuery' => '',
                'digitsQuery' => '',
                'categories' => collect(),
                'products' => collect(),
            ];
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->with('category')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->get();

        return [
            'normalizedQuery' => $normalizedQuery,
            'digitsQuery' => $digitsQuery,
            'categories' => $this->rankCategories($categories, $normalizedQuery, $categoryLimit),
            'products' => $this->sortProducts(
                $this->rankProducts($products, $normalizedQuery, $digitsQuery),
                $productSort,
                $productLimit,
            ),
        ];
    }

    public function searchAdminProducts(
        string $query,
        string $productSort = 'default',
        ?int $productLimit = null,
    ): Collection {
        $normalizedQuery = $this->normalize($query);
        $digitsQuery = $this->digitsOnly($query);

        if ($normalizedQuery === '' && $digitsQuery === '') {
            return collect();
        }

        $products = Product::query()
            ->with('category')
            ->get();

        return $this->sortProducts(
            $this->rankProducts($products, $normalizedQuery, $digitsQuery),
            $productSort,
            $productLimit,
        );
    }

    public function shouldProvideSuggestions(string $query): bool
    {
        $normalizedQuery = $this->normalize($query);
        $alphanumericQuery = str_replace(' ', '', $normalizedQuery);

        if ($alphanumericQuery === '') {
            return false;
        }

        if (ctype_digit($alphanumericQuery)) {
            return strlen($this->digitsOnly($query)) >= 3;
        }

        return mb_strlen($normalizedQuery) >= 2;
    }

    private function rankCategories(Collection $categories, string $normalizedQuery, ?int $limit = null): Collection
    {
        $rankedCategories = $categories
            ->map(function (Category $category) use ($normalizedQuery) {
                [$score, $matchedOn] = $this->scoreCategory($category, $normalizedQuery);

                if ($score <= 0) {
                    return null;
                }

                $category->setAttribute('search_score', $score);
                $category->setAttribute('search_match_label', $matchedOn);

                return $category;
            })
            ->filter()
            ->sort(function (Category $left, Category $right) {
                return $right->search_score <=> $left->search_score
                    ?: strcasecmp($left->name, $right->name);
            })
            ->values();

        return $limit ? $rankedCategories->take($limit)->values() : $rankedCategories;
    }

    private function rankProducts(Collection $products, string $normalizedQuery, string $digitsQuery): Collection
    {
        return $products
            ->map(function (Product $product) use ($normalizedQuery, $digitsQuery) {
                [$score, $matchedOn] = $this->scoreProduct($product, $normalizedQuery, $digitsQuery);

                if ($score <= 0) {
                    return null;
                }

                $product->setAttribute('search_score', $score);
                $product->setAttribute('search_match_label', $matchedOn);

                return $product;
            })
            ->filter()
            ->values();
    }

    private function sortProducts(Collection $products, string $sort, ?int $limit = null): Collection
    {
        $sortedProducts = match ($sort) {
            'alphabetical' => $products
                ->sortBy(fn (Product $product) => Str::lower($product->name), SORT_NATURAL)
                ->values(),
            'alphabetical_desc' => $products
                ->sortBy(fn (Product $product) => Str::lower($product->name), SORT_NATURAL, true)
                ->values(),
            'newest' => $products
                ->sortBy(fn (Product $product) => $product->created_at?->getTimestamp() ?? 0, SORT_NUMERIC, true)
                ->values(),
            'price_asc', 'price' => $products
                ->sortBy(fn (Product $product) => $product->discountedPriceAmount(), SORT_NUMERIC)
                ->values(),
            'price_desc' => $products
                ->sortBy(fn (Product $product) => $product->discountedPriceAmount(), SORT_NUMERIC, true)
                ->values(),
            default => $products
                ->sort(function (Product $left, Product $right) {
                    return $right->search_score <=> $left->search_score
                        ?: strcasecmp($left->name, $right->name);
                })
                ->values(),
        };

        return $limit ? $sortedProducts->take($limit)->values() : $sortedProducts;
    }

    private function scoreCategory(Category $category, string $normalizedQuery): array
    {
        $bestMatch = [0, null];

        $bestMatch = $this->bestScore(
            $bestMatch,
            $this->scoreText(
                $this->normalize($category->name),
                $normalizedQuery,
                'Categoria',
                exactScore: 1000,
                startsWithScore: 860,
                wordStartsWithScore: 800,
                containsScore: 720,
                fuzzyBaseScore: 640,
            ),
        );

        $bestMatch = $this->bestScore(
            $bestMatch,
            $this->scoreText(
                $this->normalize((string) $category->description),
                $normalizedQuery,
                'Descripcion',
                exactScore: 0,
                startsWithScore: 0,
                wordStartsWithScore: 0,
                containsScore: 280,
                fuzzyBaseScore: 0,
            ),
        );

        return $bestMatch;
    }

    private function scoreProduct(Product $product, string $normalizedQuery, string $digitsQuery): array
    {
        $bestMatch = [0, null];

        $bestMatch = $this->bestScore(
            $bestMatch,
            $this->scoreBarcode($this->digitsOnly((string) $product->barcode), $digitsQuery),
        );

        $bestMatch = $this->bestScore(
            $bestMatch,
            $this->scoreText(
                $this->normalize($product->name),
                $normalizedQuery,
                'Producto',
                exactScore: 1000,
                startsWithScore: 900,
                wordStartsWithScore: 860,
                containsScore: 820,
                fuzzyBaseScore: 700,
            ),
        );

        $bestMatch = $this->bestScore(
            $bestMatch,
            $this->scoreText(
                $this->normalize((string) $product->category?->name),
                $normalizedQuery,
                'Categoria',
                exactScore: 780,
                startsWithScore: 720,
                wordStartsWithScore: 680,
                containsScore: 640,
                fuzzyBaseScore: 560,
            ),
        );

        $bestMatch = $this->bestScore(
            $bestMatch,
            $this->scoreText(
                $this->normalize((string) $product->description),
                $normalizedQuery,
                'Descripcion',
                exactScore: 0,
                startsWithScore: 0,
                wordStartsWithScore: 0,
                containsScore: 380,
                fuzzyBaseScore: 0,
            ),
        );

        return $bestMatch;
    }

    private function scoreBarcode(string $barcode, string $digitsQuery): array
    {
        if ($barcode === '' || $digitsQuery === '') {
            return [0, null];
        }

        if ($barcode === $digitsQuery) {
            return [1500, 'Codigo de barras'];
        }

        if (str_starts_with($barcode, $digitsQuery)) {
            return [1380, 'Codigo de barras'];
        }

        if (strlen($digitsQuery) >= 3 && str_contains($barcode, $digitsQuery)) {
            return [1200, 'Codigo de barras'];
        }

        return [0, null];
    }

    private function scoreText(
        string $fieldValue,
        string $normalizedQuery,
        string $label,
        int $exactScore,
        int $startsWithScore,
        int $wordStartsWithScore,
        int $containsScore,
        int $fuzzyBaseScore,
    ): array {
        if ($fieldValue === '' || $normalizedQuery === '') {
            return [0, null];
        }

        if ($exactScore > 0 && $fieldValue === $normalizedQuery) {
            return [$exactScore, $label];
        }

        if ($startsWithScore > 0 && str_starts_with($fieldValue, $normalizedQuery)) {
            return [$startsWithScore, $label];
        }

        if ($wordStartsWithScore > 0 && $this->anyWordStartsWith($fieldValue, $normalizedQuery)) {
            return [$wordStartsWithScore, $label];
        }

        if ($containsScore > 0 && str_contains($fieldValue, $normalizedQuery)) {
            return [$containsScore, $label];
        }

        if ($fuzzyBaseScore > 0) {
            $fuzzyScore = $this->fuzzyScore($fieldValue, $normalizedQuery, $fuzzyBaseScore);

            if ($fuzzyScore > 0) {
                return [$fuzzyScore, $label];
            }
        }

        return [0, null];
    }

    private function fuzzyScore(string $fieldValue, string $normalizedQuery, int $baseScore): int
    {
        if (mb_strlen($normalizedQuery) < 4) {
            return 0;
        }

        $bestDistance = null;
        $candidates = array_unique([
            $fieldValue,
            ...$this->words($fieldValue),
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === '') {
                continue;
            }

            $threshold = $this->fuzzyThreshold($normalizedQuery, $candidate);

            if (abs(strlen($candidate) - strlen($normalizedQuery)) > $threshold) {
                continue;
            }

            $distance = levenshtein($normalizedQuery, $candidate);

            if ($distance > $threshold) {
                continue;
            }

            $bestDistance = $bestDistance === null
                ? $distance
                : min($bestDistance, $distance);
        }

        if ($bestDistance === null) {
            return 0;
        }

        return max($baseScore - ($bestDistance * 60), 1);
    }

    private function fuzzyThreshold(string $query, string $candidate): int
    {
        $length = max(strlen($query), strlen($candidate));

        return match (true) {
            $length <= 5 => 1,
            $length <= 9 => 2,
            default => 3,
        };
    }

    private function anyWordStartsWith(string $fieldValue, string $normalizedQuery): bool
    {
        foreach ($this->words($fieldValue) as $word) {
            if (str_starts_with($word, $normalizedQuery)) {
                return true;
            }
        }

        return false;
    }

    private function words(string $value): array
    {
        return array_values(array_filter(explode(' ', $value)));
    }

    private function bestScore(array $currentBest, array $candidate): array
    {
        return $candidate[0] > $currentBest[0] ? $candidate : $currentBest;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->value();
    }

    private function digitsOnly(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
