<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use PDO;

class ApiController
{
    public function getUnits(Request $request): void
    {
        $propertyId = (int)$request->query('property_id');
        if (!$propertyId) {
            Response::json(['units' => []]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, reference, name, status 
            FROM units 
            WHERE property_id = :id 
            ORDER BY reference ASC
        ");
        $stmt->execute([':id' => $propertyId]);
        Response::json(['units' => $stmt->fetchAll()]);
    }

    public function getSubcategories(Request $request): void
    {
        $categoryId = (int)$request->query('category_id');
        if (!$categoryId) {
            Response::json(['subcategories' => []]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT id, name 
            FROM incident_subcategories 
            WHERE category_id = :id 
            ORDER BY name ASC
        ");
        $stmt->execute([':id' => $categoryId]);
        Response::json(['subcategories' => $stmt->fetchAll()]);
    }

    public function getNotifications(Request $request): void
    {
        $userId = Auth::id();
        if (!$userId) {
            Response::json(['unread_count' => 0, 'notifications' => []]);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :uid 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([':uid' => $userId]);
        $notifications = $stmt->fetchAll();

        $unreadCount = (int)$db->query("
            SELECT count(*) FROM notifications 
            WHERE user_id = {$userId} AND read_at IS NULL
        ")->fetchColumn();

        Response::json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    public function markNotificationsRead(Request $request): void
    {
        $userId = Auth::id();
        if ($userId) {
            $db = Database::getConnection();
            $db->prepare("
                UPDATE notifications 
                SET read_at = datetime('now') 
                WHERE user_id = :uid AND read_at IS NULL
            ")->execute([':uid' => $userId]);
        }

        Response::json(['success' => true]);
    }
}
