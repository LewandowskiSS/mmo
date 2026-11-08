<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// Главная — панель игры
$routes->get('/', 'Game::index');
$routes->get('walk', 'Game::walkPage');

$routes->get('view/(:num)', 'Profile::view/$1');
$routes->post('view/(:num)/comment', 'Profile::addComment/$1');
$routes->post('view/(:num)/comment/(:num)/like', 'Profile::toggleLike/$1/$2');
$routes->post('view/(:num)/comment/(:num)/pin', 'Profile::pinComment/$1/$2');
$routes->post('view/(:num)/comment/(:num)/delete', 'Profile::deleteComment/$1/$2');
$routes->get('view/(:num)/comments', 'Profile::comments/$1');
$routes->post('profile/bio', 'Profile::saveBio');

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
$routes->post('game/sell', 'Game::sell');
$routes->post('game/sellSelected', 'Game::sellSelected');
$routes->get('game/shop', 'Game::shop');
$routes->get('game/shop/(:segment)', 'Game::shopCategory/$1');
$routes->post('game/buy', 'Game::buy');
$routes->get('game/leaderboard', 'Game::leaderboard');
$routes->get('game/tasks', 'Game::tasks');
$routes->get('game/tasksState', 'Game::tasksState');
$routes->post('game/claimTask', 'Game::claimTask');
$routes->post('game/claimMilestone', 'Game::claimMilestone');
$routes->post('game/refreshTasks', 'Game::refreshTasks');
$routes->get('game/chests', 'Game::chests');
$routes->post('game/openChest', 'Game::openChest');
$routes->post('game/buyKey', 'Game::buyKey');
