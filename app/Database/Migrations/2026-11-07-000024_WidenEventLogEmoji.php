<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WidenEventLogEmoji extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE event_log MODIFY emoji VARCHAR(255) NULL");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE event_log MODIFY emoji VARCHAR(20) NULL");
    }
}
