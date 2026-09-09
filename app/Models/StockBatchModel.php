<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

/**
 * Owns FIFO (First-In-First-Out) stock movement: every batch this model
 * creates is dated, and depletion always draws from the oldest remaining
 * batch first — so older stock is the stock that gets used/sold first,
 * matching how a perishable dessert business actually wants inventory to
 * rotate. This model is the single place that moves stock: it keeps
 * products.stock in sync and writes the matching inventory_transactions
 * log entry, so callers (controllers) never touch either of those
 * directly — they just call receive()/deplete()/restore().
 */
class StockBatchModel extends Model
{
    protected $table            = 'stock_batches';
    protected $primaryKey       = 'batch_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'product_id', 'quantity', 'remaining_quantity', 'notes', 'received_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * New stock arriving — a brand new product's initial stock, a manual
     * restock, or a cancelled reservation's stock coming back. Always
     * dated "now", so it naturally lands at the back of the FIFO queue
     * behind whatever stock was already on hand.
     */
    public function receive(int $productId, int $quantity, string $transactionType, string $notes = ''): int
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Received quantity must be positive.');
        }

        $batchId = $this->insert([
            'product_id'         => $productId,
            'quantity'           => $quantity,
            'remaining_quantity' => $quantity,
            'notes'              => $notes,
            'received_at'        => date('Y-m-d H:i:s'),
        ]);

        (new ProductModel())->adjustStock($productId, $quantity);
        (new InventoryTransactionModel())->log($productId, $transactionType, $quantity, $notes);

        return $batchId;
    }

    /** Alias of receive() for the "stock coming back" case — same mechanics, clearer call site. */
    public function restore(int $productId, int $quantity, string $notes = ''): int
    {
        return $this->receive($productId, $quantity, 'Cancelled Return', $notes);
    }

    /**
     * Stock going out (a reservation gets confirmed) — draws from the
     * oldest batch(es) with remaining_quantity > 0 first, splitting
     * across batches if the oldest one alone doesn't cover it. Returns
     * the per-batch breakdown actually taken, e.g.
     * [['batch_id' => 3, 'taken' => 2], ['batch_id' => 5, 'taken' => 3]].
     */
    public function deplete(int $productId, int $quantity, string $transactionType = 'Reserved', string $notes = ''): array
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Depleted quantity must be positive.');
        }

        $batches = $this->where('product_id', $productId)
            ->where('remaining_quantity >', 0)
            ->orderBy('received_at', 'ASC')
            ->orderBy('batch_id', 'ASC')
            ->findAll();

        $remainingToTake = $quantity;
        $taken            = [];

        foreach ($batches as $batch) {
            if ($remainingToTake <= 0) {
                break;
            }

            $takeFromThisBatch = min($batch['remaining_quantity'], $remainingToTake);

            $this->update($batch['batch_id'], [
                'remaining_quantity' => $batch['remaining_quantity'] - $takeFromThisBatch,
            ]);

            $taken[]           = ['batch_id' => $batch['batch_id'], 'taken' => $takeFromThisBatch];
            $remainingToTake -= $takeFromThisBatch;
        }

        if ($remainingToTake > 0) {
            // Batches didn't have enough between them — shouldn't happen
            // since callers check products.stock first, but guards
            // against the two ever drifting out of sync silently.
            throw new RuntimeException("Not enough batched stock for product #{$productId}: short by {$remainingToTake}.");
        }

        (new ProductModel())->adjustStock($productId, -$quantity);
        (new InventoryTransactionModel())->log($productId, $transactionType, -$quantity, $notes);

        return $taken;
    }

    /**
     * The current FIFO queue for a product — oldest (soonest to be used)
     * first — for the Inventory > Batches breakdown view. Only batches
     * that still have stock left are shown.
     */
    public function breakdown(int $productId): array
    {
        return $this->where('product_id', $productId)
            ->where('remaining_quantity >', 0)
            ->orderBy('received_at', 'ASC')
            ->orderBy('batch_id', 'ASC')
            ->findAll();
    }

    /**
     * Full batch history across every product, newest first, for the
     * Inventory > All Batches log. Unlike breakdown(), this includes
     * fully-depleted batches too (remaining_quantity = 0) — it's a
     * history log, not "what's currently on hand". $from/$to filter on
     * received_at (inclusive), either can be left null.
     */
    public function allWithProduct(?string $from = null, ?string $to = null): array
    {
        $builder = $this->select('stock_batches.*, products.product_name')
            ->join('products', 'products.product_id = stock_batches.product_id')
            ->orderBy('stock_batches.received_at', 'DESC')
            ->orderBy('stock_batches.batch_id', 'DESC');

        if ($from) {
            $builder->where('stock_batches.received_at >=', $from . ' 00:00:00');
        }
        if ($to) {
            $builder->where('stock_batches.received_at <=', $to . ' 23:59:59');
        }

        return $builder->findAll();
    }
}
