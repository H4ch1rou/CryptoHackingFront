<?php
/**
 * CF-02 · Secreto "cifrado" con XOR y la clave en el JS (Corporativo, Básico)
 *
 * ⚠️ VULN (front end): el texto se guarda XOR-eado en el cliente y se descifra
 * en el navegador con una clave que está en el mismo JS. XOR con clave conocida
 * es reversible de inmediato: no hay confidencialidad.
 *
 * Texto cifrado (hex): 2c3e28392b004f584651   ·   clave: "clave-xor"
 * Texto plano (frase de acceso): ORION-7742
 */
require_once 'bootstrap.php';

[$lab, $student] = lab_begin('CF-02');

const CF02_PASSPHRASE = 'ORION-7742';

$solved = !empty($_SESSION['cf02_ok']);
$err    = null;
if (is_post()) {
    $ans = strtoupper(trim((string) ($_POST['answer'] ?? '')));
    if (hash_equals(CF02_PASSPHRASE, $ans)) {
        $solved = true;
        $_SESSION['cf02_ok'] = true;
    } else {
        $err = 'Esa no es la frase de acceso.';
    }
}

page_header($lab['title']);
lab_intro($lab);
app_open('🏢 Corp Vega · Intranet', 'Bóveda de credenciales');
?>
<div class="grid-2">
    <div class="tile">
        <h3>Revelar frase de acceso</h3>
        <p class="muted small">Introduce el PIN de 6 dígitos para desbloquear la frase de acceso del proveedor.</p>
        <div class="stack">
            <label>PIN <input type="text" id="cf02-pin" class="mono" maxlength="6" inputmode="numeric"></label>
            <button class="btn" id="cf02-unlock" type="button">Desbloquear</button>
        </div>
        <p id="cf02-out" class="mono" style="margin-top:1rem"></p>
    </div>
    <div class="tile">
        <h3>Verificar frase de acceso</h3>
        <p class="muted small">Cuando la tengas, confírmala aquí para registrar el hallazgo.</p>
        <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
        <form method="post" class="stack" autocomplete="off">
            <label>Frase de acceso <input type="text" name="answer" class="mono" placeholder="XXXXX-XXXX" required></label>
            <button class="btn" type="submit">Verificar</button>
        </form>
    </div>
</div>

<?php if ($solved): ?>
    <?= flag_box($lab['id'], 'Recuperaste la frase sin saber el PIN: la clave estaba en el cliente. Tu flag:') ?>
<?php endif; ?>

<script>
// ⚠️ VULN: dato cifrado + clave, ambos en el navegador. XOR es reversible.
(function () {
    var CIPHER_HEX = '2c3e28392b004f584651'; // frase de acceso ofuscada
    var KEY = 'clave-xor';                    // clave de "cifrado"
    var PIN = '730194';                       // el PIN también está aquí…

    function xorDecrypt(hex, key) {
        var out = '';
        for (var i = 0; i < hex.length; i += 2) {
            var b = parseInt(hex.substr(i, 2), 16);
            var k = key.charCodeAt((i / 2) % key.length);
            out += String.fromCharCode(b ^ k);
        }
        return out;
    }

    document.getElementById('cf02-unlock').addEventListener('click', function () {
        var out = document.getElementById('cf02-out');
        if (document.getElementById('cf02-pin').value === PIN) {
            out.textContent = 'Frase de acceso: ' + xorDecrypt(CIPHER_HEX, KEY);
        } else {
            out.textContent = 'PIN incorrecto.';
        }
    });
})();
</script>
<?php
app_close();
lab_panel($lab, $student);
page_footer();
