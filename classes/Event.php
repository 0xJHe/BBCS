<?php

class Event {
    private $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Fetch a event by id
    public function getEventById(int $eventId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM Event WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        return $event ?: null;
    }

    // Get upcoming events with slots and (Optional) filtered by venue.
    public function getUpcomingEventsWithSlots(string $venueSearch = ''): array {
        $sql = "SELECT e.*, 
                       (e.capacity - (SELECT COUNT(*) FROM Appointment a WHERE a.event_id = e.event_id)) as slots 
                FROM Event e 
                WHERE e.status = 'Upcoming'";

        if ($venueSearch !== '') {
            $sql .= " AND e.venue LIKE :venue";
        }

        $sql .= " ORDER BY e.event_date ASC";

        $stmt = $this->db->prepare($sql);

        // If venue search not null
        if ($venueSearch !== '') {
            $stmt->bindValue(':venue', '%' . $venueSearch . '%');
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

