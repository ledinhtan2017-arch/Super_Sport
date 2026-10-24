<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Product Model
 *
 * Cung cấp các query phổ biến cho storefront (shop listing, product detail)
 * và admin panel.
 */
final class Product extends BaseModel
{
    protected string $table    = 'products';
    protected array  $fillable = [
        'brand_id', 'category_id', 'name', 'slug', 'description',
        'short_description', 'status', 'is_featured', 'meta_title',
        'meta_description',
    ];

    // ─── Storefront Queries ───────────────────────────────────────────────────

    /**
     * Lấy danh sách sản phẩm có lọc, sort và phân trang cho trang shop.
     *
     * Filters:
     *  - category  : slug danh mục
     *  - brand     : slug thương hiệu
     *  - sale      : 1 = chỉ sp đang sale
     *  - featured  : 1 = chỉ sp nổi bật
     *  - search    : fulltext keyword
     *  - min_price, max_price
     *
     * Sort: price_asc | price_desc | newest | popular (default: newest)
     *
     * @return array{data: array, total: int, per_page: int, current_page: int, last_page: int, from: int, to: int}
     */
    public function shopList(
        array  $filters = [],
        string $sort    = 'newest',
        int    $page    = 1,
        int    $perPage = 16
    ): array {
        [$where, $params] = $this->buildShopFilters($filters);

        $orderBy = match ($sort) {
            'price_asc'  => 'min_price ASC',
            'price_desc' => 'min_price DESC',
            'popular'    => 'p.id DESC',           // TODO: replace with sales count
            default      => 'p.created_at DESC',   // newest
        };

        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $baseQuery = "
            FROM products p
            INNER JOIN brands b    ON b.id = p.brand_id
            INNER JOIN categories c ON c.id = p.category_id
            LEFT JOIN (
                SELECT product_id,
                       MIN(COALESCE(sale_price, price)) AS min_price,
                       MIN(price)                       AS base_price,
                       MIN(sale_price)                  AS sale_price,
                       SUM(stock_quantity)              AS total_stock,
                       COUNT(*)                         AS variant_count
                FROM product_variants
                GROUP BY product_id
            ) pv ON pv.product_id = p.id
            LEFT JOIN (
                SELECT product_id, image_path AS image_url
                FROM product_images
                WHERE sort_order = (
                    SELECT MIN(sort_order) FROM product_images pi2
                    WHERE pi2.product_id = product_images.product_id
                )
            ) pi ON pi.product_id = p.id
            WHERE p.status = 'published' {$where}
        ";

        $total = (int)$this->rawColumn(
            "SELECT COUNT(DISTINCT p.id) " . $baseQuery,
            $params
        );

        $data = $this->raw(
            "SELECT DISTINCT p.id, p.name, p.slug, p.is_featured, p.created_at,
                    b.name AS brand_name, b.slug AS brand_slug,
                    c.name AS category_name, c.slug AS category_slug,
                    pv.min_price, pv.base_price, pv.sale_price, pv.total_stock,
                    pi.image_url AS thumbnail
             " . $baseQuery . "
             ORDER BY {$orderBy}
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    /**
     * Tìm sản phẩm theo slug, kèm đầy đủ thông tin brand + category.
     */
    public function findBySlugFull(string $slug): ?array
    {
        return $this->rawOne(
            "SELECT p.*,
                    b.name AS brand_name, b.slug AS brand_slug, b.logo AS brand_logo,
                    c.name AS category_name, c.slug AS category_slug
             FROM products p
             INNER JOIN brands     b ON b.id = p.brand_id
             INNER JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.status = 'published'
             LIMIT 1",
            [$slug]
        );
    }

    /**
     * Lấy tất cả images của sản phẩm.
     */
    public function getImages(int $productId): array
    {
        return $this->raw(
            "SELECT product_images.*, image_path AS image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC",
            [$productId]
        );
    }

    /**
     * Lấy tất cả variants của sản phẩm, kèm attribute values (màu, size).
     */
    public function getVariants(int $productId): array
    {
        $variants = $this->raw(
            "SELECT pv.*
             FROM product_variants pv
             WHERE pv.product_id = ?
             ORDER BY pv.id ASC",
            [$productId]
        );

        // Gắn attribute values cho từng variant
        foreach ($variants as &$v) {
            $v['attributes'] = $this->raw(
                "SELECT av.*, a.name AS attribute_name
                 FROM variant_attribute_values vav
                 INNER JOIN attribute_values av ON av.id = vav.attribute_value_id
                 INNER JOIN attributes a        ON a.id  = av.attribute_id
                 WHERE vav.variant_id = ?",
                [$v['id']]
            );
        }
        unset($v);
        foreach ($variants as &$variant) {
            foreach ($variant['attributes'] as &$attribute) {
                $attribute['attribute_slug'] = str_slug($attribute['attribute_name']);
            }
            unset($attribute);
            $variant['variant_label'] = implode(' / ', array_column($variant['attributes'], 'value'));
        }
        unset($variant);

        return $variants;
    }

    /**
     * Sản phẩm nổi bật (featured) cho trang chủ.
     */
    public function featured(int $limit = 8): array
    {
        return $this->raw(
            "SELECT p.id, p.name, p.slug, p.is_featured,
                    b.name AS brand_name, b.slug AS brand_slug,
                    c.name AS category_name, c.slug AS category_slug,
                    MIN(COALESCE(pv.sale_price, pv.price)) AS min_price,
                    MIN(pv.price)                           AS base_price,
                    MIN(pv.sale_price)                      AS sale_price,
                    SUM(pv.stock_quantity)                  AS total_stock,
                    pi.image_url AS thumbnail
             FROM products p
             INNER JOIN brands     b  ON b.id = p.brand_id
             INNER JOIN categories c  ON c.id = p.category_id
             LEFT  JOIN product_variants pv ON pv.product_id = p.id
             LEFT  JOIN (
                 SELECT product_id, image_path AS image_url
                 FROM product_images
                 WHERE sort_order = (
                     SELECT MIN(sort_order) FROM product_images pi2
                     WHERE pi2.product_id = product_images.product_id
                 )
             ) pi ON pi.product_id = p.id
             WHERE p.status = 'published' AND p.is_featured = 1
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Sản phẩm mới nhất.
     */
    public function newArrivals(int $limit = 8): array
    {
        return $this->raw(
            "SELECT p.id, p.name, p.slug,
                    b.name AS brand_name, b.slug AS brand_slug,
                    c.name AS category_name, c.slug AS category_slug,
                    MIN(COALESCE(pv.sale_price, pv.price)) AS min_price,
                    MIN(pv.price)                           AS base_price,
                    MIN(pv.sale_price)                      AS sale_price,
                    SUM(pv.stock_quantity)                  AS total_stock,
                    pi.image_url AS thumbnail
             FROM products p
             INNER JOIN brands     b  ON b.id = p.brand_id
             INNER JOIN categories c  ON c.id = p.category_id
             LEFT  JOIN product_variants pv ON pv.product_id = p.id
             LEFT  JOIN (
                 SELECT product_id, image_path AS image_url
                 FROM product_images
                 WHERE sort_order = (
                     SELECT MIN(sort_order) FROM product_images pi2
                     WHERE pi2.product_id = product_images.product_id
                 )
             ) pi ON pi.product_id = p.id
             WHERE p.status = 'published'
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Sản phẩm liên quan (cùng category, khác slug).
     */
    public function related(int $categoryId, int $excludeId, int $limit = 4): array
    {
        return $this->raw(
            "SELECT p.id, p.name, p.slug,
                    b.name AS brand_name,
                    c.name AS category_name, c.slug AS category_slug,
                    MIN(COALESCE(pv.sale_price, pv.price)) AS min_price,
                    MIN(pv.price)                           AS base_price,
                    MIN(pv.sale_price)                      AS sale_price,
                    SUM(pv.stock_quantity)                  AS total_stock,
                    pi.image_url AS thumbnail
             FROM products p
             INNER JOIN brands     b  ON b.id = p.brand_id
             INNER JOIN categories c  ON c.id = p.category_id
             LEFT  JOIN product_variants pv ON pv.product_id = p.id
             LEFT  JOIN (
                 SELECT product_id, image_path AS image_url
                 FROM product_images
                 WHERE sort_order = (
                     SELECT MIN(sort_order) FROM product_images pi2
                     WHERE pi2.product_id = product_images.product_id
                 )
             ) pi ON pi.product_id = p.id
             WHERE p.status = 'published'
               AND p.category_id = ?
               AND p.id != ?
             GROUP BY p.id
             ORDER BY RAND()
             LIMIT ?",
            [$categoryId, $excludeId, $limit]
        );
    }

    /**
     * Tìm kiếm sản phẩm (trang /search).
     *
     * @return array{data: array, total: int, per_page: int, current_page: int, last_page: int}
     */
    public function search(string $keyword, int $page = 1, int $perPage = 16): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $like   = '%' . $keyword . '%';

        $baseQuery = "
            FROM products p
            INNER JOIN brands     b  ON b.id = p.brand_id
            INNER JOIN categories c  ON c.id = p.category_id
            LEFT  JOIN product_variants pv ON pv.product_id = p.id
            LEFT  JOIN (
                SELECT product_id, image_path AS image_url
                FROM product_images
                WHERE sort_order = (
                    SELECT MIN(sort_order) FROM product_images pi2
                    WHERE pi2.product_id = product_images.product_id
                )
            ) pi ON pi.product_id = p.id
            WHERE p.status = 'published'
              AND (p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ? OR c.name LIKE ?)
        ";

        $params = [$like, $like, $like, $like];

        $total = (int)$this->rawColumn(
            "SELECT COUNT(DISTINCT p.id) " . $baseQuery,
            $params
        );

        $data = $this->raw(
            "SELECT DISTINCT p.id, p.name, p.slug,
                    b.name AS brand_name, b.slug AS brand_slug,
                    c.name AS category_name, c.slug AS category_slug,
                    MIN(COALESCE(pv.sale_price, pv.price)) AS min_price,
                    MIN(pv.price)                           AS base_price,
                    MIN(pv.sale_price)                      AS sale_price,
                    SUM(pv.stock_quantity)                  AS total_stock,
                    pi.image_url AS thumbnail
             " . $baseQuery . "
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function buildShopFilters(array $filters): array
    {
        $conditions = [];
        $params     = [];

        if (!empty($filters['category'])) {
            $conditions[] = "c.slug = ?";
            $params[]     = $filters['category'];
        }

        if (!empty($filters['brand'])) {
            $conditions[] = "b.slug = ?";
            $params[]     = $filters['brand'];
        }

        if (!empty($filters['sale']) && (int)$filters['sale'] === 1) {
            $conditions[] = "pv.sale_price IS NOT NULL";
        }

        if (!empty($filters['featured']) && (int)$filters['featured'] === 1) {
            $conditions[] = "p.is_featured = 1";
        }

        if (!empty($filters['search'])) {
            $like           = '%' . $filters['search'] . '%';
            $conditions[]   = "(p.name LIKE ? OR b.name LIKE ?)";
            $params[]       = $like;
            $params[]       = $like;
        }

        if (!empty($filters['min_price'])) {
            $conditions[] = "pv.min_price >= ?";
            $params[]     = (int)$filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $conditions[] = "pv.min_price <= ?";
            $params[]     = (int)$filters['max_price'];
        }

        $where = !empty($conditions) ? 'AND ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }

    /**
     * Gợi ý tìm kiếm nhanh (autocomplete).
     * Trả về tối đa $limit sản phẩm khớp với keyword.
     */
    public function searchSuggestions(string $keyword, int $limit = 8): array
    {
        $like = '%' . $keyword . '%';
        return $this->raw(
            "SELECT p.id, p.name, p.slug,
                    b.name AS brand_name,
                    COALESCE(pv.sale_price, pv.price) AS price,
                    pi.image_url AS thumbnail
             FROM products p
             JOIN brands b ON b.id = p.brand_id
             LEFT JOIN (
                 SELECT product_id, MIN(COALESCE(sale_price, price)) AS price,
                        MIN(sale_price) AS sale_price
                 FROM product_variants GROUP BY product_id
             ) pv ON pv.product_id = p.id
             LEFT JOIN (
                 SELECT product_id, image_path AS image_url
                 FROM product_images
                 WHERE sort_order = (
                     SELECT MIN(sort_order) FROM product_images pi2
                     WHERE pi2.product_id = product_images.product_id
                 )
             ) pi ON pi.product_id = p.id
             WHERE p.status = 'published'
               AND (p.name LIKE ? OR b.name LIKE ?)
             ORDER BY p.is_featured DESC, p.created_at DESC
             LIMIT ?",
            [$like, $like, $limit]
        );
    }

    // ─── Admin Queries ────────────────────────────────────────────────────────

    /**
     * Danh sach san pham cho admin panel voi filter va pagination.
     */
    public function adminList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $conditions = ["1=1"];
        $params     = [];

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = ?";
            $params[]     = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $like           = '%' . $filters['search'] . '%';
            $conditions[]   = "(p.name LIKE ? OR b.name LIKE ?)";
            $params[]       = $like;
            $params[]       = $like;
        }
        if (!empty($filters['category_id'])) {
            $conditions[] = "p.category_id = ?";
            $params[]     = (int)$filters['category_id'];
        }
        if (!empty($filters['brand_id'])) {
            $conditions[] = "p.brand_id = ?";
            $params[]     = (int)$filters['brand_id'];
        }

        $where = implode(' AND ', $conditions);

        $total = (int)$this->rawColumn(
            "SELECT COUNT(DISTINCT p.id) FROM products p
             JOIN brands b ON b.id = p.brand_id
             JOIN categories c ON c.id = p.category_id
             WHERE {$where}",
            $params
        );

        $data = $this->raw(
            "SELECT p.id, p.name, p.slug, p.status, p.is_featured, p.created_at,
                    b.name AS brand_name, c.name AS category_name,
                    COALESCE(pv.total_stock, 0) AS total_stock,
                    COALESCE(pv.min_price, 0) AS min_price,
                    pi.image_url AS thumbnail
             FROM products p
             JOIN brands b ON b.id = p.brand_id
             JOIN categories c ON c.id = p.category_id
             LEFT JOIN (
                 SELECT product_id, SUM(stock_quantity) AS total_stock,
                        MIN(COALESCE(sale_price, price)) AS min_price
                 FROM product_variants GROUP BY product_id
             ) pv ON pv.product_id = p.id
             LEFT JOIN (
                 SELECT product_id, image_path AS image_url
                 FROM product_images
                 WHERE sort_order = (
                     SELECT MIN(sort_order) FROM product_images pi2
                     WHERE pi2.product_id = product_images.product_id
                 )
             ) pi ON pi.product_id = p.id
             WHERE {$where}
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }

    /**
     * Lay bien the cua san pham.
     */

    /**
     * Find product with all details (by ID or slug).
     * Used by admin controllers.
     */
    public function findWithDetails(string|int $idOrSlug): ?array
    {
        if (is_int($idOrSlug) || ctype_digit((string)$idOrSlug)) {
            $product = $this->db->fetchOne(
                "SELECT p.*, c.name AS category_name, b.name AS brand_name
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 LEFT JOIN brands b ON b.id = p.brand_id
                 WHERE p.id = ? LIMIT 1",
                [(int)$idOrSlug]
            );
        } else {
            $product = $this->findBySlugFull((string)$idOrSlug);
        }

        if (!$product) return null;

        $product['images']   = $this->db->fetchAll("SELECT product_images.*, image_path AS image_url FROM product_images WHERE product_id = ? ORDER BY sort_order", [$product['id']]);
        $product['variants'] = $this->getVariants((int)$product['id']);
        return $product;
    }
}
