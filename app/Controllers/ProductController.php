<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Product;

/**
 * ProductController — Trang chi tiết sản phẩm
 */
final class ProductController
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    /**
     * GET /product/{slug}
     */
    public function show(Request $request, Response $response): void
    {
        $slug = $request->param('slug');

        if (!$slug) {
            $response->status(404)->view('errors/404', [], 'none')->send();
            return;
        }

        $product = $this->productModel->findBySlugFull($slug);

        if (!$product) {
            $response->status(404)->view('errors/404', [], 'none')->send();
            return;
        }

        $images   = $this->productModel->getImages($product['id']);
        $variants = $this->productModel->getVariants($product['id']);
        $related  = $this->productModel->related($product['category_id'], $product['id'], 4);

        // Tính giá hiển thị
        $prices    = array_column($variants, 'price');
        $salePrices = array_filter(array_column($variants, 'sale_price'));
        $minPrice  = !empty($prices) ? min($prices) : 0;
        $salePrice = !empty($salePrices) ? min($salePrices) : null;
        $inStock   = array_sum(array_column($variants, 'stock_quantity')) > 0;

        // Nhóm attribute values theo attribute_slug
        $colorOptions = [];
        $sizeOptions  = [];
        foreach ($variants as $v) {
            foreach ($v['attributes'] as $attr) {
                if ($attr['attribute_slug'] === 'mau-sac' || $attr['attribute_slug'] === 'color') {
                    $colorOptions[$attr['value']] = $attr;
                } elseif ($attr['attribute_slug'] === 'size' || $attr['attribute_slug'] === 'kich-co') {
                    $sizeOptions[$attr['value']] = $attr;
                }
            }
        }

        // Thumbnail chính
        $thumbnail = !empty($images) ? $images[0]['image_url'] : null;

        // Breadcrumb
        $breadcrumb = [
            ['label' => 'Trang chủ', 'url' => base_url('/')],
            ['label' => 'Cửa hàng',  'url' => base_url('/shop')],
            ['label' => $product['category_name'], 'url' => base_url('/shop?category=' . $product['category_slug'])],
            ['label' => $product['name'], 'url' => null],
        ];

        $response->view('product/show', [
            'title'        => ($product['meta_title'] ?? '') ?: ($product['name'] . ' — SPORTZONE'),
            'description'  => ($product['meta_description'] ?? '') ?: $product['short_description'] ?: truncate($product['description'] ?? '', 155),
            'product'      => $product,
            'images'       => $images,
            'variants'     => $variants,
            'related'      => $related,
            'colorOptions' => array_values($colorOptions),
            'sizeOptions'  => array_values($sizeOptions),
            'minPrice'     => $minPrice,
            'salePrice'    => $salePrice,
            'inStock'      => $inStock,
            'thumbnail'    => $thumbnail,
            'breadcrumb'   => $breadcrumb,
        ])->send();
    }
}