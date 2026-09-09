<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGrahamGoTables extends Migration
{
    public function up(): void
    {
        // ---------------------------------------------------------------
        // Users
        // ---------------------------------------------------------------
        $this->forge->addField([
            'user_id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'            => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'password'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'contact_number'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'customer_type'   => ['type' => 'ENUM', 'constraint' => ['Student', 'Faculty', 'Staff', 'Other'], 'default' => 'Student'],
            'role'            => ['type' => 'ENUM', 'constraint' => ['customer', 'owner'], 'default' => 'customer'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('user_id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);

        // ---------------------------------------------------------------
        // Products
        // ---------------------------------------------------------------
        $this->forge->addField([
            'product_id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'product_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'stock'          => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'reorder_level'  => ['type' => 'INT', 'unsigned' => true, 'default' => 5],
            'image'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Active', 'Inactive'], 'default' => 'Active'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('product_id', true);
        $this->forge->createTable('products', true);

        // ---------------------------------------------------------------
        // Reservations
        // ---------------------------------------------------------------
        $this->forge->addField([
            'reservation_id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'          => ['type' => 'INT', 'unsigned' => true],
            'reservation_date' => ['type' => 'DATETIME'],
            'claim_date'       => ['type' => 'DATE'],
            'total_amount'     => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'payment_status'   => ['type' => 'ENUM', 'constraint' => ['Unpaid', 'Paid'], 'default' => 'Unpaid'],
            'status'           => ['type' => 'ENUM', 'constraint' => ['Pending', 'Confirmed', 'Ready', 'Claimed', 'Cancelled'], 'default' => 'Pending'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('reservation_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reservations', true);

        // ---------------------------------------------------------------
        // Reservation Details
        // ---------------------------------------------------------------
        $this->forge->addField([
            'detail_id'      => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reservation_id' => ['type' => 'INT', 'unsigned' => true],
            'product_id'     => ['type' => 'INT', 'unsigned' => true],
            'quantity'       => ['type' => 'INT', 'unsigned' => true],
            'price'          => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'subtotal'       => ['type' => 'DECIMAL', 'constraint' => '10,2'],
        ]);
        $this->forge->addKey('detail_id', true);
        $this->forge->addForeignKey('reservation_id', 'reservations', 'reservation_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('reservation_details', true);

        // ---------------------------------------------------------------
        // Payments
        // ---------------------------------------------------------------
        $this->forge->addField([
            'payment_id'     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reservation_id' => ['type' => 'INT', 'unsigned' => true],
            'amount_paid'    => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'payment_method' => ['type' => 'ENUM', 'constraint' => ['Cash', 'GCash', 'Other'], 'default' => 'Cash'],
            'payment_date'   => ['type' => 'DATETIME'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['Paid', 'Refunded'], 'default' => 'Paid'],
        ]);
        $this->forge->addKey('payment_id', true);
        $this->forge->addForeignKey('reservation_id', 'reservations', 'reservation_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments', true);

        // ---------------------------------------------------------------
        // Sales
        // ---------------------------------------------------------------
        $this->forge->addField([
            'sale_id'        => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'reservation_id' => ['type' => 'INT', 'unsigned' => true],
            'payment_id'     => ['type' => 'INT', 'unsigned' => true],
            'sale_date'      => ['type' => 'DATETIME'],
            'total_amount'   => ['type' => 'DECIMAL', 'constraint' => '10,2'],
        ]);
        $this->forge->addKey('sale_id', true);
        $this->forge->addUniqueKey('reservation_id');
        $this->forge->addForeignKey('reservation_id', 'reservations', 'reservation_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('payment_id', 'payments', 'payment_id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('sales', true);

        // ---------------------------------------------------------------
        // Inventory Transactions
        // ---------------------------------------------------------------
        $this->forge->addField([
            'transaction_id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'product_id'       => ['type' => 'INT', 'unsigned' => true],
            'transaction_type' => ['type' => 'ENUM', 'constraint' => ['Stock In', 'Reserved', 'Sold', 'Cancelled Return', 'Adjustment']],
            'quantity'         => ['type' => 'INT'],
            'notes'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'transaction_date' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('transaction_id', true);
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('inventory_transactions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('inventory_transactions', true);
        $this->forge->dropTable('sales', true);
        $this->forge->dropTable('payments', true);
        $this->forge->dropTable('reservation_details', true);
        $this->forge->dropTable('reservations', true);
        $this->forge->dropTable('products', true);
        $this->forge->dropTable('users', true);
    }
}
