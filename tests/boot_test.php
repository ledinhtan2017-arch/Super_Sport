<?php
/**
 * Bootstrap test script — chạy từ project root:
 * C:\xampp\php\php.exe tests/boot_test.php
 */

define('BASE_PATH', dirname(__DIR__));
define('START_TIME', microtime(true));

require BASE_PATH . '/app/Core/Env.php';
\App\Core\Env::load(BASE_PATH . '/.env');
require BASE_PATH . '/vendor/autoload.php';

$pass = 0;
$fail = 0;

function assert_ok(string $label, bool $condition, string $got = ''): void
{
    global $pass, $fail;
    if ($condition) {
        echo "  ✓  {$label}\n";
        $pass++;
    } else {
        echo "  ✗  {$label}" . ($got ? " (got: {$got})" : '') . "\n";
        $fail++;
    }
}

echo "\n=== SPORTZONE Boot Test ===\n\n";

// ── 1. Env ────────────────────────────────────────────────────
echo "[1] Env\n";
assert_ok('APP_NAME loaded', (($_ENV['APP_NAME'] ?? '') !== ''), $_ENV['APP_NAME'] ?? 'empty');

// ── 2. Config ─────────────────────────────────────────────────
echo "\n[2] Config\n";
$dbCfg  = require BASE_PATH . '/config/database.php';
$appCfg = require BASE_PATH . '/config/app.php';
assert_ok('config/database.php loads', is_array($dbCfg));
assert_ok('config/app.php loads',      is_array($appCfg));
assert_ok('DB host present',           !empty($dbCfg['host']));
assert_ok('PDO options present',       isset($dbCfg['options'][PDO::ATTR_ERRMODE]));

// ── 3. Helpers ────────────────────────────────────────────────
echo "\n[3] Global Helpers\n";
assert_ok('config() function exists', function_exists('config'));
assert_ok('base_url() function exists', function_exists('base_url'));
assert_ok('e() function exists', function_exists('e'));
assert_ok('format_price() function exists', function_exists('format_price'));
assert_ok('format_price(2450000)',
    format_price(2450000) === '2.450.000đ',
    format_price(2450000)
);
assert_ok('e() escapes XSS',
    e('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;',
    e('<script>alert(1)</script>')
);
assert_ok('config("app.debug") resolves', config('app.debug') !== null);

// ── 4. Request ────────────────────────────────────────────────
echo "\n[4] Request\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = '/shop?sort=price&page=2';
$_SERVER['SCRIPT_NAME']    = '/index.php';
$req = new \App\Core\Request();
assert_ok('Request instantiates',    $req instanceof \App\Core\Request);
assert_ok('method() = GET',          $req->method() === 'GET',  $req->method());
assert_ok('path() = /shop',          $req->path()   === '/shop', $req->path());
assert_ok('query("sort") = price',   $req->query('sort') === 'price', $req->query('sort'));
assert_ok('query("page") = 2',       $req->query('page') === '2',   $req->query('page'));
assert_ok('isGet() = true',          $req->isGet());
assert_ok('isPost() = false',        !$req->isPost());

// Method override test
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['_method'] = 'DELETE';
$req2 = new \App\Core\Request();
assert_ok('Method override POST→DELETE', $req2->method() === 'DELETE', $req2->method());
unset($_POST['_method']);

// ── 5. Response ───────────────────────────────────────────────
echo "\n[5] Response\n";
$res = new \App\Core\Response();
assert_ok('Response instantiates', $res instanceof \App\Core\Response);
$res->status(201);
assert_ok('status(201)', $res->getStatus() === 201);
$res->json(['ok' => true]);
assert_ok('json() sets body', str_contains($res->getBody(), '"ok"'));
$res2 = new \App\Core\Response();
$res2->success(['id' => 1], 'Created');
assert_ok('success() wraps correctly', str_contains($res2->getBody(), '"success":true'));
$res3 = new \App\Core\Response();
$res3->error('Bad input', ['field' => ['required']], 422);
assert_ok('error() wraps correctly', str_contains($res3->getBody(), '"success":false'));

// ── 6. Router ─────────────────────────────────────────────────
echo "\n[6] Router\n";
$router = new \App\Core\Router();
assert_ok('Router instantiates', $router instanceof \App\Core\Router);
$hit = false;
$router->get('/', function($req, $res) use (&$hit) { $hit = true; });
$router->get('/product/{slug}', function($req, $res) use (&$hit) { $hit = $req->param('slug'); });
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI']    = '/';
$_SERVER['SCRIPT_NAME']    = '/index.php';
$req4 = new \App\Core\Request();
ob_start();
$router->dispatch($req4, new \App\Core\Response());
ob_end_clean();
assert_ok('Router dispatches / correctly', $hit === true);

$_SERVER['REQUEST_URI'] = '/product/nike-air-zoom-pegasus-41';
$req5 = new \App\Core\Request();
ob_start();
$router->dispatch($req5, new \App\Core\Response());
ob_end_clean();
assert_ok('Router extracts {slug} param', $hit === 'nike-air-zoom-pegasus-41', (string)$hit);

// ── 7. Validator ──────────────────────────────────────────────
echo "\n[7] Validator\n";
use App\Helpers\Validator;
$v = new Validator(['email' => 'bad-email', 'name' => '']);
$v->required('name', 'Tên')->email('email');
assert_ok('Validator fails on bad data', $v->fails());
assert_ok('Error for name field', !empty($v->errors()['name']));
assert_ok('Error for email field', !empty($v->errors()['email']));

$v2 = new Validator(['email' => 'user@example.com', 'name' => 'An']);
$v2->required('name', 'Tên')->email('email');
assert_ok('Validator passes on good data', $v2->passes());

// ── 8. Sanitizer ──────────────────────────────────────────────
echo "\n[8] Sanitizer\n";
use App\Helpers\Sanitizer;
assert_ok('html() escapes quotes', Sanitizer::html('"hello"') === '&quot;hello&quot;', Sanitizer::html('"hello"'));
assert_ok('slug() from Vietnamese', Sanitizer::slug('Giày Chạy Bộ') === 'giay-chay-bo', Sanitizer::slug('Giày Chạy Bộ'));
assert_ok('int() parses', Sanitizer::int('42abc') === 0);
assert_ok('int() valid', Sanitizer::int('42') === 42);

// ── 9. CSRF ───────────────────────────────────────────────────
echo "\n[9] CSRF\n";
session_name('sz_test');
@session_start();
use App\Helpers\CsrfToken;
$token = CsrfToken::regenerate();
assert_ok('Token generated (64 hex chars)', strlen($token) === 64 && ctype_xdigit($token));
assert_ok('Token validates correctly', CsrfToken::validate($token));
assert_ok('Wrong token fails', !CsrfToken::validate('wrongtoken'));
assert_ok('field() contains input tag', str_contains(CsrfToken::field(), '<input'));

// ── Summary ───────────────────────────────────────────────────
echo "\n" . str_repeat('─', 40) . "\n";
$total = $pass + $fail;
echo "Result: {$pass}/{$total} tests passed";
if ($fail > 0) {
    echo " | {$fail} FAILED";
}
echo "\n\n";
exit($fail > 0 ? 1 : 0);
