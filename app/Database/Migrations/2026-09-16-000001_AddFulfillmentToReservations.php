<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The business also does weekly delivery, not just counter pickup (per
 * owner interview) — reservations now record which one the customer
 * wants. delivery_address is only ever filled in when fulfillment_type
 * is 'Delivery' (enforced in Customer\ReservationController::store()),
 * nullable here since a Pickup reservation never has one.
 */
class AddFulfillmentToReservations extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('reservations', [
            'fulfillment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['Pickup', 'Delivery'],
                'default'    => 'Pickup',
                'after'      => 'claim_date',
            ],
            'delivery_address' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'fulfillment_type',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('reservations', ['fulfillment_type', 'delivery_address']);
    }
}
