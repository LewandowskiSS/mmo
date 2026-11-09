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

                // Есть ли доступные награды по заданиям (для кружка в сайдбаре)
                $hasClaimableTasks = false;
                $taskKeys = [date('Y-m-d'), date('Y-m-d', strtotime('monday this week')), date('Y-m-01')];
                $taskRows = (new \App\Models\CharacterTaskModel())
                    ->where('character_id', $character->id)
                    ->whereIn('task_date', $taskKeys)
                    ->findAll();
                $byPeriod = [];
                foreach ($taskRows as $r) {
                    $byPeriod[$r->period][] = $r;
                    if ((int) $r->progress >= (int) $r->target && ! (bool) $r->claimed) {
                        $hasClaimableTasks = true;
                    }
                }
                if (! $hasClaimableTasks) {
                    foreach ($byPeriod as $list) {
                        $total = count($list);
                        $done  = 0;
                        foreach ($list as $r) {
                            if ((bool) $r->claimed || (int) $r->progress >= (int) $r->target) {
                                $done++;
                            }
                        }
                        $percent = $total > 0 ? (int) floor($done / $total * 100) : 0;
                        $claimed = array_filter(explode(',', (string) ($list[0]->milestones_claimed ?? '')));
                        foreach ([25, 50, 75, 100] as $p) {
                            if ($percent >= $p && ! in_array((string) $p, $claimed, true)) {
                                $hasClaimableTasks = true;
                                break 2;
                            }
                        }
                    }
                }

                self::$layoutData = [
                    'characterName'      => $character->name,
                    'characterTag'       => $character->tag ?? '0000',
                    'characterGender'    => $character->gender ?? 'male',
                    'characterLevel'     => (int) $character->level,
                    'characterXpPercent' => min(100, (int) round($character->xp / $xpNeeded * 100)),
                    'characterGold'      => (int) $character->gold,
                    'characterEnergy'    => (int) $character->energy,
                    'characterMaxEnergy' => (int) $character->max_energy,
                    'characterHp'        => (int) $character->hp,
                    'characterMaxHp'     => (int) $character->max_hp,
                    'characterDiamonds'  => (int) ($character->diamonds ?? 0),
                    'characterWalks'     => (int) ($character->steps_total ?? 0),
                    'hasClaimableTasks'  => $hasClaimableTasks,
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

    protected function currentCharacter()
    {
        if (! $this->session->get('user_id')) {
            return null;
        }
        $id = $this->session->get('character_id');
        if (! $id) {
            return null;
        }
        return (new \App\Models\CharacterModel())->find($id);
    }
}
