<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfileCommentModel extends Model
{
    protected $table         = 'profile_comments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['character_id', 'author_id', 'parent_id', 'message', 'likes', 'pinned'];
}
