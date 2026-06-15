<?php
// Alle kommenden Shows mit Event-Infos laden
function getAllShows($conn) {
    $sql = "SELECT
                s.id AS show_id,
                s.event_id,
                e.title,
                e.description,
                e.category,
                s.show_date,
                s.show_time,
                s.hall,
                s.ticket_price
            FROM shows s
            JOIN events e ON s.event_id = e.id
            WHERE s.show_date >= CURDATE()
            ORDER BY s.show_date ASC, s.show_time ASC";

    $result = $conn->query($sql);
    $shows = [];

    while ($row = $result->fetch_assoc()) {
        $shows[] = $row;
    }

    return $shows;
}

// Eine einzelne Show laden
function getShowById($conn, $show_id) {
    $stmt = $conn->prepare("SELECT s.*, e.title, e.description, e.category
                             FROM shows s
                             JOIN events e ON s.event_id = e.id
                             WHERE s.id = ?");
    $stmt->bind_param('i', $show_id);
    $stmt->execute();
    $show = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $show;
}

// Saal-Info laden
function getSaalByName($conn, $name) {
    $stmt = $conn->prepare("SELECT * FROM saele WHERE name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $saal = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $saal;
}

// Alle Events laden (für Filtermenü)
function getAllEvents($conn) {
    $result = $conn->query("SELECT * FROM events ORDER BY title ASC");
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    return $events;
}

