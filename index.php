<?php
$pageTitle = "Home";
include 'includes/header.php';
?>

<section class="hero">
    <h1>Train Slim. Blijf Fit 💪</h1>
    <p>Reserveer eenvoudig jouw sportlessen online en werk aan de beste versie van jezelf bij de gezelligste sportschool van de regio.</p>
    <div class="hero-btns">
        <a href="/lessen.php" class="button" style="font-size:16px; padding:0 35px; height:50px;">Start nu</a>
    </div>
</section>

<div class="container" style="background:white;">
    <div class="home-grid">
        <div class="home-info">
            <h2>Behaal jouw doelen</h2>
            <p>Bij Fit for Fun geloven we in een persoonlijke aanpak. Of je nu wilt afvallen, spieren wilt opbouwen of gewoon lekker in je vel wilt zitten, ons team van ervaren trainers staat voor je klaar.</p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <span class="feature-icon">🏋️‍♂️</span>
                    <h3>Moderne Fitness</h3>
                    <p>De nieuwste apparatuur en een ruime opzet voor jouw optimale training.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📅</span>
                    <h3>Snel Reserveren</h3>
                    <p>Boek je favoriete groepslessen in een handomdraai via ons systeem.</p>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">👥</span>
                    <h3>Persoonlijke Hulp</h3>
                    <p>Altijd professionele begeleiding aanwezig in de zaal.</p>
                </div>
            </div>
        </div>

        <div class="home-sidebar">
            <div class="promo-card">
                <h3>Vandaag Actie!</h3>
                <p>Meld je vandaag nog aan en betaal de eerste maand slechts 1 euro!</p>
                <a href="/registreren.php" class="button" style="width:100%; box-shadow:0 3px 0 #000;">Schrijf je in</a>
            </div>
            
            <div style="margin-top:20px; border:2px solid #000; padding:20px;">
                <h3 style="color:#1F3864; margin-bottom:10px;">Openingstijden</h3>
                <ul style="list-style:none; padding:0; font-size:14px; line-height:1.8;">
                    <li style="display:flex; justify-content:space-between;"><span>Ma - Vr:</span> <strong>08:00 - 22:00</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Zaterdag:</span> <strong>09:00 - 18:00</strong></li>
                    <li style="display:flex; justify-content:space-between;"><span>Zondag:</span> <strong>09:00 - 16:00</strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


