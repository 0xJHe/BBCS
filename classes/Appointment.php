<?php

class Appointment
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    // Register donor for an event if not already registered
    // Returns true on success
    // False if already registered or DB error
    public function registerEvent(int $donorId, int $eventId): bool
    {
        // Check if already registered
        $checkSql = "SELECT 1 FROM Appointment WHERE donor_id = ? AND event_id = ?";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([$donorId, $eventId]);

        if ($checkStmt->rowCount() > 0) {
            return false;
        }

        // Fetch event date and time for appointment details
        $eventStmt = $this->db->prepare("SELECT event_date, event_time FROM Event WHERE event_id = ?");
        $eventStmt->execute([$eventId]);
        $event = $eventStmt->fetch(PDO::FETCH_ASSOC);
        // If no event
        if (!$event) {
            return false;
        }

        // Fetch event details to insert into Appointment table
        $apptDate = $event['event_date'];
        $timeSlot = $event['event_time'];
        $status = 'Pending';
        // Insert to Appointment
        $insertSql = "INSERT INTO Appointment (donor_id, event_id, appointment_date, time_slot, status)
                      VALUES (?, ?, ?, ?, ?)";

        $insertStmt = $this->db->prepare($insertSql);
        // Return details to show the event registered
        return $insertStmt->execute([$donorId, $eventId, $apptDate, $timeSlot, $status]);
    }


    // Get all appointments for a donor
    public function getAppointmentsDetails(int $donorId): array
    {
        $sql = "SELECT 
                    a.appointment_id, 
                    a.appointment_date, 
                    a.time_slot, 
                    a.status,
                    e.event_id,
                    e.event_name, 
                    e.venue,
                    d.blood_type,
                    u.name as donor_name
                FROM Appointment a
                JOIN Event e ON a.event_id = e.event_id
                JOIN Donor d ON a.donor_id = d.donor_id
                JOIN User u ON a.donor_id = u.user_id
                WHERE a.donor_id = ?
                ORDER BY a.appointment_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$donorId]);
        // Return details to show the event registered
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Delete an appointment
    public function deleteAppt(int $appointmentId, int $donorId): bool
    {
        $sql = "DELETE FROM Appointment 
                WHERE appointment_id = ? AND donor_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$appointmentId, $donorId]);

        return $stmt->rowCount() > 0;
    }
}

