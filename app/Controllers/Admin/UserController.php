<?php
declare(strict_types=1);
namespace App\Controllers\Admin;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\User;

final class UserController
{
    private User $model;
    public function __construct() { $this->model = new User(); }

    public function index(Request $request, Response $response): void
    {
        $page   = max(1, (int)$request->query('page', 1));
        $search = trim((string)$request->query('search', ''));
        $role   = $request->query('role', '');

        $conditions = [];
        if ($role !== '') $conditions['role'] = $role;

        if ($search !== '') {
            // Search via raw paginate-like approach
            $users = $this->searchUsers($search, $role, $page, 20);
        } else {
            $users = $this->model->paginate($page, 20, $conditions, 'created_at', 'DESC');
        }

        $response->view('admin/users/index', [
            'title'     => 'Nguoi dung — SPORTZONE Admin',
            'pageTitle' => 'Quan ly nguoi dung',
            'paginator' => $users,
            'users'     => $users['data'],
            'search'    => $search,
            'role'      => $role,
        ], 'admin')->send();
    }

    public function update(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $data = $request->body();
        $this->model->update($id, [
            'name'      => trim($data['name'] ?? ''),
            'role'      => in_array($data['role'] ?? '', ['customer','admin'], true) ? $data['role'] : 'customer',
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ]);
        Session::flash('success', 'Da cap nhat nguoi dung!');
        $response->redirect(base_url('/admin/users'))->send();
    }

    private function searchUsers(string $search, string $role, int $page, int $perPage): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $like   = '%' . $search . '%';
        $params = [$like, $like];
        $roleWhere = '';
        if ($role !== '') {
            $roleWhere = " AND role = ?";
            $params[]  = $role;
        }
        $total = (int)$this->model->rawColumn(
            "SELECT COUNT(*) FROM users WHERE (name LIKE ? OR email LIKE ?){$roleWhere}",
            $params
        );
        $data = $this->model->raw(
            "SELECT * FROM users WHERE (name LIKE ? OR email LIKE ?){$roleWhere} ORDER BY created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );
        return [
            'data' => $data, 'total' => $total, 'per_page' => $perPage,
            'current_page' => $page, 'last_page' => $total > 0 ? (int)ceil($total/$perPage) : 1,
            'from' => $total > 0 ? $offset+1 : 0, 'to' => min($offset+$perPage,$total),
        ];
    }
}
