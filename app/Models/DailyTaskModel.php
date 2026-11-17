<?php

namespace App\Models;

use CodeIgniter\Model;

class DailyTaskModel extends Model
{
    protected $table         = 'daily_tasks';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'description', 'type', 'target_monster_id', 'target', 'reward_gold', 'reward_xp', 'min_level', 'emoji', 'period'];
}
