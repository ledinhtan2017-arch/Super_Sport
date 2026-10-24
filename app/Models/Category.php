<?php

declare(strict_types=1);

namespace App\Models;

final class Category extends BaseModel
{
    protected string $table    = 'categories';
    protected array  $fillable = ['name', 'slug', 'parent_id', 'description', 'image_url', 'sort_order', 'is_active'];

    public function allActive(): array
    {
        return $this->raw(
            "SELECT c.*, COUNT(p.id) as product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id AND p.status = 'published'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    public function allForAdmin(): array
    {
        return $this->raw(
            "SELECT c.*, COUNT(p.id) as product_count
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->rawOne(
            "SELECT * FROM categories WHERE slug = ? LIMIT 1",
            [$slug]
        );
    }

    public function parents(): array
    {
        return $this->raw(
            "SELECT * FROM categories WHERE parent_id IS NULL AND is_active = 1 ORDER BY sort_order ASC, name ASC"
        );
    }

    public function children(int $parentId): array
    {
        return $this->raw(
            "SELECT * FROM categories WHERE parent_id = ? AND is_active = 1 ORDER BY sort_order ASC, name ASC",
            [$parentId]
        );
    }
}
