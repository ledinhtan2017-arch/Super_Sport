<?php
declare(strict_types=1);
namespace App\Controllers\Api\Admin;
use App\Core\Request; use App\Core\Response; use App\Models\Order;
final class DashboardApiController {
    public function stats(Request $req, Response $res): void {
        $res->success((new Order())->getDashboardStats())->send();
    }
    public function revenue(Request $req, Response $res): void {
        $res->success(["message" => "Revenue chart coming soon"])->send();
    }
}