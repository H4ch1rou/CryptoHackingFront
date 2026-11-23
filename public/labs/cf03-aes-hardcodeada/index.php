<?php
/**
 * CF-03 · AES en el navegador con la clave hardcodeada (Fintech, Intermedio)
 *
 * ⚠️ VULN (front end): el documento se cifra con AES (CryptoJS) pero la clave
 * (passphrase) viaja en el JS. AES es fuerte; una clave pública no. Cualquiera
 * descifra el contenido desde la consola del navegador.
 *
 * Cifrado (OpenSSL/CryptoJS, Salted__ + MD5): U2FsdGVkX1+ttADF3igGz1Hz0rx5bJ2NWBj9gb+LjTs=
 * clave: s3cr3t-finpay-2024   ·   texto plano (código de auditoría): AUDIT-9981
 */
require_once 'bootstrap.php';

[$lab, $student] = lab_begin('CF-03');

const CF03_CODE = 'AUDIT-9981';

$solved = !empty($_SESSION['cf03_ok']);
$err    = null;
if (is_post()) {
    $ans = strtoupper(trim((string) ($_POST['answer'] ?? '')));
    if (hash_equals(CF03_CODE, $ans)) {
        $solved = true;
        $_SESSION['cf03_ok'] = true;
    } else {
        $err = 'Ese no es el código de auditoría.';
    }
}

page_header($lab['title']);
lab_intro($lab);
app_open('💳 FinPay · Documentos', 'Clasificado — solo administración');
?>
<div class="grid-2">
    <div class="tile">
        <h3>Documento confidencial</h3>
        <p class="muted small">Contenido cifrado con AES-256. Se descifra en pantalla si tu rol es administrador.</p>
        <p class="mono small" style="word-break:break-all">cipher = U2FsdGVkX1+ttADF3igGz1Hz0rx5bJ2NWBj9gb+LjTs=</p>
        <label class="stack"><span>Tu rol</span>
            <select id="cf03-role" class="mono">
                <option value="cliente">cliente</option>
                <option value="auditor">auditor</option>
            </select>
        </label>
        <button class="btn" id="cf03-open" type="button" style="margin-top:.6rem">Abrir documento</button>
        <p id="cf03-out" class="mono" style="margin-top:1rem"></p>
    </div>
    <div class="tile">
        <h3>Verificar código de auditoría</h3>
        <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
        <form method="post" class="stack" autocomplete="off">
            <label>Código <input type="text" name="answer" class="mono" placeholder="XXXXX-XXXX" required></label>
            <button class="btn" type="submit">Verificar</button>
        </form>
        <p class="muted small">Pista: CryptoJS ya está cargado en esta página; puedes usarlo desde la consola.</p>
    </div>
</div>

<?php if ($solved): ?>
    <?= flag_box($lab['id'], 'Descifraste AES con la clave que venía en el cliente. Tu flag:') ?>
<?php endif; ?>

<script src="/assets/js/vendor/crypto-js.min.js"></script>
<script>
// ⚠️ VULN: la passphrase de AES está en el navegador.
(function () {
    var CIPHER = 'U2FsdGVkX1+ttADF3igGz1Hz0rx5bJ2NWBj9gb+LjTs=';
    var KEY = 's3cr3t-finpay-2024';

    document.getElementById('cf03-open').addEventListener('click', function () {
        var out = document.getElementById('cf03-out');
        // El "control de acceso" es solo visual: cliente no ve el texto…
        if (document.getElementById('cf03-role').value !== 'auditor') {
            out.textContent = '🔒 Necesitas rol auditor para ver el documento.';
            return;
        }
        var plain = CryptoJS.AES.decrypt(CIPHER, KEY).toString(CryptoJS.enc.Utf8);
        out.textContent = 'Código de auditoría: ' + plain;
    });
})();
</script>
<?php
app_close();
lab_panel($lab, $student);
page_footer();
