<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $session;

    /**
     * @param CLIRequest|IncomingRequest $request
     *
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Хелперы загружаются до вызова родительского метода.
        $this->helpers = ['form'];

        parent::initController($request, $response, $logger);

        // Сервисы/модели — после.
        $this->session = service('session');

        // Данные персонажа для хедера/меню профиля в лейауте.
        if ($this->session->get('character_id')) {
            $character = (new \App\Models\CharacterModel())->find($this->session->get('character_id'));
            if ($character) {
                $xpNeeded = 50 + ((int) $character->level - 1) * 25;
                self::$layoutData = [
                    'characterName'      => $character->name,
                    'characterLevel'     => (int) $character->level,
                    'characterXpPercent' => min(100, (int) round($character->xp / $xpNeeded * 100)),
                    'characterGold'      => (int) $character->gold,
                    'characterEnergy'    => (int) $character->energy,
                    'characterMaxEnergy' => (int) $character->max_energy,
                    'characterHp'        => (int) $character->hp,
                    'characterMaxHp'     => (int) $character->max_hp,
                    'characterDiamonds'  => (int) ($character->diamonds ?? 0),
                ];
            }
        }
    }

    // Данные лейаута (доступны в шаблонах через статический вызов).
    public static array $layoutData = [];

    protected function isLoggedIn(): bool
    {
        return (bool) $this->session->get('user_id');
    }
}
