<?php
$pageTitle = "Inloggen";
$hideNav = true;
$bodyClass = "login-page";
$extraCss = "/css/login.css";
include 'includes/header.php';

// Als je al bent ingelogd, sturen we je direct naar het goede dashboard!
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: /admin/index.php");
        exit();
    } elseif ($_SESSION['rol'] === 'collega') {
        header("Location: /collegas/index.php");
        exit();
    }
}

$fout = '';

// Check of het formulier gesubmit is
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $wachtwoord = $_POST['wachtwoord'];

    if (empty($username) || empty($wachtwoord)) {
        $fout = "Vul a.u.b. je gebruikersnaam en wachtwoord in.";
    } else {
        // Zoek de gebruiker op basis van de Gebruikersnaam en rol
    $stmt = $pdo->prepare("SELECT g.*, r.Naam as rol FROM gebruiker g JOIN rol r ON g.Id = r.GebruikerId WHERE g.Gebruikersnaam = ?");
    $stmt->execute([$username]);
    $gebruiker = $stmt->fetch();

    // Vergelijk de hash OF plain text (voor testen)
    $loginSucces = false;
    if ($gebruiker) {
        if (password_verify($wachtwoord, $gebruiker['Wachtwoord'])) {
            $loginSucces = true;
        } elseif ($wachtwoord === $gebruiker['Wachtwoord']) {
            $loginSucces = true;
        }
    }

    if ($loginSucces) {
        $_SESSION['gebruiker_id'] = $gebruiker['Id'];
        $_SESSION['naam']         = $gebruiker['Voornaam'] . ' ' . $gebruiker['Achternaam'];
        // Mapping database rol naar PHP sessie rol
        $databaseRol = strtolower($gebruiker['rol']);
        if (strpos($databaseRol, 'admin') !== false) {
            $_SESSION['rol'] = 'admin';
            header("Location: /admin/index.php");
        } elseif (strpos($databaseRol, 'medewerker') !== false) {
            $_SESSION['rol'] = 'collega';
            header("Location: /collegas/index.php");
        } else {
            $_SESSION['rol'] = 'lid';
            header("Location: /login.php"); 
        }
        exit();
    } else {
        $fout = "Ongeldige gebruikersnaam of wachtwoord.";
    }
    }
}
?>
    <!-- Wrapper om de login netjes in het midden te zetten en de footer onderaan te houden -->
    <div class="login-wrapper">
        <!-- De login-box met de style van de nieuwe modal -->
        <div class="login-modal">
        <img src="/img/logo.png" alt="Fit for Fun Logo" style="height: 100px; margin-bottom: 20px;">
        
        <!-- Fout weergave (indien wachtwoord verkeerd is) -->
        <?php if ($fout): ?>
            <div class="fout"><?= htmlspecialchars($fout) ?></div>
        <?php endif; ?>
        
        <!-- Login Formulier -->
        <form method="POST" novalidate>
            <!-- Gebruikersnaam Invoer -->
            <input type="text" name="username" placeholder="Gebruikersnaam">
            
            <!-- Wachtwoord Invoer -->
            <input type="password" name="wachtwoord" placeholder="Wachtwoord">
            
            <!-- Inloggen knop -->
            <button type="submit" class="modal-btn">Inloggen</button>
            
            <!-- Registreren knop -->
            <a href="/registreren.php" class="modal-btn" style="background-color: #f1f1f1; color: #333; margin-top: 10px; display: inline-block; text-align: center; text-decoration: none; box-sizing: border-box;">Registreren</a>
            <br><br>
            <a href="/index.php" style="text-decoration:none; color:#1F3864; font-size:14px; font-weight:bold;">← Terug naar home</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
