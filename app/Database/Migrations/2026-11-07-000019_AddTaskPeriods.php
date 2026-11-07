<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskPeriods extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('daily_tasks', [
            'period' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'daily'],
        ]);
        $this->forge->addColumn('character_tasks', [
            'period' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'daily'],
        ]);

        // Пул еженедельных заданий
        $weekly = [
            ['name' => 'Недельный маршрут', 'description' => 'Пройди 15 прогулок за неделю', 'type' => 'walk', 'target' => 15, 'reward_gold' => 150, 'reward_xp' => 30, 'min_level' => 1, 'emoji' => '🚶', 'period' => 'weekly'],
            ['name' => 'Охотник недели', 'description' => 'Победи 20 монстров за неделю', 'type' => 'kill', 'target' => 20, 'reward_gold' => 200, 'reward_xp' => 40, 'min_level' => 1, 'emoji' => '⚔️', 'period' => 'weekly'],
            ['name' => 'Серия побед', 'description' => 'Одержи 15 побед за неделю', 'type' => 'win', 'target' => 15, 'reward_gold' => 250, 'reward_xp' => 50, 'min_level' => 1, 'emoji' => '🏆', 'period' => 'weekly'],
            ['name' => 'Собиратель', 'description' => 'Найди 5 предметов за неделю', 'type' => 'item', 'target' => 5, 'reward_gold' => 150, 'reward_xp' => 30, 'min_level' => 1, 'emoji' => '🎒', 'period' => 'weekly'],
            ['name' => 'Марафонец', 'description' => '25 прогулок за неделю', 'type' => 'walk', 'target' => 25, 'reward_gold' => 220, 'reward_xp' => 45, 'min_level' => 1, 'emoji' => '🥾', 'period' => 'weekly'],
            ['name' => 'Гроза монстров', 'description' => 'Победи 30 монстров за неделю', 'type' => 'kill', 'target' => 30, 'reward_gold' => 280, 'reward_xp' => 55, 'min_level' => 1, 'emoji' => '💀', 'period' => 'weekly'],
            ['name' => 'Доминирование', 'description' => 'Одержи 20 побед за неделю', 'type' => 'win', 'target' => 20, 'reward_gold' => 300, 'reward_xp' => 60, 'min_level' => 1, 'emoji' => '🥇', 'period' => 'weekly'],
            ['name' => 'Кладоискатель', 'description' => 'Найди 10 предметов за неделю', 'type' => 'item', 'target' => 10, 'reward_gold' => 200, 'reward_xp' => 40, 'min_level' => 1, 'emoji' => '🧭', 'period' => 'weekly'],
        ];

        // Пул ежемесячных заданий
        $monthly = [
            ['name' => 'Месяц пути', 'description' => 'Пройди 60 прогулок за месяц', 'type' => 'walk', 'target' => 60, 'reward_gold' => 500, 'reward_xp' => 100, 'min_level' => 1, 'emoji' => '🚶', 'period' => 'monthly'],
            ['name' => 'Легенда охоты', 'description' => 'Победи 80 монстров за месяц', 'type' => 'kill', 'target' => 80, 'reward_gold' => 600, 'reward_xp' => 120, 'min_level' => 1, 'emoji' => '⚔️', 'period' => 'monthly'],
            ['name' => 'Непобедимый', 'description' => 'Одержи 60 побед за месяц', 'type' => 'win', 'target' => 60, 'reward_gold' => 700, 'reward_xp' => 140, 'min_level' => 1, 'emoji' => '🏆', 'period' => 'monthly'],
            ['name' => 'Мастер добычи', 'description' => 'Найди 20 предметов за месяц', 'type' => 'item', 'target' => 20, 'reward_gold' => 500, 'reward_xp' => 100, 'min_level' => 1, 'emoji' => '🧭', 'period' => 'monthly'],
            ['name' => 'Тысяча шагов', 'description' => '100 прогулок за месяц', 'type' => 'walk', 'target' => 100, 'reward_gold' => 800, 'reward_xp' => 150, 'min_level' => 1, 'emoji' => '🥾', 'period' => 'monthly'],
            ['name' => 'Мясорубка', 'description' => 'Победи 120 монстров за месяц', 'type' => 'kill', 'target' => 120, 'reward_gold' => 900, 'reward_xp' => 160, 'min_level' => 1, 'emoji' => '💀', 'period' => 'monthly'],
            ['name' => 'Чемпион месяца', 'description' => 'Одержи 90 побед за месяц', 'type' => 'win', 'target' => 90, 'reward_gold' => 950, 'reward_xp' => 180, 'min_level' => 1, 'emoji' => '🥇', 'period' => 'monthly'],
            ['name' => 'Сокровищница', 'description' => 'Найди 30 предметов за месяц', 'type' => 'item', 'target' => 30, 'reward_gold' => 700, 'reward_xp' => 130, 'min_level' => 1, 'emoji' => '🗝️', 'period' => 'monthly'],
            ['name' => 'Дальний поход', 'description' => '80 прогулок за месяц', 'type' => 'walk', 'target' => 80, 'reward_gold' => 750, 'reward_xp' => 140, 'min_level' => 1, 'emoji' => '🗺️', 'period' => 'monthly'],
            ['name' => 'Багровый клинок', 'description' => 'Победи 100 монстров за месяц', 'type' => 'kill', 'target' => 100, 'reward_gold' => 850, 'reward_xp' => 150, 'min_level' => 1, 'emoji' => '🗡️', 'period' => 'monthly'],
        ];

        $this->db->table('daily_tasks')->insertBatch(array_merge($weekly, $monthly));
    }

    public function down(): void
    {
        $this->db->table('daily_tasks')->where('period !=', 'daily')->delete();
        $this->forge->dropColumn('daily_tasks', 'period');
        $this->forge->dropColumn('character_tasks', 'period');
    }
}
