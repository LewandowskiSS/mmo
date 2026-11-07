<?php

namespace App\Models;

use CodeIgniter\Model;

class EventLogModel extends Model
{
    protected $table         = 'event_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['character_id', 'type', 'message', 'rarity', 'emoji'];
}
