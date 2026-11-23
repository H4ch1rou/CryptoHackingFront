<?php
/**
 * Registro central de laboratorios: metadata + pistas escalonadas.
 *
 * LAB02 — Criptografía mal aplicada en el FRONT END.
 * Todos los laboratorios comparten una idea: el secreto, la clave o el
 * algoritmo viajan al navegador. "Codificar no es cifrar", y una clave en
 * el cliente es una clave pública de facto.
 *
 * Para editar textos o pistas basta con modificar este arreglo.
 * Regla: la pista más detallada describe el CÓMO, nunca entrega la flag.
 */

declare(strict_types=1);

const CATEGORIES = [
    'crypto' => ['name' => 'Criptografía en el front end', 'icon' => '🔐', 'desc' => 'Todo lo que corre en el navegador es inspeccionable. Si la clave, el secreto o el algoritmo llegan al cliente, el cliente manda.'],
];

function labs(): array
{
    static $labs = null;
    if ($labs !== null) {
        return $labs;
    }

    $labs = [
        'CF-01' => [
            'slug'       => 'cf01-hash-cliente',
            'title'      => 'Login validado con un hash embebido',
            'category'   => 'crypto',
            'sector'     => 'SaaS',
            'difficulty' => 'Básico',
            'objective'  => 'Entender que validar una contraseña en el navegador no protege nada: el valor esperado (aquí, un hash) viaja al cliente y puede romperse sin tocar el servidor.',
            'scenario'   => 'El panel interno de NubeDocs comprueba la contraseña «en el navegador, para ir más rápido». El formulario calcula un hash y lo compara con un valor que trae la propia página. Entra como administrador.',
            'hints'      => [
                1 => 'La validación ocurre en JavaScript, no en el servidor. Abre el código fuente (Ctrl+U) o la pestaña Sources/Depurador y busca cómo decide si la contraseña es correcta.',
                2 => 'Verás una constante con un hash SHA-256 y una comparación contra sha256(lo que escribes). El hash es público: solo tienes que averiguar qué texto lo genera.',
                3 => 'Pega el hash en un servicio de crackeo (CrackStation) o pruébalo con rockyou: es una palabra de diccionario muy común. Escríbela en el formulario para entrar.',
            ],
        ],
        'CF-02' => [
            'slug'       => 'cf02-xor',
            'title'      => 'Secreto "cifrado" con XOR y la clave en el JS',
            'category'   => 'crypto',
            'sector'     => 'Corporativo',
            'difficulty' => 'Básico',
            'objective'  => 'Ver que un cifrado reversible (XOR) con la clave incluida en el cliente no aporta confidencialidad: cualquiera con el navegador recupera el texto plano.',
            'scenario'   => 'La intranet de Corp Vega guarda una «frase de acceso» ofuscada con XOR y la revela solo si introduces el PIN correcto… pero el PIN y la clave están en el mismo JavaScript. Recupera la frase de acceso.',
            'hints'      => [
                1 => 'No necesitas adivinar el PIN: el dato cifrado y la clave están, los dos, en el código de la página.',
                2 => 'Busca en el JS un arreglo/cadena hexadecimal (el texto cifrado) y la variable que se usa como clave en la operación XOR (el operador ^).',
                3 => 'Reproduce el XOR tú mismo: por cada byte del texto cifrado, byteCifrado ^ clave[i % largoClave]. Puedes hacerlo en la consola del navegador. El resultado es la frase de acceso.',
            ],
        ],
        'CF-03' => [
            'slug'       => 'cf03-aes-hardcodeada',
            'title'      => 'AES en el navegador con la clave hardcodeada',
            'category'   => 'crypto',
            'sector'     => 'Fintech',
            'difficulty' => 'Intermedio',
            'objective'  => 'Comprender que usar AES «de verdad» no sirve de nada si la clave se entrega al cliente. Un cifrado fuerte con clave pública sigue siendo descifrable por cualquiera.',
            'scenario'   => 'FinPay guarda un «documento confidencial» cifrado con AES (CryptoJS) en el propio HTML y lo descifra en el navegador cuando el usuario es administrador. El código de auditoría está dentro. Descífralo.',
            'hints'      => [
                1 => 'El cifrado es sólido (AES), pero la clave tiene que estar en algún sitio para que el navegador pueda descifrar. ¿Dónde?',
                2 => 'Localiza en el JS la llamada a CryptoJS.AES.decrypt(...) y la cadena que se usa como clave/passphrase, junto al texto cifrado en Base64 (empieza por U2FsdGVk…, que es «Salted__»).',
                3 => 'Desde la consola del navegador (CryptoJS ya está cargado) ejecuta CryptoJS.AES.decrypt(cifrado, clave).toString(CryptoJS.enc.Utf8). Obtendrás el código de auditoría.',
            ],
        ],
        'CF-04' => [
            'slug'       => 'cf04-licencia',
            'title'      => 'Código de activación forjado (hash débil en el cliente)',
            'category'   => 'crypto',
            'sector'     => 'Software',
            'difficulty' => 'Intermedio',
            'objective'  => 'Entender que un esquema de licencias o tokens cuyo algoritmo y «sal» viven en el cliente puede ser falsificado para cualquier valor que elijas.',
            'scenario'   => 'PixelForge desbloquea la edición PRO si introduces un código de activación válido para tu correo. El código es md5(correo + una sal secreta)… que no es tan secreta. Activa la licencia de gerencia@finpay.cl.',
            'hints'      => [
                1 => 'El cliente comprueba el código recomputándolo él mismo. Eso significa que tienes todo lo necesario para generar uno válido.',
                2 => 'Encuentra en el JS cómo se construye el código: concatena el correo con una «sal» fija y le aplica MD5. Anota esa sal.',
                3 => 'Calcula md5(correo_objetivo + sal) con la misma receta (por ejemplo md5("gerencia@finpay.cl:fp-...") en la consola o en CyberChef) e introduce ese código para el correo objetivo.',
            ],
        ],
        'CF-05' => [
            'slug'       => 'cf05-prng-predecible',
            'title'      => 'Código VIP generado por un PRNG predecible',
            'category'   => 'crypto',
            'sector'     => 'Retail',
            'difficulty' => 'Avanzado',
            'objective'  => 'Ver por qué un número «secreto» generado en el cliente con un PRNG no criptográfico y una semilla conocida es totalmente predecible.',
            'scenario'   => 'La promo de MegaRetail genera un «código VIP» del día con un generador pseudoaleatorio sembrado por un valor de campaña que aparece en la página. Reproduce el algoritmo y predice el código VIP ganador.',
            'hints'      => [
                1 => 'El código no se pide al servidor: se calcula en el navegador a partir de una semilla visible (el identificador de campaña). Mismo insumo ⇒ mismo resultado.',
                2 => 'Busca en el JS el generador congruencial lineal (LCG): una línea del tipo x = (a*x + c) % m. Anota la semilla, las constantes y cuántas iteraciones hace antes de formar el código.',
                3 => 'Reimplementa ese LCG con la misma semilla y el mismo número de pasos (en la consola o en Python) y formatea el resultado igual que la página (VIP-NNNNNN). Ese es el código válido.',
            ],
        ],
    ];

    foreach ($labs as $id => &$lab) {
        $lab['id']  = $id;
        $lab['url'] = '/labs/' . $lab['slug'] . '/';
    }
    unset($lab);
    return $labs;
}

function lab(string $id): array
{
    $all = labs();
    if (!isset($all[$id])) {
        http_response_code(404);
        exit('Laboratorio no encontrado');
    }
    return $all[$id];
}

function labs_by_category(): array
{
    $out = [];
    foreach (labs() as $id => $lab) {
        $out[$lab['category']][$id] = $lab;
    }
    return $out;
}
