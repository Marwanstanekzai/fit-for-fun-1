<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check of je bent ingelogd
if (!isset($_SESSION['gebruiker_id'])) {
    header("Location: /index.php");
    exit();
}

$user_id = $_SESSION['gebruiker_id'];
$succes = '';
$fout   = '';

// 1. Gegevens van de ingelogde gebruiker ophalen
$stmt = $pdo->prepare("SELECT g.*, r.Naam as rol FROM gebruiker g JOIN rol r ON g.Id = r.GebruikerId WHERE g.Id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Wachtwoord wijzigen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nieuw_wachtwoord = $_POST['wachtwoord'];
    $confirm_wachtwoord = $_POST['wachtwoord_confirm'];

    if (empty($nieuw_wachtwoord) || empty($confirm_wachtwoord)) {
        $fout = "Vul a.u.b. alle velden in.";
    } elseif ($nieuw_wachtwoord === $confirm_wachtwoord) {
        $hash = password_hash($nieuw_wachtwoord, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE gebruiker SET Wachtwoord = ? WHERE Id = ?");
        $stmt->execute([$hash, $user_id]);
        $succes = "Je wachtwoord is succesvol gewijzigd!";
    } else {
        $fout = "De wachtwoorden komen niet overeen.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Mijn Account – Fit for Fun</title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; font-family:Arial, Helvetica, sans-serif; }
        .navbar{ background:#dcdcdc; height:110px; display:flex; justify-content:space-between; align-items:center; padding:0 80px; }
        .logo{ height:90px; }
        .container { padding: 30px 80px; background: rgba(255, 255, 255, 0.95); min-height: 100vh; }
        h1 { color: #1F3864; margin-bottom: 20px;}
        .account-form { width: 350px; background: #f9f9f9; padding: 25px; border-radius: 10px; border: 2px solid #000; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .account-form input { width: 100%; height: 45px; margin-bottom: 15px; padding: 0 15px; border-radius: 30px; border: 2px solid #000; outline: none; }
        .button {
            width:100%; height:50px; background-color:#ff8c00; border-radius:40px; border:3px solid #000;
            box-shadow:0 5px 0 #1e88c9; font-weight:800; font-size:16px; color:#000; cursor:pointer;
        }
        .succes { background: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .fout { background: #ffe0e0; color: #c0392b; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .terug { display: block; margin-top: 20px; text-decoration: none; font-weight: bold; color: #1F3864; }

        /* RESPONSIVENESS */
        @media (max-width: 768px) {
            .navbar { height: auto; padding: 20px; flex-direction: column; gap: 20px; text-align: center; }
            .navbar .logo { height: 60px; }
            .container { padding: 20px; }
            .account-form { width: 100%; }
        }
    </style>
</head>
<body>
<nav class="navbar"><img src="/img/logo.png" alt="Logo" class="logo"></nav>
<div class="container">
    <h1>👤 Mijn Account</h1>
    <div class="account-form">
        <p><strong>Naam:</strong> <?= htmlspecialchars($user['Voornaam'] . ' ' . $user['Achternaam']) ?></p>
        <p><strong>Gebruikersnaam:</strong> <?= htmlspecialchars($user['Gebruikersnaam']) ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($user['rol']) ?></p>
        <hr style="margin: 20px 0;">
        
        <h3>Wachtwoord wijzigen</h3>
        <br>
        <?php if ($succes): ?><div class="succes"><?= htmlspecialchars($succes) ?></div><?php endif; ?>
        <?php if ($fout):   ?><div class="fout"><?= htmlspecialchars($fout) ?></div><?php endif; ?>

        <form method="POST" novalidate>
            <input type="password" name="wachtwoord" placeholder="Nieuw wachtwoord" required>
            <input type="password" name="wachtwoord_confirm" placeholder="Bevestig nieuw wachtwoord" required>
            <button type="submit" class="button">Wachtwoord Opslaan</button>
        </form>
    </div>
    <a href="index.php" class="terug">← Terug naar dashboard</a>
</div>
</body>
</html>
