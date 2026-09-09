<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Widens inventory_transactions.transaction_type so walk-in (over-the-
 * counter, no online reservation) sales log distinctly from the
 * 'Sold' type used by the normal reservation-claim flow.
 */
class AddWalkInSaleTransactionType extends Migration
{
    public function up(): void
    {
        $this->db->query(
            "ALTER TABLE inventory_transactions MODIFY transaction_type " .
            "ENUM('Stock In','Reserved','Sold','Cancelled Return','Adjustment','Walk-in Sale') NOT NULL"
        );
    }

    public function down(): void
    {
        $this->db->query(
            "ALTER TABLE inventory_transactions MODIFY transaction_type " .
            "ENUM('Stock In','Reserved','Sold','Cancelled Return','Adjustment') NOT NULL"
        );
    }
}
