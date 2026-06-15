<?php 
  if (!function_exists('esc')) {
      function esc(string $v): string {
          return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
      }
  } 
?>
<header class="seitenkopf">
  <div class="seitenbreite seitenkopf__leiste">
    <a href="index.php" class="markenname">VibeSeat</a>

    <nav class="hauptnavigation" aria-label="Hauptnavigation">
      <a class="hauptnavigation__link" href="index.php">Events</a>

      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Eingeloggt: Profillink mit Avatar und Logout -->
        <a class="hauptnavigation__link" href="profil.php">
          <?php if (!empty($_SESSION['user_profilbild'])): ?>
            <img class="nav-avatar" src="<?= htmlspecialchars($_SESSION['user_profilbild'], ENT_QUOTES, 'UTF-8') ?>" alt="Profilbild">
          <?php endif; ?>
          <?= htmlspecialchars($_SESSION['user_username'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <a class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" href="logout.php">Logout</a>
      <?php else: ?>
        <!-- Nicht eingeloggt: Registrieren und Login -->
        <a class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" href="register.php">Registrieren</a>
        <a class="schaltflaeche schaltflaeche--primaer schaltflaeche--klein" href="login.php">Login</a>
      <?php endif; ?>

    </nav>
  </div>
</header>
