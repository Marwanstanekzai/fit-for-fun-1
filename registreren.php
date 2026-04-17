<?php
$pageTitle = "Registreren";
$hideNav = true;
$bodyClass = "login-page";
$extraCss = "/css/login.css";
include 'includes/header.php';

$fout = '';
$succes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam       = trim($_POST['naam']);
    $email      = trim($_POST['email']);
    $wachtwoord = $_POST['wachtwoord'];
    
    if (empty($naam) || empty($email) || empty($wachtwoord)) {
        $fout = "Vul a.u.b. alle velden in.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fout = "Ongeldig e-mailadres.";
    } elseif (strlen($wachtwoord) < 6) {
        $fout = "Wachtwoord moet minimaal 6 tekens zijn.";
    } else {
        // Split naam in voornaam en achternaam voor de database 
        $parts = explode(' ', $naam, 2);
        $voornaam = $parts[0];
        $achternaam = isset($parts[1]) ? $parts[1] : '';

        // Check of dit e-mailadres (Gebruikersnaam) al bestaat
    $stmt = $pdo->prepare("SELECT Id FROM gebruiker WHERE Gebruikersnaam = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $fout = "Dit e-mailadres is al in gebruik.";
    } else {
        // We hashen het wachtwoord veilig
        $hashedWachtwoord = password_hash($wachtwoord, PASSWORD_DEFAULT);
        
        // Gebruiker aanmaken in de database
        $insert = $pdo->prepare("INSERT INTO gebruiker (Voornaam, Achternaam, Gebruikersnaam, Wachtwoord) VALUES (?, ?, ?, ?)");
        if ($insert->execute([$voornaam, $achternaam, $email, $hashedWachtwoord])) {
            $gebruikerId = $pdo->lastInsertId();
            
            // Standaardrol "gebruiker" toekennen in de rol tabel
            $rolInsert = $pdo->prepare("INSERT INTO rol (GebruikerId, Naam, IsActief, Opmerking) VALUES (?, 'gebruiker', 1, 'Automatisch geregistreerd')");
            $rolInsert->execute([$gebruikerId]);
            
            $succes = "Je account is succesvol aangemaakt! Je kunt nu inloggen.";
        } else {
            $fout = "Er ging iets mis bij het aanmaken van het account. Probeer het later opnieuw.";
        }
    }
}
}
?>
    <div class="login-wrapper">
        <div class="login-modal">
        <img src="/img/logo.png" alt="Fit for Fun Logo" style="height: 100px; margin-bottom: 20px;">
        
        <h2>Account Aanmaken</h2>
        
        <?php if ($fout): ?>
            <div class="fout" style="color: red; margin-bottom: 15px; font-weight: bold;"><?= htmlspecialchars($fout) ?></div>
        <?php endif; ?>
        <?php if ($succes): ?>
            <div class="succes" style="color: green; margin-bottom: 15px; font-weight: bold;"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>
        
        <!-- Registratie Formulier -->
        <form method="POST" novalidate>
            <!-- Naam Invoer -->
            <input type="text" name="naam" placeholder="Je volledige naam">
            
            <!-- E-mail / Gebruikersnaam Invoer -->
            <input type="text" name="email" placeholder="E-mailadres">
            
            <!-- Wachtwoord Invoer -->
            <input type="password" name="wachtwoord" placeholder="Wachtwoord (minimaal 6 tekens)">
            
            <!-- Account aanmaken knop -->
            <button type="submit" class="modal-btn">Account aanmaken</button>
            
            <!-- Verwijzing naar inloggen -->
            <a href="/login.php" class="modal-btn" style="background-color: #f1f1f1; color: #333; margin-top: 10px; display: inline-block; text-align: center; text-decoration: none; box-sizing: border-box;">Heb je al een account? Log in</a>
            <br><br>
            <a href="/index.php" style="text-decoration:none; color:#1F3864; font-size:14px; font-weight:bold;">← Terug naar home</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


