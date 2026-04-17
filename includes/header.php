<?php
// Centraal header bestand voor Fit for Fun
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Standaard titel als er geen is gezet
if (!isset($pageTitle)) {
    $pageTitle = "Fit for Fun";
}

// Check of we in de admin folder zitten voor de juiste paden naar CSS
$cssPath = "/css/style.css";
$loginCssPath = "/css/login.css";
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – Fit for Fun</title>
    <link rel="stylesheet" href="<?= $cssPath ?>">
    <?php if (isset($extraCss)): ?>
        <link rel="stylesheet" href="<?= $extraCss ?>">
    <?php endif; ?>
</head>
<body class="<?= isset($bodyClass) ? $bodyClass : '' ?>">

<?php if (!isset($hideNav) || !$hideNav): ?>
<nav class="navbar">
    <div class="nav-left">
        <a href="/"><img src="/img/logo.png" alt="Fit for Fun Logo" class="logo"></a>
    </div>

    <!-- HAMBURGER MENU BUTTON FOR MOBILE -->
    <button class="hamburger-btn" onclick="toggleMenu()">☰</button>

    <div class="nav-links" id="navLinks">
        <a href="/">Home</a>
        <a href="/lessen.php">Lessen</a>

        <?php if (isset($_SESSION['rol'])): ?>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <a href="/admin/index.php">Dashboard</a>
                <div class="dropdown">
                    <a href="#" class="dropdown-toggle">Beheren ▾</a>
                    <ul class="dropdown-menu">
                        <li><a href="/admin/leden.php">Leden</a></li>
                        <li><a href="/admin/lessen.php">Lessen</a></li>
                        <li><a href="/admin/reserveringen.php">Reserveringen</a></li>
                        <li><a href="/admin/medewerkers.php">Medewerkers</a></li>
                    </ul>
                </div>
            <?php elseif ($_SESSION['rol'] === 'collega'): ?>
                <a href="/collegas/index.php">Dashboard</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['naam'])): ?>
            <div class="user-nav">
                <span>Welkom, <?= htmlspecialchars(explode(' ', $_SESSION['naam'])[0]) ?></span>
                <a href="/logout.php" class="login-btn">Uitloggen</a>
            </div>
        <?php else: ?>
            <a href="/login.php" class="login-btn">Inloggen</a>
        <?php endif; ?>
    </div>
</nav>

<script>
    function toggleMenu() {
        const navLinks = document.getElementById('navLinks');
        navLinks.classList.toggle('active');
        document.body.classList.toggle('no-scroll');
    }

    // Dropdown click toggle (voor mobiel)
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                this.closest('.dropdown').classList.toggle('open');
            });
        }
        // Sluit dropdown als je erbuiten klikt
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown').forEach(d => d.classList.remove('open'));
            }
        });
    });
</script>

<?php endif; ?>