<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DailyTaskSeeder extends Seeder
{
    public function run(): void
    {
        $monsters = $this->monsterIds();
        $tasks    = $this->buildPool($monsters);

        foreach ($tasks as $row) {
            $existing = $this->db->table('daily_tasks')
                ->where('name', $row['name'])
                ->where('period', $row['period'])
                ->get()
                ->getRow();

            if ($existing) {
                // Шаблон уже есть — обновляем только безопасные метаданные, сохраняя id и связанные character_tasks
                $this->db->table('daily_tasks')->where('id', $existing->id)->update([
                    'description'       => $row['description'],
                    'type'              => $row['type'],
                    'target_monster_id' => $row['target_monster_id'],
                    'target'            => $row['target'],
                    'reward_gold'       => $row['reward_gold'],
                    'reward_xp'         => $row['reward_xp'],
                    'min_level'         => $row['min_level'],
                    'emoji'             => $row['emoji'],
                ]);
            } else {
                $this->db->table('daily_tasks')->insert($row);
            }
        }

        // Чистка: удаляем старые справочные шаблоны, которых нет в новом пуле и которые не
        // используются никакими активными заданиями (активные character_tasks с прогрессом не трогаем).
        $keepNames = array_column($tasks, 'name');
        $allRefs   = $this->db->table('character_tasks')->select('task_id')->get()->getResult();
        $referenced = [];
        foreach ($allRefs as $ref) {
            $referenced[(int) $ref->task_id] = true;
        }
        $orphanRows = $this->db->table('daily_tasks')->get()->getResult();
        foreach ($orphanRows as $orphan) {
            if (in_array($orphan->name, $keepNames, true)) {
                continue;
            }
            if (! isset($referenced[(int) $orphan->id])) {
                $this->db->table('daily_tasks')->where('id', $orphan->id)->delete();
            }
        }
    }

    /** ID монстров по именам (привязка target_monster_id к реальным записям). */
    private function monsterIds(): array
    {
        $names = ['Слизень', 'Лесной волк', 'Гоблин', 'Скелет', 'Темный рыцарь', 'Дракон', 'Лич', 'Демон', 'Гидра'];
        $rows  = $this->db->table('monsters')->whereIn('name', $names)->select('id, name')->get()->getResult();
        $ids   = [];
        foreach ($rows as $r) {
            $ids[$r->name] = (int) $r->id;
        }
        return $ids;
    }

    private function buildPool(array $monsters): array
    {
        $tasks = [];

        // ===== Добыча руды («Рудокоп»): daily 5 / weekly 25 / monthly 90 =====
        $tasks[] = $this->spec('Рудокоп', 'Добудь 5 единиц руды за день', 'mine', 5, 40, 25, 1, '/img/tools/stone_pickaxe.png', 'daily');
        $tasks[] = $this->spec('Рудокоп', 'Добудь 25 единиц руды за неделю', 'mine', 25, 180, 90, 1, '/img/tools/stone_pickaxe.png', 'weekly');
        $tasks[] = $this->spec('Рудокоп', 'Добудь 90 единиц руды за месяц', 'mine', 90, 500, 250, 1, '/img/tools/stone_pickaxe.png', 'monthly');

        $tasks = array_merge($tasks, $this->huntTasks($monsters));

        // ===== Прогулки (2 на период) =====
        $tasks[] = $this->spec('Лёгкая прогулка', 'Пройди 5 прогулок за день', 'walk', 5, 20, 10, 1, '/img/misc/map.png', 'daily');
        $tasks[] = $this->spec('Марафон дня', 'Пройди 10 прогулок за день', 'walk', 10, 45, 30, 5, '/img/misc/map.png', 'daily');
        $tasks[] = $this->spec('Большой маршрут', 'Пройди 40 прогулок за неделю', 'walk', 40, 150, 90, 1, '/img/misc/map.png', 'weekly');
        $tasks[] = $this->spec('Скиталец', 'Пройди 60 прогулок за неделю', 'walk', 60, 260, 140, 10, '/img/misc/map.png', 'weekly');
        $tasks[] = $this->spec('Далекая дорога', 'Пройди 150 прогулок за месяц', 'walk', 150, 500, 300, 1, '/img/misc/map.png', 'monthly');
        $tasks[] = $this->spec('Странник', 'Пройди 220 прогулок за месяц', 'walk', 220, 780, 420, 15, '/img/misc/map.png', 'monthly');

        // ===== Находки (2 на период) =====
        $tasks[] = $this->spec('Странное открытие', 'Найди 2 предмета за день', 'item', 2, 30, 20, 1, '/img/misc/bag.png', 'daily');
        $tasks[] = $this->spec('Сокровища дня', 'Найди 4 предмета за день', 'item', 4, 70, 45, 6, '/img/misc/bag.png', 'daily');
        $tasks[] = $this->spec('Собиратель недели', 'Найди 10 предметов за неделю', 'item', 10, 150, 90, 1, '/img/misc/bag.png', 'weekly');
        $tasks[] = $this->spec('Хранитель сокровищ', 'Найди 15 предметов за неделю', 'item', 15, 300, 180, 12, '/img/misc/bag.png', 'weekly');
        $tasks[] = $this->spec('Лавина находок', 'Найди 40 предметов за месяц', 'item', 40, 500, 300, 1, '/img/misc/bag.png', 'monthly');
        $tasks[] = $this->spec('Драгоценный месяц', 'Найди 60 предметов за месяц', 'item', 60, 880, 520, 18, '/img/misc/bag.png', 'monthly');

        // ===== Победы (2 на период) =====
        $tasks[] = $this->spec('Первые победы', 'Выиграй 2 боя за день', 'win', 2, 40, 25, 1, '/img/misc/quests.png', 'daily');
        $tasks[] = $this->spec('Победоносный день', 'Выиграй 6 боёв за день', 'win', 6, 100, 60, 8, '/img/misc/quests.png', 'daily');
        $tasks[] = $this->spec('Неутомимый воин', 'Выиграй 15 боёв за неделю', 'win', 15, 250, 120, 1, '/img/misc/quests.png', 'weekly');
        $tasks[] = $this->spec('Турнир недели', 'Выиграй 25 боёв за неделю', 'win', 25, 420, 220, 12, '/img/misc/quests.png', 'weekly');
        $tasks[] = $this->spec('Месяц побед', 'Выиграй 50 боёв за месяц', 'win', 50, 700, 400, 1, '/img/misc/quests.png', 'monthly');
        $tasks[] = $this->spec('Бессмертная слава', 'Выиграй 80 боёв за месяц', 'win', 80, 1200, 700, 15, '/img/misc/quests.png', 'monthly');

        return $tasks;
    }

    /** Охота на конкретных монстров (kill_monster), привязка к минимальному уровню монстра. */
    private function huntTasks(array $monsters): array
    {
        $tasks = [];

        // [имя задания, монстр, цели (daily, weekly, monthly)]
        $hunts = [
            ['Охота на слизней',     'Слизень',       [3, 10, 40]],
            ['Зачистка лесных волков', 'Лесной волк', [3, 8, 30]],
            ['Гроза гоблинов',       'Гоблин',        [2, 6, 25]],
            ['Костолом',            'Скелет',        [2, 6, 20]],
            ['Охота на тёмных рыцарей', 'Темный рыцарь', [2, 4, 12]],
            ['Охотник на дракона',  'Дракон',        [1, 3, 8]],
            ['Охота на личей',      'Лич',           [1, 2, 5]],
            ['Охотник на демонов',  'Демон',         [1, 2, 3]],
            ['Разделка гидры',      'Гидра',         [1, 1, 2]],
        ];

        $periods = ['daily', 'weekly', 'monthly'];
        $suffix  = ['за день', 'за неделю', 'за месяц'];

        $dailyRewards   = [30, 40, 45, 55, 70, 150, 200, 250, 300];
        $dailyXp        = [15, 20, 25, 30, 40, 90, 120, 150, 180];
        $weeklyRewards  = [120, 140, 160, 180, 250, 500, 650, 800, 1000];
        $weeklyXp       = [60, 70, 85, 100, 130, 320, 420, 520, 650];
        $monthlyRewards = [450, 520, 580, 650, 900, 1800, 2200, 2600, 3200];
        $monthlyXp      = [225, 260, 300, 350, 480, 1200, 1500, 1800, 2200];

        foreach ($hunts as $i => $hunt) {
            [$name, $monster, $targets] = $hunt;
            $monsterId = $monsters[$monster] ?? null;
            if ($monsterId === null) {
                continue;
            }

            $rewardSets = [$dailyRewards, $weeklyRewards, $monthlyRewards];
            $xpSets     = [$dailyXp, $weeklyXp, $monthlyXp];
            $minLevel   = (int) $this->db->table('monsters')->where('id', $monsterId)->get()->getRow('min_level');

            foreach ($periods as $pi => $period) {
                $tasks[] = $this->spec(
                    $name,
                    "Убей {$targets[$pi]} {$this->monsterPlural($monster)} {$suffix[$pi]}",
                    'kill_monster',
                    $targets[$pi],
                    $rewardSets[$pi][$i],
                    $xpSets[$pi][$i],
                    $minLevel,
                    $this->monsterIcon($monster, $monsterId),
                    $period,
                    $monsterId
                );
            }
        }

        return $tasks;
    }

    private function spec(string $name, string $description, string $type, int $target, int $rewardGold, int $rewardXp, int $minLevel, string $emoji, string $period, ?int $monsterId = null): array
    {
        return [
            'name'                => $name,
            'description'         => $description,
            'type'                => $type,
            'target_monster_id'   => $monsterId,
            'target'              => $target,
            'reward_gold'         => $rewardGold,
            'reward_xp'           => $rewardXp,
            'min_level'           => $minLevel,
            'emoji'               => $emoji,
            'period'              => $period,
        ];
    }

    /** Винительная форма имени монстра для описания задания. */
    private function monsterPlural(string $name): string
    {
        $map = [
            'Слизень'        => 'слизней',
            'Лесной волк'   => 'лесных волков',
            'Гоблин'        => 'гоблинов',
            'Скелет'        => 'скелетов',
            'Темный рыцарь' => 'тёмных рыцарей',
            'Дракон'        => 'дракона',
            'Лич'           => 'лича',
            'Демон'         => 'демона',
            'Гидра'         => 'гидру',
        ];
        return $map[$name] ?? mb_strtolower($name);
    }

    /** Иконка задания охоты — PNG монстра из /img/monsters/. */
    private function monsterIcon(string $name, int $monsterId): string
    {
        $map = [
            'Слизень'        => 'slime',
            'Лесной волк'   => 'wolf',
            'Гоблин'        => 'goblin',
            'Скелет'        => 'skeleton',
            'Темный рыцарь' => 'dark_knight',
            'Дракон'        => 'dragon',
            'Лич'           => 'lich',
            'Демон'         => 'demon',
            'Гидра'         => 'hydra',
        ];
        $slug = $map[$name] ?? null;
        if ($slug === null) {
            return '/img/misc/fight.png';
        }
        $icon = $this->db->table('monsters')->where('id', $monsterId)->get()->getRow('emoji');
        return ($icon !== null && $icon !== '' && str_starts_with((string) $icon, '/img/monsters/'))
            ? (string) $icon
            : '/img/monsters/' . $slug . '.png';
    }
}
