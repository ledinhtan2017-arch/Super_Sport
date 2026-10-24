<?php
declare(strict_types=1);
namespace App\Controllers\Api\Admin;
use App\Core\Request; use App\Core\Response; use App\Models\User;
final class UserApiController {
    private User $model;
    public function __construct() { $this->model = new User(); }
    public function index(Request $req, Response $res): void {
        $res->success($this->model->adminList($req->query("search",""),$req->query("role",""),(int)$req->query("page","1")))->send();
    }
    public function update(Request $req, Response $res): void {
        $data = array_intersect_key($req->json(),array_flip(["name","role","is_active"]));
        $this->model->update((int)$req->param("id"),$data);
        $res->success(null,"Updated.")->send();
    }
}