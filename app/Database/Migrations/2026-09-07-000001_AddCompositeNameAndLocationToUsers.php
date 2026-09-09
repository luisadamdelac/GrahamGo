<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Splits the single `name` column into structured last_name / first_name
 * / middle_name fields, and adds `location`. `name` itself is kept (not
 * dropped) — UserModel now auto-computes it from the three parts on
 * every insert/update, so every existing query/view/email that already
 * reads `users.name` keeps working unchanged.
 */
class AddCompositeNameAndLocationToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'last_name'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'name'],
            'first_name'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'last_name'],
            'middle_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'first_name'],
            'location'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'contact_number'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['last_name', 'first_name', 'middle_name', 'location']);
    }
}
