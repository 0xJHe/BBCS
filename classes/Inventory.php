<?php

class Inventory {
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Get blood inventory rows for hospital
    public function getInventoryRows(int $hospitalId): array
    {
        // Blood types
        $standardTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        // Fetch blood inventory data
        $sql = "
            SELECT 
                blood_type,
                quantity as units,
                100 as capacity, 
                status
            FROM BloodInventory
            WHERE hospital_id = :hid
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hid', $hospitalId, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map the results by blood type to easy lookup
        $dbData = [];
        foreach ($results as $row) {
            $dbData[$row['blood_type']] = $row;
        }

        // Combine blood type and dbData
        // Return as array and extract easily
        $finalRows = [];
        foreach ($standardTypes as $type) {
            if (isset($dbData[$type])) {
                // Use DB data
                $row = $dbData[$type];
                $row['percent_full'] = round(($row['units'] / 100) * 100);
                $finalRows[] = $row;
            } else {
                // Default data 0 if not found in database
                $finalRows[] = [
                    'blood_type' => $type,
                    'units' => 0,
                    'capacity' => 100,
                    'status' => 'Critical', // 0 is critical
                    'percent_full' => 0
                ];
            }
        }

        return $finalRows;
    }

    // Calculate the summary
    public function getSummary(array $rows): array
    {
        $totalTypes = count($rows);
        $critical = 0;
        $low = 0;

        foreach ($rows as $row) {
            $status = strtolower($row['status']);
            if ($status === 'critical') {
                $critical++;
            }
            if ($status === 'low') {
                $low++;
            }
        }

        return [
            'totalTypes' => $totalTypes,
            'critical'   => $critical,
            'low'        => $low,
        ];
    }
}