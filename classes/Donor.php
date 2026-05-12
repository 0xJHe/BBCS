<?php

include "User.php";

class Donor extends User {
    protected $db;
    private $bloodType;
    private $phone;

    public function __construct(PDO $db, $userID = null) {
        // Call parent(User) constructor
        parent::__construct($db, $userID);
        $this->db = $db;

        // Load data if got userID
        if ($userID) {
            $this->loadDonorData($userID);
        }
    }
    private function loadDonorData($id) {
        $sql = "SELECT blood_type, contact_number FROM Donor WHERE donor_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        // If data not null
        if ($data) {
            $this->bloodType = $data['blood_type'];
            $this->phone = $data['contact_number'];
        }
    }

    // Getters
    public function getBloodType() { return $this->bloodType; }
    public function getPhone() { return $this->phone; }

    // Get full donor details including User table data
    public function getDonorDetails($user_id) {
        $sql = "SELECT 
                    u.user_id, 
                    u.name as full_name, 
                    u.email, 
                    u.status, 
                    d.blood_type, 
                    d.contact_number as phone, 
                    d.eligibility_status,
                    d.donor_id
                FROM User u 
                JOIN Donor d ON u.user_id = d.donor_id 
                WHERE u.user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get most recent completed donation
    public function getLastDonationDate($donor_id) {
        $sql = "SELECT appointment_date 
                FROM Appointment 
                WHERE donor_id = :donor_id 
                  AND status = 'Completed' 
                ORDER BY appointment_date DESC 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':donor_id' => $donor_id]);
        return $stmt->fetchColumn();
    }

    // Sends notification to the donor
    public function sendNotification($donor_id, $message) {
        try {
            $sql = "INSERT INTO Notification (message, channel, status, sent_date) 
                    VALUES (:message, 'System/SMS', 'Sent', CURDATE())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':message' => "To Donor #$donor_id: " . $message]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    
}
?>

<!-- 

// Updates donor profile
    public function updateDonorProfile($user_id, $data) {
        try {
            $this->db->beginTransaction();

            // 1. Update User Table
            $stmt1 = $this->db->prepare("UPDATE User SET name = :name WHERE user_id = :user_id");
            $stmt1->execute([
                ':name' => $data['name'], 
                ':user_id' => $user_id
            ]);

            // 2. Update Donor Table
            $stmt2 = $this->db->prepare("UPDATE Donor SET contact_number = :contact, blood_type = :blood_type WHERE donor_id = :user_id");
            $stmt2->execute([
                ':contact' => $data['contact_number'], 
                ':blood_type' => $data['blood_type'],
                ':user_id' => $user_id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    // Updates only the blood type for a donor
    public function updateBloodType($user_id, $bloodType) {
        $sql = "UPDATE Donor SET blood_type = :blood_type WHERE donor_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':blood_type' => $bloodType,
            ':user_id' => $user_id
        ]);
    }



    // Checks if a donor is currently eligible to donate.
    public function isEligible($user_id) {
        $stmt = $this->db->prepare("SELECT eligibility_status FROM Donor WHERE donor_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $status = $stmt->fetchColumn();
        
        return ($status && strtolower($status) === 'eligible');
    }

-->