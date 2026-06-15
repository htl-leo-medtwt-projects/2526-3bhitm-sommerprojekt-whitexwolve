<?php

// Alle kommenden Shows mit Event-Infos laden (JOIN auf events)
// nur Shows ab heute — vergangene werden ausgeblendet
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
    $shows  = [];

    while ($row = $result->fetch_assoc()) {
        $shows[] = $row;
    }

    return $shows;
}

// Eine einzelne Show anhand ID laden
// gibt auch alle Event-Felder zurück (title, description, category) damit kein zweiter Query nötig ist
function getShowById($conn, $show_id) {
    $stmt = $conn->prepare(
        "SELECT s.*, e.title, e.description, e.category
         FROM shows s
         JOIN events e ON s.event_id = e.id
         WHERE s.id = ?"
    );
    $stmt->bind_param('i', $show_id);
    $stmt->execute();
    $show = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $show;
}

// Saal anhand Name laden (z.B. "Kinosaal")
// gibt Zeilen- und Spaltenanzahl zurück, wird in seat.php für das Layout gebraucht
function getSaalByName($conn, $name) {
    $stmt = $conn->prepare("SELECT * FROM saele WHERE name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $saal = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $saal;
}

// Alle Events für das Filtermenü auf der Startseite
function getAllEvents($conn) {
    $result = $conn->query("SELECT * FROM events ORDER BY title ASC");
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    return $events;
}