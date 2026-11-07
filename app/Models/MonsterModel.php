<?php

namespace App\Models;

use CodeIgniter\Model;

class MonsterModel extends Model
{
    protected $table         = 'monsters';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name', 'level', 'hp', 'attack', 'defense',
        'xp_reward', 'gold_min', 'gold_max', 'emoji',
    ];
}
