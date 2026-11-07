<?php

namespace App\Models;

use CodeIgniter\Model;

class MonsterModel extends Model
{
    protected $table         = 'monsters';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['name', 'emoji', 'min_level'];
}
