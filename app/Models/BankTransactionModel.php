<?php

namespace App\Models;

use CodeIgniter\Model;

class BankTransactionModel extends Model
{
    protected $table = 'bank_transactions';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = ['character_id', 'type', 'amount', 'transaction_hash', 'created_at'];
}