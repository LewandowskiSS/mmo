<?php

namespace App\Models;

use CodeIgniter\Model;

class FlavorTextModel extends Model
{
    protected $table         = 'flavor_texts';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['text'];
}
