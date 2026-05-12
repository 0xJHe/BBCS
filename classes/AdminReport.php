<?php
require_once 'Database.php';

class AdminReport {
    private $conn;

    public function __construct() {
        global $con;
        $this->conn = $con;
    }

    // --- GENERAL COUNTS ---

    public function getUsersByRole($role) {
        $query = "SELECT COUNT(*) as total FROM User WHERE role = :role";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getEventsCount($year) {
        $query = "SELECT COUNT(*) as total FROM Event WHERE YEAR(event_date) = :year";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getAppointmentStats($year, $status = null) {
        if ($status) {
            $query = "SELECT COUNT(*) as total 
                      FROM Appointment a 
                      JOIN Event e ON a.event_id = e.event_id 
                      WHERE a.status = :status AND YEAR(e.event_date) = :year";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
        } else {
            $query = "SELECT COUNT(*) as total 
                      FROM Appointment a 
                      JOIN Event e ON a.event_id = e.event_id 
                      WHERE YEAR(e.event_date) = :year";
            $stmt = $this->conn->prepare($query);
        }
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getMonthlyAppointments($year) {
        $query = "SELECT MONTH(e.event_date) as month, COUNT(*) as total 
                  FROM Appointment a
                  JOIN Event e ON a.event_id = e.event_id
                  WHERE YEAR(e.event_date) = :year 
                  GROUP BY MONTH(e.event_date) 
                  ORDER BY month ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $data = array_fill(1, 12, 0);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[$row['month']] = $row['total'];
        }
        return $data;
    }

    // --- OPERATIONAL INSIGHTS ---

    // Top Event Locations (Using 'venue' column)
    public function getTopLocations($year, $limit = 5) {
        try {
            // Alias venue as 'location' for consistency in view
            $query = "SELECT venue as location, COUNT(*) as count
                      FROM Event
                      WHERE YEAR(event_date) = :year
                      GROUP BY venue
                      ORDER BY count DESC
                      LIMIT :limit";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>