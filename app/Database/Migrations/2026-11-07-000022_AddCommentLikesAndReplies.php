<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommentLikesAndReplies extends Migration
{
    public function up(): void
    {
        // Колонки в profile_comments (идемпотентно — проверяем наличие)
        $cols = $this->db->query("SHOW COLUMNS FROM profile_comments LIKE 'parent_id'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('profile_comments', [
                'parent_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true, 'after' => 'author_id'],
            ]);
        }
        $cols = $this->db->query("SHOW COLUMNS FROM profile_comments LIKE 'likes'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('profile_comments', [
                'likes' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'default' => 0, 'after' => 'message'],
            ]);
        }
        $cols = $this->db->query("SHOW COLUMNS FROM profile_comments LIKE 'pinned'")->getNumRows();
        if ($cols === 0) {
            $this->forge->addColumn('profile_comments', [
                'pinned' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'default' => 0, 'after' => 'likes'],
            ]);
        }

        // FK parent_id → profile_comments.id (сырой запрос, таблица уже существует)
        $fk = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                                WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profile_comments'
                                AND COLUMN_NAME = 'parent_id' AND REFERENCED_TABLE_NAME IS NOT NULL")->getNumRows();
        if ($fk === 0) {
            $this->db->query('ALTER TABLE profile_comments ADD CONSTRAINT profile_comments_parent_id_foreign
                FOREIGN KEY (parent_id) REFERENCES profile_comments(id) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        // Таблица лайков
        if (! $this->db->tableExists('comment_likes')) {
            $this->forge->addField([
                'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'comment_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'character_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['comment_id', 'character_id']);
            $this->forge->addForeignKey('comment_id', 'profile_comments', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('character_id', 'characters', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('comment_likes');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('comment_likes')) {
            $this->forge->dropTable('comment_likes');
        }
        $this->forge->dropColumn('profile_comments', ['parent_id', 'likes', 'pinned']);
    }
}
