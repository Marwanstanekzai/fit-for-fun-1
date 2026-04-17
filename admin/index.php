<?php
$pageTitle = "Admin Dashboard";
include '../includes/header.php';
checkAdmin();

// Unhappy dashboard
$simulate_db_error = false;

try {
    if ($simulate_db_error) throw new PDOException("Kan dashboard data niet inladen!");

    // Statistieken voor de kaarten
    $aantalLeden       = $pdo->query("SELECT COUNT(*) FROM lid")->fetchColumn();
    $aantalLessen      = $pdo->query("SELECT COUNT(*) FROM les")->fetchColumn();
    $aantalReserv      = $pdo->query("SELECT COUNT(*) FROM reservering")->fetchColumn();
    $aantalMedewerkers = $pdo->query("SELECT COUNT(*) FROM gebruiker")->fetchColumn();

    // Overzicht 1: Reserveringen per maand
    $reservPerPeriode = $pdo->query("
        SELECT DATE_FORMAT(Datum, '%Y-%m') AS periode, COUNT(*) AS aantal
        FROM reservering
        GROUP BY periode
        ORDER BY periode DESC
        LIMIT 6
    ")->fetchAll();

    // Overzicht 2: Geplande lessen
    $geplandeLessen = $pdo->query("
        SELECT Naam, Trainer, Datum, Tijd, MaxAantalPersonen
        FROM les
        ORDER BY Datum ASC
        LIMIT 5
    ")->fetchAll();

    // Overzicht 3: Leden aangemeld per maand
    $ledenPerPeriode = $pdo->query("
        SELECT DATE_FORMAT(Datumaangemaakt, '%Y-%m') AS periode, COUNT(*) AS aantal
        FROM lid
        GROUP BY periode
        ORDER BY periode DESC
        LIMIT 6
    ")->fetchAll();
    $fout = '';
} catch (PDOException $e) {
    $fout = "Systeemfout: " . $e->getMessage();
    $aantalLeden = $aantalLessen = $aantalReserv = $aantalMedewerkers = 0;
    $reservPerPeriode = [];
    $geplandeLessen = [];
    $ledenPerPeriode = [];
}
?>

<div class="container">
    <h1>Dashboard</h1>
    <?php if ($fout): ?><div class="fout"><?= htmlspecialchars($fout) ?></div><?php endif; ?>

    <!-- Statistieken kaarten -->
    <div class="kaarten">
        <a href="/admin/leden.php" class="kaart" style="text-decoration:none;"><h2><?= $aantalLeden ?></h2><p>Leden</p></a>
        <a href="/admin/lessen.php" class="kaart" style="text-decoration:none;"><h2><?= $aantalLessen ?></h2><p>Lessen</p></a>
        <a href="/admin/reserveringen.php" class="kaart" style="text-decoration:none;"><h2><?= $aantalReserv ?></h2><p>Reserveringen</p></a>
        <a href="/admin/medewerkers.php" class="kaart" style="text-decoration:none;"><h2><?= $aantalMedewerkers ?></h2><p>Medewerkers</p></a>
    </div>

    <!-- Overzichten sectie -->
    <div class="overzichten">

        <!-- Overzicht 1: Reserveringen per periode -->
        <div class="overzicht-kaart">
            <h2>Reserveringen per periode</h2>
            <table>
                <thead>
                    <tr><th>Periode</th><th>Aantal</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reservPerPeriode as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['periode']) ?></td>
                        <td><?= $r['aantal'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reservPerPeriode)): ?>
                    <tr><td colspan="2" style="text-align:center;color:#999;">Geen data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Overzicht 2: Geplande lessen -->
        <div class="overzicht-kaart">
            <h2>Geplande lessen</h2>
            <table>
                <thead>
                    <tr><th>Les</th><th>Trainer</th><th>Datum</th><th>Tijd</th><th>Max</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($geplandeLessen as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['Naam']) ?></td>
                        <td><?= htmlspecialchars($l['Trainer']) ?></td>
                        <td><?= date('d-m-Y', strtotime($l['Datum'])) ?></td>
                        <td><?= $l['Tijd'] ?></td>
                        <td><?= $l['MaxAantalPersonen'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($geplandeLessen)): ?>
                    <tr><td colspan="5" style="text-align:center;color:#999;">Geen lessen</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Overzicht 3: Leden per periode -->
        <div class="overzicht-kaart">
            <h2>Leden per periode</h2>
            <table>
                <thead>
                    <tr><th>Periode</th><th>Nieuwe leden</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($ledenPerPeriode as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['periode']) ?></td>
                        <td><?= $l['aantal'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($ledenPerPeriode)): ?>
                    <tr><td colspan="2" style="text-align:center;color:#999;">Geen data</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>

// Debug: Statistieken weergave gecontroleerd
