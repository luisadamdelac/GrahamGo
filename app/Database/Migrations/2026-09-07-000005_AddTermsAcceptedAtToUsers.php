<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTermsAcceptedAtToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'terms_accepted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'terms_accepted_at');
    }
}
