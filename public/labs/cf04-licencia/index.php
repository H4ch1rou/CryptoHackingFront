<?php
/**
 * CF-04 · Código de activación forjado (Software, Intermedio)
 *
 * ⚠️ VULN (front end): el código de licencia se valida recomputándolo en el
 * cliente como md5(correo + ":" + SAL), con la sal incrustada en el JS. Quien
 * lea el JS puede forjar un código válido para cualquier correo.
 *
 * SAL: fp-2024-activate
 * Correo objetivo: gerencia@finpay.cl
 * Código válido: md5("gerencia@finpay.cl:fp-2024-activate") = 60c582be8dd202dff45978f4597163c9
 */
require_once 'bootstrap.php';

[$lab, $student] = lab_begin('CF-04');

const CF04_SALT         = 'fp-2024-activate';
const CF04_TARGET_EMAIL = 'gerencia@finpay.cl';

$solved = !empty($_SESSION['cf04_ok']);
$err    = null;
if (is_post()) {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $code  = strtolower(trim((string) ($_POST['code'] ?? '')));
    $expected = md5($email . ':' . CF04_SALT);
    if ($email === CF04_TARGET_EMAIL && hash_equals($expected, $code)) {
        $solved = true;
        $_SESSION['cf04_ok'] = true;
    } elseif ($email !== CF04_TARGET_EMAIL) {
        $err = 'Activa la licencia del correo objetivo: ' . CF04_TARGET_EMAIL;
    } else {
        $err = 'Código de activación inválido para ese correo.';
    }
}

page_header($lab['title']);
lab_intro($lab);
app_open('🎨 PixelForge', 'Edición PRO');
?>
<div class="grid-2">
    <div class="tile">
        <h3>Activar licencia PRO</h3>
        <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
        <form method="post" class="stack" id="cf04-form" autocomplete="off">
            <label>Correo <input type="text" name="email" class="mono" value="gerencia@finpay.cl"></label>
            <label>Código de activación <input type="text" name="code" class="mono" placeholder="32 hex" required></label>
            <button class="btn" type="submit">Activar</button>
        </form>
        <p id="cf04-js-msg" class="muted small"></p>
    </div>
    <div class="tile">
        <h3>Comercial</h3>
        <p class="muted small">¿Perdiste tu código? El instalador lo valida sin conexión, así que no hace falta
            contactar a soporte: el propio programa sabe reconstruirlo.</p>
    </div>
</div>

<?php if ($solved): ?>
    <?= flag_box($lab['id'], 'Forjaste un código de activación válido. Tu flag:') ?>
<?php endif; ?>

<script src="/assets/js/vendor/crypto-js.min.js"></script>
<script>
// ⚠️ VULN: el algoritmo y la "sal" para generar el código están en el cliente.
(function () {
    var SALT = 'fp-2024-activate';

    // Validación offline: el instalador recomputa el código y compara.
    function expectedCode(email) {
        return CryptoJS.MD5(email.trim().toLowerCase() + ':' + SALT).toString();
    }

    var form = document.getElementById('cf04-form');
    var msg  = document.getElementById('cf04-js-msg');
    form.addEventListener('submit', function (ev) {
        var code = form.code.value.trim().toLowerCase();
        if (code !== expectedCode(form.email.value)) {
            // El cliente rechazaría el código; el servidor confirma igualmente.
            msg.textContent = 'El validador local no reconoce ese código.';
        }
    });
})();
</script>
<?php
app_close();
lab_panel($lab, $student);
page_footer();
