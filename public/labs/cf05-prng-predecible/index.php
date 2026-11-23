<?php
/**
 * CF-05 · Código VIP generado por un PRNG predecible (Retail, Avanzado)
 *
 * ⚠️ VULN (front end): el "código VIP ganador" se genera en el navegador con
 * un PRNG NO criptográfico (LCG Park-Miller / MINSTD) sembrado por un valor
 * de campaña visible en la página. Mismo seed + mismo algoritmo ⇒ mismo código.
 * Cualquiera reproduce el algoritmo y predice el código sin "jugar".
 *
 * Seed (id de campaña): 20240601 · a=16807 · m=2147483647 · 5 iteraciones
 * Código válido: VIP-216667
 */
require_once 'bootstrap.php';

[$lab, $student] = lab_begin('CF-05');

const CF05_SEED  = 20240601;
const CF05_A     = 16807;
const CF05_M     = 2147483647;
const CF05_STEPS = 5;

function cf05_expected_code(): string
{
    $x = CF05_SEED;
    for ($i = 0; $i < CF05_STEPS; $i++) {
        $x = (CF05_A * $x) % CF05_M;
    }
    return 'VIP-' . str_pad((string) ($x % 1000000), 6, '0', STR_PAD_LEFT);
}

$solved = !empty($_SESSION['cf05_ok']);
$err    = null;
if (is_post()) {
    $ans = strtoupper(trim((string) ($_POST['answer'] ?? '')));
    if (hash_equals(cf05_expected_code(), $ans)) {
        $solved = true;
        $_SESSION['cf05_ok'] = true;
    } else {
        $err = 'Ese no es el código VIP de la campaña.';
    }
}

page_header($lab['title']);
lab_intro($lab);
app_open('🛒 MegaRetail · Promo VIP', 'Campaña #20240601');
?>
<div class="grid-2">
    <div class="tile">
        <h3>¡Gira y gana!</h3>
        <p class="muted small">Campaña actual: <code class="mono">20240601</code>. Pulsa el botón para generar tu
            código y, si coincide con el código VIP ganador, obtienes el premio.</p>
        <button class="btn" id="cf05-spin" type="button">🎰 Generar mi código</button>
        <p id="cf05-out" class="mono" style="margin-top:1rem"></p>
    </div>
    <div class="tile">
        <h3>Reclamar premio VIP</h3>
        <p class="muted small">Introduce el código VIP ganador de la campaña.</p>
        <?php if ($err): ?><p class="err"><?= h($err) ?></p><?php endif; ?>
        <form method="post" class="stack" autocomplete="off">
            <label>Código VIP <input type="text" name="answer" class="mono" placeholder="VIP-NNNNNN" required></label>
            <button class="btn" type="submit">Reclamar</button>
        </form>
    </div>
</div>

<?php if ($solved): ?>
    <?= flag_box($lab['id'], 'Predijiste el código: el "azar" era determinista y estaba en el cliente. Tu flag:') ?>
<?php endif; ?>

<script>
// ⚠️ VULN: PRNG no criptográfico con semilla pública, todo en el navegador.
(function () {
    var SEED = 20240601;   // id de campaña (visible en la página)
    var A = 16807, M = 2147483647;

    // Genera el código VIP "ganador" del día.
    function winningCode() {
        var x = SEED;
        for (var i = 0; i < 5; i++) {
            x = (A * x) % M;   // LCG MINSTD (no apto para secretos)
        }
        return 'VIP-' + String(x % 1000000).padStart(6, '0');
    }

    document.getElementById('cf05-spin').addEventListener('click', function () {
        // Al usuario se le entrega un código al azar; casi nunca gana.
        var mine = 'VIP-' + String(Math.floor(Math.random() * 1000000)).padStart(6, '0');
        var out = document.getElementById('cf05-out');
        if (mine === winningCode()) {
            out.textContent = '🎉 ¡' + mine + ' es el código ganador!';
        } else {
            out.textContent = 'Tu código: ' + mine + ' — no coincide con el ganador. ¡Sigue intentando!';
        }
    });
})();
</script>
<?php
app_close();
lab_panel($lab, $student);
page_footer();
