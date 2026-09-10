<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * One review per (customer, product) — a customer who claims the same
 * product again can update their existing review instead of stacking a
 * second one. Reviews start Pending and only count toward a product's
 * public rating/listing once an owner approves them (see
 * Owner\ReviewController).
 */
class CreateReviewsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'review_id'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'     => ['type' => 'INT', 'unsigned' => true],
            'product_id'  => ['type' => 'INT', 'unsigned' => true],
            'rating'      => ['type' => 'TINYINT', 'unsigned' => true],
            'comment'     => ['type' => 'TEXT', 'null' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['Pending', 'Approved', 'Rejected'], 'default' => 'Pending'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('review_id');
        $this->forge->addUniqueKey(['user_id', 'product_id']);
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', '', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'product_id', '', 'CASCADE');
        $this->forge->createTable('reviews', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('reviews', true);
    }
}
