<?php
$seats = ['A1', 'A2', 'A3', 'A4', 'B1', 'B2', 'B3', 'B4', 'C1', 'C2', 'C3', 'C4'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitzplätze</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/seat.css">
</head>
<body>
    <main class="seat-page">
        <div class="seat-layout">
            <section class="seat-box">
                <h1>Sitzplätze auswählen</h1>
                <div class="screen">LEINWAND</div>

                <div class="seat-grid">
                    <?php foreach ($seats as $seat): ?>
                        <button class="seat"><?= $seat ?></button>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="legend-box">
                <h2>Legende</h2>
                <p><span class="legend-color legend-free"></span> Frei</p>
                <p><span class="legend-color legend-taken"></span> Belegt</p>
                <p><span class="legend-color legend-selected"></span> Ausgewählt</p>
            </aside>
        </div>
    </main>
</body>
</html>