<?php
declare(strict_types=1);
namespace App\Controllers\Api\Admin;
use App\Core\Request; use App\Core\Response; use App\Core\Session; use App\Models\Order;
final class OrderApiController {
    private Order $model;
    public function __construct() { $this->model = new Order(); }
    public function index(Request $req, Response $res): void {
        $filters = ["search"=>$req->query("search",""),"status"=>$req->query("status",""),"payment_status"=>$req->query("payment_status","")];
        $res->success($this->model->adminList($filters,(int)$req->query("page","1")))->send();
    }
    public function show(Request $req, Response $res): void {
        $o = $this->model->findWithDetails((int)$req->param("id"));
        $o ? $res->success($o)->send() : $res->error("Not found",[],404)->send();
    }
    public function updateStatus(Request $req, Response $res): void {
        $d = $req->json();
        $this->model->updateStatus((int)$req->param("id"),$d["status"],(int)Session::userId(),$d["note"]??"");
        $res->success(null,"Status updated.")->send();
    }
}