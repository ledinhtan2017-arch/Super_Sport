<?php
declare(strict_types=1);
namespace App\Controllers\Api\Admin;
use App\Core\Request; use App\Core\Response; use App\Models\Coupon;
final class DiscountApiController {
    private Coupon $model;
    public function __construct() { $this->model = new Coupon(); }
    public function index(Request $req, Response $res): void {
        $res->success($this->model->allForAdmin())->send();
    }
    public function store(Request $req, Response $res): void {
        $id = $this->model->createCoupon($req->json());
        $res->success(["id"=>$id],"Coupon created.",201)->send();
    }
    public function update(Request $req, Response $res): void {
        $this->model->update((int)$req->param("id"),$req->json());
        $res->success(null,"Updated.")->send();
    }
    public function destroy(Request $req, Response $res): void {
        $this->model->delete((int)$req->param("id"));
        $res->success(null,"Deleted.")->send();
    }
}