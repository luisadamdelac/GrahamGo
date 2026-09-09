<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GrahamGoSeeder extends Seeder
{
    public function run(): void
    {
        // Owner account
        $ownerExists = $this->db->table('users')->where('email', 'owner@grahamgo.test')->countAllResults();
        if (! $ownerExists) {
            $this->db->table('users')->insert([
                'name'           => 'GrahamGo Owner',
                'email'          => 'owner@grahamgo.test',
                'password'       => password_hash('owner123', PASSWORD_DEFAULT),
                'contact_number' => '09171234567',
                'customer_type'  => 'Other',
                'role'           => 'owner',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }

        // Sample products
        $products = [
            [
                'product_name'  => 'Graham Mango',
                'description'   => 'Layers of crushed graham, creamy custard, and fresh mango slices.',
                'price'         => 60.00,
                'stock'         => 30,
                'reorder_level' => 5,
                'status'        => 'Active',
            ],
            [
                'product_name'  => 'Oreo Graham',
                'description'   => 'Classic graham dessert layered with crushed Oreo cookies and creamy filling.',
                'price'         => 60.00,
                'stock'         => 30,
                'reorder_level' => 5,
                'status'        => 'Active',
            ],
        ];

        foreach ($products as $product) {
            $exists = $this->db->table('products')->where('product_name', $product['product_name'])->countAllResults();
            if (! $exists) {
                $product['created_at'] = date('Y-m-d H:i:s');
                $product['updated_at'] = date('Y-m-d H:i:s');
                $this->db->table('products')->insert($product);

                $productId = $this->db->insertID();
                $this->db->table('inventory_transactions')->insert([
                    'product_id'       => $productId,
                    'transaction_type' => 'Stock In',
                    'quantity'         => $product['stock'],
                    'notes'            => 'Initial stock',
                    'transaction_date' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
