<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// .env
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

// Database connect
dibi::connect([
    'driver'   => getenv('DB_DRIVER'),
    'host'     => getenv('DB_HOST'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'database' => getenv('DB_NAME'),
]);

// router
$container = new League\Container\Container;

$container->share('response', Zend\Diactoros\Response::class);
$container->share('request', function() {
    $_SERVER['REQUEST_URI'] = str_replace(getenv('API_DOCUMENT_ROOT'), '', $_SERVER['REQUEST_URI']);
    return Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    );
});

$container->share('emitter', Zend\Diactoros\Response\SapiEmitter::class);

$route = new League\Route\RouteCollection($container);
$route->map('GET', '/', function (ServerRequestInterface $request, ResponseInterface $response) {
    $response->getBody()->write('hello');
    return $response;
});
$route->map('GET', '/items', function (ServerRequestInterface $request, ResponseInterface $response) {
    $items = dibi::fetchAll('
        SELECT *
        FROM `items`
        ORDER BY `id`
    ');
    $response->getBody()->write(json_encode($items));
    return $response;
});
$route->map('GET', '/items/{param}-{type}-{value}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $rule = null;

    switch ($args['type']) {
        case 'equal':
            $rule = "`props`->'$.".$args['param']."' = %s";
            break;
        case 'bigger':
            $rule = "`props`->'$.".$args['param']."' > %i";
            break;
        case 'less':
            $rule = "`props`->'$.".$args['param']."' < %i";
            break;
        case 'contains':
            $rule = "`props`->'$.".$args['param']."' LIKE %~like~";
            break;
        default:
            exit;
    }

    /*
    dibi::test('
        SELECT *
        FROM `items`
        WHERE '.($rule ?: '1').'
        ORDER BY `id`
    ', $args['value']);exit;
    */

    $items = dibi::fetchAll('
        SELECT *
        FROM `items`
        WHERE '.($rule ?: '1').'
        ORDER BY `id`
    ', $args['value']);
    $response->getBody()->write(json_encode($items));
    return $response;
});

$route->map('POST', '/items', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    dibi::insert('items', [
        'props' => '{}',
    ])
        ->execute();
    $response->getBody()->write(json_encode([
        'status' => 'OK',
        'id' => dibi::insertId(),
    ]));
    return $response;
});
$route->map('PUT', '/items/{id:number}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    parse_str($request->getBody()->getContents(), $post);
    dibi::update('items', $post)->where('id = %i', $args['id'])->execute();
    $response->getBody()->write(json_encode([
        'status' => 'OK',
    ]));
    return $response;
});
$route->map('DELETE', '/items/{id:number}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    dibi::query('DELETE FROM `items` WHERE `id` = %i', $args['id']);
    $response->getBody()->write(json_encode([
        'status' => 'OK',
    ]));
    return $response;
});
$route->map('PATCH', '/items/{item:number}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $item = dibi::query('SELECT * FROM `items` WHERE `id` = %i LIMIT 1', $args['item'])->fetch();

    if (!$item) {
        $response->getBody()->write(json_encode([
            'status' => 'Not found',
        ]));
    }

    $newItem = json_decode($request->getBody()->getContents());
    dibi::update('items', [
        'props%sql' => "JSON_SET(`props`, '$.".$newItem->name."', ".(is_numeric($newItem->value) ? (int) $newItem->value : "'".$newItem->value."'").")",
    ])
        ->where('id = %i', $item->id)
        ->execute();

    $response->getBody()->write(json_encode([
        'status' => 'OK',
    ]));
    return $response;
});
$route->map('DELETE', '/items/{item:number}/{prop}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) {
    $item = dibi::query('SELECT * FROM `items` WHERE `id` = %i LIMIT 1', $args['item'])->fetch();

    if (!$item) {
        $response->getBody()->write(json_encode([
            'status' => 'Not found',
        ]));
    }

    dibi::update('items', [
        'props%sql' => "JSON_REMOVE(`props` , '$.".$args['prop']."')",
    ])
        ->where('id = %i', $item->id)
        ->execute();

    $response->getBody()->write(json_encode([
        'status' => 'OK',
    ]));
    return $response;
});

try {
    $response = $route->dispatch($container->get('request'), $container->get('response'));
    $container->get('emitter')->emit($response);
} catch (\League\Route\Http\Exception\NotFoundException $e) {
    exit;
}
