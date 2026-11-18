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
        'stat_points', 'allocated_attack', 'allocated_defense', 'bonus_attack', 'bonus_defense',
        'energy', 'max_energy', 'last_energy_at', 'last_hp_at', 'last_step_at',
        'bronze_keys', 'silver_keys', 'gold_keys', 'diamonds', 'bank_balance',
        'steps_total', 'npc_kills', 'boss_kills', 'quests_done', 'player_kills', 'chests_opened',
        'heal_count', 'heal_last_date', 'healer_intro_seen',
        'location_id',
        'mining_level', 'mining_xp',
        'crafting_level', 'crafting_xp', 'crafting_claimed_level',
    ];
}
