<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSitioToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'sitio' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'street'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'sitio');
    }
}
