<?php

declare(strict_types=1);

// Framework classes
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

// Tranquillity route-specific middlewares
use Tranquillity\Middlewares\AuthenticationMiddleware;
use Tranquillity\Middlewares\JsonApiRequestValidatorMiddleware;

// Tranquillity controllers
use Tranquillity\Controllers\RootController;
use Tranquillity\Controllers\AuthController;
use Tranquillity\Controllers\UserController;
use Tranquillity\Controllers\PersonController;
use Tranquillity\Controllers\AccountController;
use Tranquillity\Controllers\TagController;

// Note to future self - DON'T TRY TO GET CLEVER WITH AUTO-GENERATING ROUTES
// See: https://phil.tech/php/2013/07/23/beware-the-route-to-evil/
// "routes.php is documentation"

return function (App $app) {
    // Version 1 API routes (unauthenticated)
    $app->get('/', RootController::class.':home');
    $app->post('/v1/auth/token', AuthController::class.':token');

    // Version 1 API route group (authenticated)
    $routeGroup = $app->group('/v1', function(RouteCollectorProxy $group) {
        // Tag resource
        $group->get('/tags', TagController::class.':list')->setName('tag-list');
        $group->post('/tags', TagController::class.':create');
        $group->get('/tags/{id}', TagController::class.':show')->setName('tag-detail');
        $group->patch('/tags/{id}', TagController::class.':update');
        $group->delete('/tags/{id}', TagController::class.':delete');
        $group->get('/tags/{id}/{resource}', TagController::class.':showRelated')->setName('tag-related');
        $group->get('/tags/{id}/relationships/{resource}', TagController::class.':showRelationship')->setName('tag-relationships');
        $group->post('/tags/{id}/relationships/{resource}', TagController::class.':addRelationship');
        $group->patch('/tags/{id}/relationships/{resource}', TagController::class.':updateRelationship');
        $group->delete('/tags/{id}/relationships/{resource}', TagController::class.':deleteRelationship');
        
        // User resource
        $group->get('/users', UserController::class.':list')->setName('user-list')->setArgument('auth-scope', 'users:read');
        $group->post('/users', UserController::class.':create')->setArgument('auth-scope', 'users:write');
        $group->get('/users/{id}', UserController::class.':show')->setName('user-detail')->setArgument('auth-scope', 'users:write');
        $group->patch('/users/{id}', UserController::class.':update')->setArgument('auth-scope', 'users:write');
        $group->delete('/users/{id}', UserController::class.':delete')->setArgument('auth-scope', 'users:write');
        $group->get('/users/{id}/{resource}', UserController::class.':showRelated')->setName('user-related')->setArgument('auth-scope', 'users:write');;
        $group->get('/users/{id}/relationships/{resource}', UserController::class.':showRelationship')->setName('user-relationships')->setArgument('auth-scope', 'users:write');;
        $group->post('/users/{id}/relationships/{resource}', UserController::class.':addRelationship')->setArgument('auth-scope', 'users:write');
        $group->patch('/users/{id}/relationships/{resource}', UserController::class.':updateRelationship')->setArgument('auth-scope', 'users:write');
        $group->delete('/users/{id}/relationships/{resource}', UserController::class.':deleteRelationship')->setArgument('auth-scope', 'users:write');
        
        // People resource
        $group->get('/people', PersonController::class.':list')->setName('person-list');
        $group->post('/people', PersonController::class.':create');
        $group->get('/people/{id}', PersonController::class.':show')->setName('person-detail');
        $group->patch('/people/{id}', PersonController::class.':update');
        $group->delete('/people/{id}', PersonController::class.':delete');
        $group->get('/people/{id}/{resource}', PersonController::class.':showRelated')->setName('person-related');
        $group->get('/people/{id}/relationships/{resource}', PersonController::class.':showRelationship')->setName('person-relationships');
        $group->post('/people/{id}/relationships/{resource}', PersonController::class.':addRelationship');
        $group->patch('/people/{id}/relationships/{resource}', PersonController::class.':updateRelationship');
        $group->delete('/people/{id}/relationships/{resource}', PersonController::class.':deleteRelationship');

        // Accounts resource
        $group->get('/accounts', AccountController::class.':list')->setName('accounts-list');
        $group->post('/accounts', AccountController::class.':create');
        $group->get('/accounts/{id}', AccountController::class.':show');
        $group->patch('/accounts/{id}', AccountController::class.':update');
        $group->delete('/accounts/{id}', AccountController::class.':delete');
    });

    // Version 1 API route group (authenticated) middleware
    $routeMiddleware = [
        AuthenticationMiddleware::class,
        JsonApiRequestValidatorMiddleware::class
    ];
    foreach ($routeMiddleware as $middleware) {
        $routeGroup->add($middleware);
    }
};