<?php
declare(strict_types=1);
namespace App\Controllers\Api\Admin;
use App\Core\Request; use App\Core\Response; use App\Models\Product;
final class ProductApiController {
    private Product $model;
    public function __construct() { $this->model = new Product(); }
    public function index(Request $req, Response $res): void {
        $res->success($this->model->adminList(["search"=>$req->query("search",""),"status"=>$req->query("status","")],(int)$req->query("page","1")))->send();
    }
    public function show(Request $req, Response $res): void {
        $p = $this->model->findWithDetails((string)$req->param("id"));
        $p ? $res->success($p)->send() : $res->error("Not found",[],404)->send();
    }
    public function store(Request $req, Response $res): void {
        $id = $this->model->insert($req->json());
        $res->success(["id"=>$id],"Product created.",201)->send();
    }
    public function update(Request $req, Response $res): void {
        $this->model->update((int)$req->param("id"),$req->json());
        $res->success(null,"Updated.")->send();
    }
    public function destroy(Request $req, Response $res): void {
        $this->model->delete((int)$req->param("id"));
        $res->success(null,"Deleted.")->send();
    }
    public function variants(Request $req, Response $res): void {
        $res->success($this->model->getVariants((int)$req->param("id")))->send();
    }
    public function uploadImage(Request $req, Response $res): void  { $res->success(null,"Upload Phase 5")->send(); }
    public function deleteImage(Request $req, Response $res): void  { $res->success(null,"Deleted.")->send(); }
    public function reorderImages(Request $req, Response $res): void { $res->success(null,"Reordered.")->send(); }
    public function addVariant(Request $req, Response $res): void   { $res->success(null,"Phase 5")->send(); }
    public function updateVariant(Request $req, Response $res): void { $res->success(null,"Updated.")->send(); }
    public function deleteVariant(Request $req, Response $res): void { $res->success(null,"Deleted.")->send(); }
}