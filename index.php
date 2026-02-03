<?php
require_once __DIR__ . '/assets/includes/conn.php';

$dbConnection = new DbhConnection();
$pdo = $dbConnection->connect();

include __DIR__ . '/assets/includes/header.php';
?>

<section class="cards">
  <?php for ($i = 0; $i < 4; $i++): ?>
    <article class="card">
      <header class="card__name">NAME</header>

      <div class="card__image" aria-hidden="true">
        <!-- eenvoudige inline SVG placeholder -->
        <svg width="150" height="120" viewBox="0 0 150 120" xmlns="http://www.w3.org/2000/svg">
          <rect width="150" height="120" fill="#ffffff" rx="12"/>
          <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#333" font-size="28">img</text>
        </svg>
      </div>

      <div class="card__text">text</div>
    </article>
  <?php endfor; ?>
</section>    