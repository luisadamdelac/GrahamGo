<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cancelling a Confirmed/Ready reservation now requires a reason (see
 * Owner\ReservationController::cancel()) — accountability for cancelling
 * an order the customer was already counting on, instead of it just
 * silently disappearing. Nullable: a Pending cancellation still doesn't
 * need one, and existing rows predate this.
 */
class AddCancelReasonToReservations extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('reservations', [
            'cancel_reason' => ['type' => 'TEXT', 'null' => true, 'after' => 'status'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('reservations', 'cancel_reason');
    }
}
