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
$routes->get('game/battle', 'Game::battle');
$routes->post('game/attack', 'Game::attack');
$routes->post('game/flee', 'Game::flee');
$routes->post('game/rest', 'Game::rest');
$routes->get('game/state', 'Game::state');
$routes->get('game/inventory', 'Game::inventory');
$routes->post('game/equip', 'Game::equip');
$routes->post('game/unequip', 'Game::unequip');
$routes->post('game/use', 'Game::useItem');
