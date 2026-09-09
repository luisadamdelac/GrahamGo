<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Splits `location` into structured province / city_municipality /
 * barangay / street fields — same pattern as the name split in the
 * previous migration. `location` itself is kept, auto-computed by
 * UserModel from the four parts, so anything already displaying it as
 * one string keeps working unchanged.
 */
class MakeLocationCompositeOnUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'street'            => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'location'],
            'barangay'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'street'],
            'city_municipality' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'barangay'],
            'province'          => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'city_municipality'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', ['street', 'barangay', 'city_municipality', 'province']);
    }
}
