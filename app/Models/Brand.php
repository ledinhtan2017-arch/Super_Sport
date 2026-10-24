<?php

declare(strict_types=1);

namespace App\Models;

final class Brand extends BaseModel
{
    protected string $table    = 'brands';
    protected array  $fillable = ['name', 'slug', 'logo_url', 'description', 'is_active', 'sort_order'];

    public function allActive(): array
    {
        return $this->raw(
            "SELECT b.*, COUNT(p.id) as product_count
             FROM brands b
             LEFT JOIN products p ON p.brand_id = b.id AND p.status = 'published'
             WHERE b.is_active = 1
             GROUP BY b.id
             ORDER BY b.sort_order ASC, b.name ASC"
        );
    }

    public function allForAdmin(): array
    {
        return $this->raw(
            "SELECT b.*, COUNT(p.id) as product_count
             FROM brands b
             LEFT JOIN products p ON p.brand_id = b.id
             GROUP BY b.id
             ORDER BY b.sort_order ASC, b.name ASC"
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->rawOne(
            "SELECT * FROM brands WHERE slug = ? AND is_active = 1 LIMIT 1",
            [$slug]
        );
    }
}