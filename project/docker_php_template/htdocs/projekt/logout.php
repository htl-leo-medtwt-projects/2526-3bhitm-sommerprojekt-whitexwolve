<?php
// Session starten um sie dann zerstören zu können
session_start();

// Alle Session-Variablen löschen
session_unset();

// Session komplett zerstören
session_destroy();

// Zurück zur Startseite
header('Location: index.php');
exit;
