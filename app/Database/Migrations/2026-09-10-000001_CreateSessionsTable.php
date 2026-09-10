<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Schema CodeIgniter's DatabaseHandler expects (see Session config —
 * matchIP is false here, so the primary key is `id` alone, not combined
 * with ip_address). Only actually used when SESSION_DRIVER=database is
 * set; harmless to have this table exist unused otherwise.
 */
class CreateSessionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'timestamp'  => ['type' => 'TIMESTAMP', 'null' => false, 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
            'data'       => ['type' => 'BLOB', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('timestamp');
        $this->forge->createTable('ci_sessions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('ci_sessions', true);
    }
}
