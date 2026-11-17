<?php

namespace App\Models;

use CodeIgniter\Model;

class CraftingSessionModel extends Model
{
    protected $table            = 'crafting_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'character_id',
        'recipe_slug',
        'result_item_id',
        'quantity',
        'energy_spent',
        'crafting_xp_reward',
        'player_xp_reward',
        'started_at',
        'finishes_at',
        'status',
        'created_at',
    ];

    protected $useTimestamps = false;
}
