<?php

include_once "User.php";

class Admin extends User {
    private $adminID;

    // Constructor
    public function __construct($db, $userID = null) {
        // Call parent(User) constructor
        parent::__construct($db, $userID);
        $this->adminID = $userID;
    }



    // Reset a user's password.
    public function resetUserPassword(int $userId, string $newPassword): bool {
        $sql = "UPDATE User SET password = :pass WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':pass', $newPassword);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Toggle user status between Active and Inactive
    public function toggleUserStatus(int $userId): ?string {
        // Fetch user status from user id
        $stmt = $this->db->prepare("SELECT status FROM User WHERE user_id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $currentStatus = $stmt->fetchColumn();
        // If no status return null
        if ($currentStatus === false) {
            return null;
        }

        // If Active then Inactive and vice versa
        $newStatus = ($currentStatus === 'Active') ? 'Inactive' : 'Active';

        // Update the new status to database
        $update = $this->db->prepare("UPDATE User SET status = :status WHERE user_id = :id");
        $update->bindParam(':status', $newStatus);
        $update->bindParam(':id', $userId, PDO::PARAM_INT);
        $update->execute();

        return $newStatus;
    }

    // Delete user by ID
    public function deleteUserById(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM User WHERE user_id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Get all users list for admin to manage users
    public function getAllUsers(): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT user_id, name, email, role, status 
                 FROM User 
                 ORDER BY user_id DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Approve registration request and verify the hospital
    public function approveRegistration(int $requestId, int $hospitalId): bool {
        try {
            $this->db->beginTransaction();
            // Table Registration
            $stmt1 = $this->db->prepare(
                "UPDATE RegistrationRequest 
                 SET status = 'Approved' 
                 WHERE request_id = :rid"
            );
            $stmt1->bindParam(':rid', $requestId, PDO::PARAM_INT);
            $stmt1->execute();
            // Table Hospital
            $stmt2 = $this->db->prepare(
                "UPDATE Hospital 
                 SET verification_status = 'Verified' 
                 WHERE hospital_id = :hid"
            );
            $stmt2->bindParam(':hid', $hospitalId, PDO::PARAM_INT);
            $stmt2->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback if error
            $this->db->rollBack();
            return false;
        }
    }

    // Reject a registration request with a reason
    public function rejectRegistration(int $requestId, int $hospitalId, string $reason): bool {
        try {
            $this->db->beginTransaction();
            // Table Registration
            $stmt1 = $this->db->prepare(
                "UPDATE RegistrationRequest 
                 SET status = 'Rejected', rejection_reason = :reason 
                 WHERE request_id = :rid"
            );
            $stmt1->bindParam(':rid', $requestId, PDO::PARAM_INT);
            $stmt1->bindParam(':reason', $reason);
            $stmt1->execute();
            // Table Hospital
            $stmt2 = $this->db->prepare(
                "UPDATE Hospital 
                 SET verification_status = 'Rejected' 
                 WHERE hospital_id = :hid"
            );
            $stmt2->bindParam(':hid', $hospitalId, PDO::PARAM_INT);
            $stmt2->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback if error
            $this->db->rollBack();
            return false;
        }
    }

    // Get all pending hospital registration requests
    public function getPendingRegistrations(): array {
        try {
            $sql = "SELECT 
                        r.request_id, 
                        r.submit_date, 
                        r.documents,
                        h.hospital_id, 
                        h.hospital_name, 
                        h.address,
                        h.verification_status 
                    FROM registrationrequest r
                    JOIN Hospital h ON r.hospital_id = h.hospital_id
                    WHERE r.status = 'Pending' 
                    ORDER BY r.submit_date ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}

?>
