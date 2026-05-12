<?php

class Organizer {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Get organizer (User) name by id
    public function getOrganizerName(int $userId): ?string
    {
        $stmt = $this->db->prepare("SELECT name FROM User WHERE user_id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ? $user['name'] : null;
    }

    // Create new event
    public function createEvent(array $data, int $organizerId): bool
    {
        $sql = "INSERT INTO Event (event_name, event_date, event_time, venue, capacity, status, organizer_id, `desc`) 
                VALUES (:name, :date, :time, :venue, :cap, 'Upcoming', :oid, :desc)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $data['event_name']);
        $stmt->bindParam(':date', $data['event_date']);
        $stmt->bindParam(':time', $data['event_time']);
        $stmt->bindParam(':venue', $data['location']);
        $stmt->bindParam(':cap', $data['total_slot']);
        $stmt->bindParam(':oid', $organizerId, PDO::PARAM_INT);
        $stmt->bindParam(':desc', $data['description']);

        return $stmt->execute();
    }

    // Update event
    public function updateEvent(array $data, int $organizerId): bool
    {
        $sql = "UPDATE Event 
                SET event_name = :name,
                    event_date = :date,
                    event_time = :time,
                    venue = :venue,
                    capacity = :cap,
                    status = :status,
                    `desc` = :desc
                WHERE event_id = :id AND organizer_id = :oid";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $data['event_name']);
        $stmt->bindParam(':date', $data['event_date']);
        $stmt->bindParam(':time', $data['event_time']);
        $stmt->bindParam(':venue', $data['location']);
        $stmt->bindParam(':cap', $data['capacity']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':id', $data['event_id'], PDO::PARAM_INT);
        $stmt->bindParam(':oid', $organizerId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // NEW FUNCTION: Notify registered donors about event updates
    public function notifyEventUpdate(int $eventId, string $eventName): int 
    {
        // 1. Get all donors having an appointment for this event
        $sqlGetDonors = "SELECT DISTINCT donor_id FROM Appointment WHERE event_id = :eid";
        $stmtGet = $this->db->prepare($sqlGetDonors);
        $stmtGet->bindParam(':eid', $eventId, PDO::PARAM_INT);
        $stmtGet->execute();
        $donors = $stmtGet->fetchAll(PDO::FETCH_COLUMN);

        if (empty($donors)) {
            return 0;
        }

        // 2. Prepare Notification Insert
        // Schema: message, channel, status, sent_date, donor_id
        $message = "Update: Details for event '$eventName' have been updated by the organizer.";
        $channel = "System";
        $status = "Unread";
        $sentDate = date('Y-m-d'); 

        $sqlInsert = "INSERT INTO Notification (message, channel, status, sent_date, donor_id) 
                      VALUES (:msg, :chan, :stat, :date, :did)";
        $stmtInsert = $this->db->prepare($sqlInsert);

        $count = 0;
        foreach ($donors as $donorId) {
            $stmtInsert->bindParam(':msg', $message);
            $stmtInsert->bindParam(':chan', $channel);
            $stmtInsert->bindParam(':stat', $status);
            $stmtInsert->bindParam(':date', $sentDate);
            $stmtInsert->bindParam(':did', $donorId, PDO::PARAM_INT);
            
            if($stmtInsert->execute()){
                $count++;
            }
        }

        return $count;
    }

    // cancel events
    public function cancelEvents(array $eventIds, int $organizerId): int
    {
        $count = 0;
        $sql = "UPDATE Event SET status = 'Cancelled' WHERE event_id = :id AND organizer_id = :oid";
        $stmt = $this->db->prepare($sql);

        foreach ($eventIds as $eventId) {
            $eventId = (int)$eventId;
            if ($eventId <= 0) {
                continue;
            }
            $stmt->bindParam(':id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':oid', $organizerId, PDO::PARAM_INT);
            $stmt->execute();
            $count++;
        }

        return $count;
    }

    // Delete event
    public function deleteEvents(array $eventIds, int $organizerId): int
    {
        $count = 0;
        $sql = "DELETE FROM Event WHERE event_id = :id AND organizer_id = :oid";
        $stmt = $this->db->prepare($sql);

        foreach ($eventIds as $eventId) {
            $eventId = (int)$eventId;
            if ($eventId <= 0) {
                continue;
            }
            $stmt->bindParam(':id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':oid', $organizerId, PDO::PARAM_INT);
            $stmt->execute();
            $count++;
        }

        return $count;
    }

    // get events that belong to a (specific) organizer
    public function getEventsForOrganizer(int $organizerId): array
    {
        $sql = "SELECT e.event_id,
                       e.event_name,
                       e.event_date,
                       e.event_time,
                       e.venue,
                       e.capacity,
                       e.status,
                       e.desc,
                       u.name AS organizer_name 
                FROM Event e 
                JOIN User u ON e.organizer_id = u.user_id
                WHERE e.organizer_id = :oid 
                ORDER BY e.event_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':oid', $organizerId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

