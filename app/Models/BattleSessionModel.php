<?php

namespace App\Models;

use CodeIgniter\Model;

class BattleSessionModel extends Model
{
    protected $table         = 'battle_sessions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'battle_hash',
        'character_id',
        'location_id',
        'monster_id',
        'monster_name',
        'monster_emoji',
        'monster_level',
        'monster_hp',
        'monster_max_hp',
        'monster_attack',
        'monster_defense',
        'status',
        'last_attack_at',
        'created_at',
    ];
}
