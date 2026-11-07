<?php

namespace App\Models;

use CodeIgniter\Model;

class CharacterTaskModel extends Model
{
    protected $table         = 'character_tasks';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['character_id', 'task_id', 'progress', 'claimed', 'task_date', 'period', 'target', 'reward_gold', 'reward_xp', 'milestones_claimed'];
}
