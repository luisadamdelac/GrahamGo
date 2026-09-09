<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Per-batch stock tracking for FIFO (First-In-First-Out) inventory. Every
 * stock-in event (new product, restock, cancelled-reservation return)
 * creates a batch row here; every stock-out event (reservation confirmed)
 * depletes the oldest batches first. products.stock stays as the fast
 * aggregate total — kept in sync alongside these rows — so none of the
 * existing stock checks/badges elsewhere in the app need to change.
 */
class CreateStockBatchesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'batch_id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'product_id'         => ['type' => 'INT', 'unsigned' => true],
            'quantity'           => ['type' => 'INT', 'unsigned' => true],
            'remaining_quantity' => ['type' => 'INT', 'unsigned' => true],
            'notes'              => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'received_at'        => ['type' => 'DATETIME'],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('batch_id', true);
        $this->forge->addKey(['product_id', 'received_at']);
        $this->forge->addForeignKey('product_id', 'products', 'product_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('stock_batches', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('stock_batches', true);
    }
}
