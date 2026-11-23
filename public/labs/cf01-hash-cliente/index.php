<?php
/**
 * CF-01 · Login validado con un hash embebido (SaaS, Básico)
 *
 * ⚠️ VULN (front end): la contraseña se "valida" en el navegador comparando
 * sha256(password) contra una constante que viaja en la propia página. El
 * valor esperado es público; basta crackear el hash (palabra de diccionario).
 *
 * El servidor SÍ revela la flag solo con la contraseña correcta, pero no
 * necesita hacerlo: toda la información para resolverlo está en el cliente.
 */
require_once 'bootstrap.php';

[$lab, $student] = lab_begin('CF-01');

// El mismo hash que se expone en el JS de la página.
const CF01_EXPECTED_SHA256 = 'c68943be5eeeb3092a614aa6119bcb560217ed1b4ec44b9c99d6e2766e524049';

$solved = !empty($_SESSION['cf01_ok']);
$err    = null;
if (is_post()) {
    $pass = (string) ($_POST['password'] ?? '');
    if (hash_equals(CF01_EXPECTED_SHA256, hash('sha256', $pass))) {
        $solved = true;
        $_SESSION['cf01_ok'] = true;
    } else {
        $err = 'Contraseña incorrecta.';
    }
}

page_header($lab['title']);
lab_intro($lab);
app_open('☁️ NubeDocs · Consola interna', 'Acceso de administración');
?>
<div class="grid-2">
    <div class="tile">
        <h3>Acceso administrador</h3>
        <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
        <form method="post" class="stack" id="cf01-form" autocomplete="off">
            <label>Contraseña de administrador
                <input type="password" name="password" id="cf01-pass" class="mono" required>
            </label>
            <button class="btn" type="submit">Entrar</button>
        </form>
        <p id="cf01-js-msg" class="muted small"></p>
    </div>
    <div class="tile">
        <h3>Nota del equipo</h3>
        <p class="muted small">«Validamos la contraseña en el cliente para no golpear el backend en cada intento.
            Es solo la consola interna, nadie va a mirar el código.»</p>
    </div>
</div>

<?php if ($solved): ?>
    <?= flag_box($lab['id'], 'Entraste crackeando un hash expuesto en el cliente. Tu flag:') ?>
<?php endif; ?>

<script>
// ⚠️ VULN: la validación vive en el navegador. El hash esperado es público.
(function () {
    // SHA-256 de la contraseña de administrador (precalculado por "comodidad").
    var EXPECTED = 'c68943be5eeeb3092a614aa6119bcb560217ed1b4ec44b9c99d6e2766e524049';

    async function sha256Hex(str) {
        var buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(str));
        return Array.from(new Uint8Array(buf)).map(function (b) {
            return b.toString(16).padStart(2, '0');
        }).join('');
    }

    var form = document.getElementById('cf01-form');
    var msg  = document.getElementById('cf01-js-msg');
    form.addEventListener('submit', async function (ev) {
        var h = await sha256Hex(document.getElementById('cf01-pass').value);
        if (h !== EXPECTED) {
            ev.preventDefault();
            msg.textContent = 'El navegador rechazó la contraseña (hash distinto).';
        }
        // Si coincide, el formulario se envía y el servidor confirma el acceso.
    });
})();
</script>
<?php
app_close();
lab_panel($lab, $student);
page_footer();
