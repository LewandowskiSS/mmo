<?php

namespace App\Models;

use CodeIgniter\Model;

class ChestModel extends Model
{
    protected $table         = 'chests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name', 'emoji', 'key_type', 'key_price',
        'common_rate', 'uncommon_rate', 'rare_rate', 'epic_rate', 'legendary_rate',
    ];
}
