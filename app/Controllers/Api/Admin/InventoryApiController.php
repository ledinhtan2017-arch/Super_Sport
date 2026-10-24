<?php
declare(strict_types=1);
namespace App\Controllers\Api\Admin;
use App\Core\Request; use App\Core\Response; use App\Core\Database;
final class InventoryApiController {
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }
    public function updateStock(Request $req, Response $res): void {
        $qty = (int)($req->json()["stock_quantity"] ?? 0);
        $this->db->execute("UPDATE product_variants SET stock_quantity=? WHERE id=?",[$qty,(int)$req->param("id")]);
        $res->success(null,"Stock updated.")->send();
    }
    public function lowStock(Request $req, Response $res): void {
        $t = (int)$req->query("threshold","5");
        $items = $this->db->fetchAll("SELECT pv.id,pv.sku,pv.stock_quantity,p.name AS product_name,p.id AS product_id FROM product_variants pv JOIN products p ON p.id=pv.product_id WHERE pv.stock_quantity<=? ORDER BY pv.stock_quantity ASC LIMIT 50",[$t]);
        $res->success($items)->send();
    }
}