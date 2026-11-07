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
        'user_id', 'name', 'level', 'xp', 'gold',
        'hp', 'max_hp', 'attack', 'defense',
        'energy', 'max_energy', 'last_energy_at', 'last_step_at',
    ];
}
