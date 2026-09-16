<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Free-form note the owner can attach when confirming or marking a
 * reservation ready — e.g. where to pick up if not the usual spot, or
 * any other instruction for the customer. General-purpose rather than
 * a fixed set of choices (like a "School"/"Home" toggle) since the
 * owner's situation varies day to day; shown on the customer's
 * reservation detail page, same idea as cancel_reason.
 */
class AddOwnerNoteToReservations extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('reservations', [
            'owner_note' => ['type' => 'TEXT', 'null' => true, 'after' => 'cancel_reason'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('reservations', 'owner_note');
    }
}
