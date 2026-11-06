<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use PDO;
use RuntimeException;

class StockService
{
    /**
     * Record stock movement with transactional integrity
     */
    public static function registerMovement(
        int $materialId,
        string $movementType,
        float $quantity,
        ?int $workOrderId = null,
        ?int $userId = null,
        ?string $reason = null
    ): bool {
        if ($quantity <= 0) {
            throw new RuntimeException("A quantidade deve ser superior a zero.");
        }

        $db = Database::getConnection();
        Database::beginTransaction();

        try {
            // Lock and fetch current material
            $stmt = $db->prepare("SELECT * FROM materials WHERE id = :id");
            $stmt->execute([':id' => $materialId]);
            $material = $stmt->fetch();

            if (!$material) {
                throw new RuntimeException("Material não encontrado.");
            }

            $currentStock = (float)$material['quantity_available'];
            $unitCost = (float)$material['unit_cost'];
            $totalCost = $quantity * $unitCost;

            if ($movementType === 'out') {
                if ($quantity > $currentStock) {
                    throw new RuntimeException("Stock insuficiente para '{$material['name']}'. Disponível: {$currentStock} {$material['unit_of_measure']}, Solicitado: {$quantity} {$material['unit_of_measure']}.");
                }
                $newStock = $currentStock - $quantity;
            } elseif (in_array($movementType, ['in', 'return'], true)) {
                $newStock = $currentStock + $quantity;
            } elseif ($movementType === 'adjustment') {
                $newStock = $quantity; // quantity represents new actual stock
                $quantity = abs($newStock - $currentStock);
            } else {
                throw new RuntimeException("Tipo de movimento inválido: {$movementType}");
            }

            // Update material quantity
            $updateStmt = $db->prepare("UPDATE materials SET quantity_available = :qty, updated_at = datetime('now') WHERE id = :id");
            $updateStmt->execute([':qty' => $newStock, ':id' => $materialId]);

            // Insert stock movement record
            $moveStmt = $db->prepare("
                INSERT INTO stock_movements (material_id, movement_type, quantity, unit_price, total_price, work_order_id, user_id, reason, created_at)
                VALUES (:material_id, :type, :qty, :unit_price, :total_price, :wo_id, :user_id, :reason, datetime('now'))
            ");
            $moveStmt->execute([
                ':material_id' => $materialId,
                ':type' => $movementType,
                ':qty' => $quantity,
                ':unit_price' => $unitCost,
                ':total_price' => $totalCost,
                ':wo_id' => $workOrderId,
                ':user_id' => $userId,
                ':reason' => $reason
            ]);

            // Check if stock is now below minimum level and trigger notification if so
            if ($newStock <= (float)$material['min_stock_level']) {
                $notifStmt = $db->prepare("
                    INSERT INTO notifications (user_id, title, message, type, link)
                    SELECT id, 'Alerta de Stock Mínimo', :msg, 'warning', '/operations/stock'
                    FROM users WHERE status = 'active' AND id IN (SELECT user_id FROM user_roles WHERE role_id IN (1, 2, 4))
                ");
                $notifStmt->execute([
                    ':msg' => "O artigo '{$material['name']}' atingiu o nível crítico: {$newStock} {$material['unit_of_measure']} (Mínimo: {$material['min_stock_level']})."
                ]);
            }

            Database::commit();
            return true;
        } catch (\Throwable $e) {
            Database::rollBack();
            Logger::error("Stock movement failed: " . $e->getMessage());
            throw $e;
        }
    }
}
