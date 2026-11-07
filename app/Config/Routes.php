<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Главная — панель игры
$routes->get('/', 'Game::index');

// Авторизация
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::register');
$routes->get('logout', 'Auth::logout');

// Игровые действия
$routes->post('game/walk', 'Game::walk');
$routes->post('game/skip', 'Game::skip');
$routes->get('game/battle', 'Game::battle');
$routes->post('game/attack', 'Game::attack');
$routes->post('game/flee', 'Game::flee');
$routes->post('game/rest', 'Game::rest');
$routes->get('game/state', 'Game::state');
$routes->get('game/inventory', 'Game::inventory');
$routes->post('game/equip', 'Game::equip');
$routes->post('game/unequip', 'Game::unequip');
$routes->post('game/use', 'Game::useItem');
$routes->get('game/shop', 'Game::shop');
$routes->post('game/buy', 'Game::buy');
$routes->get('game/leaderboard', 'Game::leaderboard');
$routes->get('game/tasks', 'Game::tasks');
$routes->post('game/claimTask', 'Game::claimTask');
$routes->get('game/chests', 'Game::chests');
$routes->post('game/openChest', 'Game::openChest');
$routes->post('game/buyKey', 'Game::buyKey');
