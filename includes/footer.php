
<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="custom-modal">
    <div class="modal-content">
        <h3>Weet je het zeker?</h3>
        <p>Wil je dit item definitief verwijderen?</p>
        <div class="modal-footer">
            <button id="confirmCancel" class="modal-btn-no">Nee</button>
            <button id="confirmOk" class="modal-btn-yes">Ja</button>
        </div>
    </div>
</div>

<style>
    .custom-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0; top: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center; justify-content: center;
    }
    .modal-content {
        background: #fff; padding: 20px; border: 2px solid #000;
        width: 300px; text-align: center;
    }
    .modal-content h3 { margin-bottom: 10px; font-size: 18px; }
    .modal-content p { margin-bottom: 20px; font-size: 14px; }
    .modal-footer { display: flex; justify-content: center; gap: 10px; }
    .modal-btn-no, .modal-btn-yes {
        padding: 8px 20px; cursor: pointer; border: 1px solid #000; font-weight: bold;
    }
    .modal-btn-no { background: #eee; }
    .modal-btn-yes { background: #ff4444; color: #fff; }
</style>

<script>
    let deleteUrl = '';
    function confirmDelete(url) {
        deleteUrl = url;
        document.getElementById('confirmModal').style.display = 'flex';
        return false;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('confirmModal');
        document.getElementById('confirmCancel').onclick = () => modal.style.display = 'none';
        document.getElementById('confirmOk').onclick = () => window.location.href = deleteUrl;

        // Berichten na 3 sec weg
        setTimeout(function() {
            const alerts = document.querySelectorAll('.succes, .fout');
            alerts.forEach(a => a.style.display = 'none');
        }, 3000);
    });
</script>

<footer style="text-align: center; padding: 20px; font-size: 14px; color: #555; background: #dcdcdc; border-top: 2px solid #000;">
    &copy; <?= date("Y") ?> Fit for Fun - Alle rechten voorbehouden.
</footer>

</body>
</html>


