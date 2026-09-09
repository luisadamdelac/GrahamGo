<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryTransactionModel extends Model
{
    protected $table            = 'inventory_transactions';
    protected $primaryKey       = 'transaction_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'product_id', 'transaction_type', 'quantity', 'notes', 'transaction_date',
    ];

    public function log(int $productId, string $type, int $quantity, string $notes = ''): void
    {
        $this->insert([
            'product_id'       => $productId,
            'transaction_type' => $type,
            'quantity'         => $quantity,
            'notes'            => $notes,
            'transaction_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function withProduct(): array
    {
        return $this->select('inventory_transactions.*, products.product_name')
            ->join('products', 'products.product_id = inventory_transactions.product_id')
            ->orderBy('transaction_date', 'DESC')
            ->findAll();
    }
}
