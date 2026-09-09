<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomerTypeOtherToUsers extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('users', [
            'customer_type_other' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'customer_type'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'customer_type_other');
    }
}
