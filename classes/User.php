<?php

class User {
    protected $db;
    protected $userID;
    protected $name;
    protected $email;
    protected $role;

    public function __construct($db, $userID = null) {
        $this->db = $db;
        if ($userID) {
            $this->userID = $userID;
            $this->loadData();
        }
    }

    // Load user data using PDO
    protected function loadData() {
        $sql = "SELECT * FROM User WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->userID]);
        $data = $stmt->fetch(); 

        if ($data) {
            $this->name = $data['name'];
            $this->email = $data['email'];
            $this->role = $data['role'];
        }
    }

    // Login logic
    public function login($email, $password) {
        $sql = "SELECT user_id, password, role FROM User WHERE email = ? and password = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $this->userID = $user['user_id'];
            $this->role = $user['role'];
            return true;
        }
        return false;
    }

    // Update profile
    public function update($data) {
        $sql = "UPDATE User SET name=?, address=?, phone=?, blood_type=? WHERE user_id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['name'], 
            $data['email'],
            $this->userID
        ]);
    }

    // Register new user
    public function register($name, $email, $password, $role, $extraData = []) {
        try {
            // Start transaction to ensure data integrity across tables
            $this->db->beginTransaction();

            // Check if email already exists
            $checkSql = "SELECT user_id FROM User WHERE email = ?";
            $stmt = $this->db->prepare($checkSql);
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return "Email already registered.";
            }

            // Insert into User table
            $sql = "INSERT INTO User (name, email, password, role, status) VALUES (?, ?, ?, ?, 'Active')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$name, $email, $password, $role]);
            
            // Get the newly created User ID
            $newUserId = $this->db->lastInsertId();

            // Insert into specific role table based on selection
            if ($role == 'Donor') {
                $sql = "INSERT INTO Donor (donor_id, blood_type, contact_number, eligibility_status) 
                        VALUES (?, ?, ?, 'Eligible')";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $newUserId, 
                    $extraData['blood_type'], 
                    $extraData['contact_number']
                ]);

            } elseif ($role == 'Organizer') {
                // Assuming Organization Name is the same as the User Name input
                $sql = "INSERT INTO EventOrganizer (organizer_id, organization_name, verification_status) 
                        VALUES (?, ?, 'Pending')";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$newUserId, $name]);

            } elseif ($role == 'Hospital') {
                // Assuming Hospital Name is the same as the User Name input
                $sql = "INSERT INTO Hospital (hospital_id, hospital_name, address, verification_status) 
                        VALUES (?, ?, ?, 'Pending')";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $newUserId, 
                    $name, 
                    $extraData['address']
                ]);
            }

            // Commit changes
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return "Error: " . $e->getMessage();
        }
    }

    // Reset Password Method
    public function resetPassword($email, $newPassword) {
        // Check if email exists first
        $checkSql = "SELECT user_id FROM User WHERE email = ?";
        $stmt = $this->db->prepare($checkSql);
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Update the password
            $updateSql = "UPDATE User SET password = ? WHERE email = ?";
            $updateStmt = $this->db->prepare($updateSql);
            
            if ($updateStmt->execute([$newPassword, $email])) {
                return true;
            } else {
                return "Failed to update password.";
            }
        } else {
            return "Email not found in our system.";
        }
    }

    // Getters
    public function getUserID() { return $this->userID; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }   
}