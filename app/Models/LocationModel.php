<?php

namespace App\Models;

use CodeIgniter\Model;

class LocationModel extends Model
{
    protected $table      = 'locations';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'slug',
        'name',
        'description',
        'image',
        'min_level',
        'travel_steps',
        'travel_cost',
        'is_wip',
        'chance_text',
        'chance_item',
        'chance_monster',
        'sort_order',
    ];
    protected $useTimestamps = false;
}