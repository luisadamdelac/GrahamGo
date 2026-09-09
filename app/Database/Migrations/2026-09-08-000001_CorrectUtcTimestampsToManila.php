<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The app was running under UTC (app.appTimezone) since the project
 * started, so every timestamp written via PHP's date()/CI4 model
 * timestamps — reservation dates, sale dates, created_at, etc. — is 8
 * hours behind actual Philippine time (Asia/Manila, UTC+8). Now that
 * appTimezone is fixed to Asia/Manila (see app/Config/App.php), new rows
 * are correct going forward; this one-time migration shifts every
 * already-stored datetime/timestamp column by +8 hours to match. Pure
 * DATE columns (reservations.claim_date) are untouched — a calendar date
 * the customer picked has no time-of-day component to be wrong about.
 */
class CorrectUtcTimestampsToManila extends Migration
{
    private const SHIFT = 'INTERVAL 8 HOUR';

    /** @var array<string, list<string>> table => datetime/timestamp columns to shift */
    private const COLUMNS = [
        'inventory_transactions' => ['transaction_date'],
        'password_resets'        => ['created_at', 'expires_at'],
        'payments'                => ['payment_date'],
        'products'                => ['created_at', 'updated_at'],
        'reservations'            => ['created_at', 'reservation_date', 'updated_at'],
        'sales'                   => ['sale_date'],
        'settings'                => ['updated_at'],
        'stock_batches'           => ['created_at', 'received_at'],
        'users'                   => ['created_at', 'terms_accepted_at', 'updated_at'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $this->db->query(
                    "UPDATE {$table} SET {$column} = DATE_ADD({$column}, " . self::SHIFT . ") WHERE {$column} IS NOT NULL"
                );
            }
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $this->db->query(
                    "UPDATE {$table} SET {$column} = DATE_SUB({$column}, " . self::SHIFT . ") WHERE {$column} IS NOT NULL"
                );
            }
        }
    }
}
