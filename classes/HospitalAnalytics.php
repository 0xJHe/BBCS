<?php

class HospitalAnalytics {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Get available years for analytics
    public function getAvailableYears(): array {
        // Appointment table
        $stmt = $this->db->query(
            "SELECT DISTINCT YEAR(appointment_date) as y 
             FROM Appointment 
             WHERE appointment_date IS NOT NULL 
             ORDER BY y DESC"
        );
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($years)) {
            $years = [date('Y')];
        }
        return $years;
    }

    // Get Total Units Collected in yearly based
    public function getYearlyUnits(int $year): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM Appointment 
             WHERE status = 'Completed' 
               AND YEAR(appointment_date) = ?"
        );
        $stmt->execute([$year]);
        return (int)$stmt->fetchColumn();
    }

    // Get total donors
    public function getTotalDonors(): int {
        // Table: Donor
        $stmt = $this->db->query("SELECT COUNT(*) FROM Donor");
        return (int)$stmt->fetchColumn();
    }

    // Get Total Units Donated
    public function getTotalUnitsDonated(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM Appointment WHERE status = 'Completed'");
        return (int)$stmt->fetchColumn();
    }

    // Get current month units collected
    public function getCurrentMonthCollection(): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) 
             FROM Appointment 
             WHERE status = 'Completed' 
               AND MONTH(appointment_date) = MONTH(CURRENT_DATE())
               AND YEAR(appointment_date) = YEAR(CURRENT_DATE())"
        );
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    // Get how many(COUNT) blood type is critical
    public function getCriticalEventsCount(): int {
        // Table: BloodInventory, Enum: Critical
        $stmt = $this->db->query(
            "SELECT COUNT(*) 
             FROM BloodInventory 
             WHERE status = 'Critical'"
        );
        return (int)$stmt->fetchColumn();
    }

    // Get current month stats and return as an array
    public function getCurrentMonthStats(): array {
        return [
            'total_units_collected' => $this->getCurrentMonthCollection(),
            'critical_events'       => $this->getCriticalEventsCount()
        ];
    }

    // Get recent donations for limit 5 people
    public function getRecentDonations(int $limit = 5): array
    {
        // Appointment, Donor, User
        $sql = "
            SELECT 
                u.name as full_name,
                d.blood_type,
                d.donor_id,
                a.appointment_date as donation_date,
                1 as units_donated -- Assuming 1 appointment = 1 unit
            FROM Appointment a
            JOIN Donor d ON a.donor_id = d.donor_id
            JOIN User u ON d.donor_id = u.user_id
            WHERE a.status = 'Completed'
            ORDER BY a.appointment_date DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 
    public function getInventoryHistory(int $limit = 5): array
    {
        // BloodInventory Table
        $sql = "
            SELECT blood_type, quantity as units, status, last_updated as recorded_at
            FROM BloodInventory
            ORDER BY last_updated DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get most donated donor by blood type
    public function getTopDonors(): array
    {
        $sql = "
            SELECT 
                d.donor_id,
                d.blood_type, 
                u.name as full_name, 
                COUNT(a.appointment_id) as total_donations
            FROM Appointment a
            JOIN Donor d ON a.donor_id = d.donor_id
            JOIN User u ON d.donor_id = u.user_id
            WHERE a.status = 'Completed'
            GROUP BY d.donor_id, d.blood_type, u.name
            ORDER BY total_donations DESC
        ";

        $stmt = $this->db->query($sql);
        $allStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $topDonors = [];
        foreach ($allStats as $row) {
            $type = $row['blood_type'];
            // Ordered by DESC, the first one is the most donated
            if (!isset($topDonors[$type])) {
                $topDonors[$type] = $row;
            }
        }
        
        return $topDonors;
    }
}