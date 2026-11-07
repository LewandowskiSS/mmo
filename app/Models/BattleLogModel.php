<?php

namespace App\Models;

use CodeIgniter\Model;

class BattleLogModel extends Model
{
    protected $table         = 'battle_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['character_id', 'monster_id', 'result', 'xp_gained', 'gold_gained'];
}
