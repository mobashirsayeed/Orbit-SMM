<?php

namespace App\Services\SEO;

use App\Models\SchemaMarkup;

class SchemaGeneratorService
{
    public function generateOrganization(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $data['name'] ?? '',
            'url' => $data['url'] ?? '',
            'logo' => $data['logo'] ?? '',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $data['phone'] ?? '',
                'contactType' => 'customer service',
            ],
            'sameAs' => $data['social_profiles'] ?? [],
        ];
    }

    public function generateLocalBusiness(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $data['name'] ?? '',
            'image' => $data['image'] ?? '',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $data['street_address'] ?? '',
                'addressLocality' => $data['locality'] ?? '',
                'addressRegion' => $data['region'] ?? '',
                'postalCode' => $data['postal_code'] ?? '',
                'addressCountry' => $data['country'] ?? '',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $data['latitude'] ?? '',
                'longitude' => $data['longitude'] ?? '',
            ],
            'url' => $data['url'] ?? '',
            'telephone' => $data['phone'] ?? '',
            'openingHours' => $data['opening_hours'] ?? [],
            'priceRange' => $data['price_range'] ?? '$$',
        ];
    }

    public function generateArticle(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['headline'] ?? '',
            'image' => $data['image'] ?? [],
            'author' => [
                '@type' => 'Person',
                'name' => $data['author_name'] ?? '',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $data['publisher_name'] ?? '',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $data['publisher_logo'] ?? '',
                ],
            ],
            'datePublished' => $data['date_published'] ?? now()->toIso8601String(),
            'dateModified' => $data['date_modified'] ?? now()->toIso8601String(),
        ];
    }

    public function generateProduct(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $data['name'] ?? '',
            'image' => $data['image'] ?? [],
            'description' => $data['description'] ?? '',
            'sku' => $data['sku'] ?? '',
            'brand' => [
                '@type' => 'Brand',
                'name' => $data['brand'] ?? '',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => $data['url'] ?? '',
                'priceCurrency' => $data['currency'] ?? 'USD',
                'price' => $data['price'] ?? 0,
                'availability' => $data['availability'] ?? 'https://schema.org/InStock',
            ],
            'aggregateRating' => $data['rating'] ? [
                '@type' => 'AggregateRating',
                'ratingValue' => $data['rating']['value'] ?? 0,
                'reviewCount' => $data['rating']['count'] ?? 0,
            ] : null,
        ];
    }

    public function saveSchema(int $tenantId, string $type, array $schemaData, ?string $pageUrl = null): SchemaMarkup
    {
        return SchemaMarkup::create([
            'tenant_id' => $tenantId,
            'type' => $type,
            'schema_data' => $schemaData,
            'page_url' => $pageUrl,
        ]);
    }

    public function getSchemaByType(int $tenantId, string $type): array
    {
        return SchemaMarkup::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->active()
            ->get()
            ->map(fn($s) => $s->json_ld)
            ->toArray();
    }
}
