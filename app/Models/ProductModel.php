<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'product_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'product_name', 'description', 'price', 'stock', 'reorder_level', 'image', 'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'product_name' => 'required|max_length[100]',
        'price'        => 'required|decimal',
        'stock'        => 'required|integer|greater_than_equal_to[0]',
    ];

    public function activeProducts(): array
    {
        return $this->where('status', 'Active')->orderBy('product_name', 'ASC')->findAll();
    }

    public function lowStockCount(): int
    {
        return $this->where('status', 'Active')
            ->where('stock <= reorder_level', null, false)
            ->countAllResults();
    }

    public function adjustStock(int $productId, int $delta): void
    {
        $product = $this->find($productId);
        if ($product) {
            $newStock = max(0, (int) $product['stock'] + $delta);
            $this->update($productId, ['stock' => $newStock]);
        }
    }
}
