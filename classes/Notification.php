<?php

// Hospital

class Notification {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Hospital
    // Checks inventory levels for the hospital and inserts alerts if critical
    public function generateInventoryAlerts(int $hospitalId) {
        // Get the blood type that in Low/Critical status
        $sql = "SELECT blood_type, status 
                FROM BloodInventory 
                WHERE hospital_id = ? 
                  AND status IN ('Low', 'Critical')";
                  
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hospitalId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $msg = "Alert: {$row['blood_type']} inventory is {$row['status']}";
            
            // Check if a same/similar alert was already sent today to prevent duplicates
            $check = $this->db->prepare("SELECT COUNT(*) FROM Notification WHERE message = ? AND sent_date = CURDATE()");
            $check->execute([$msg]);
            // Insert the msg if no duplicates
            if ($check->fetchColumn() == 0) {
                $ins = $this->db->prepare("INSERT INTO Notification (message, channel, status, sent_date, donor_id) VALUES (?, 'System', 'Unread', CURDATE(), ?)");
                $ins->execute([$msg, 1]);
            }
        }
    }

    
    // Get the most recent notifications.
    public function getRecentNotifications(int $limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM Notification ORDER BY sent_date DESC, notification_id DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}