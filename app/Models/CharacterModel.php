<?php

namespace App\Models;

use CodeIgniter\Model;

class CharacterModel extends Model
{
    protected $table         = 'characters';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'user_id', 'name', 'tag', 'gender', 'bio', 'motto', 'level', 'xp', 'gold',
        'hp', 'max_hp', 'attack', 'defense',
        'energy', 'max_energy', 'last_energy_at', 'last_step_at',
        'bronze_keys', 'silver_keys', 'gold_keys', 'diamonds',
        'steps_total', 'npc_kills', 'boss_kills', 'quests_done', 'player_kills', 'chests_opened',
    ];
}
