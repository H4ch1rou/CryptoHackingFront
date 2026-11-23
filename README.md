# Crypto Front-End Lab — Laboratorio de criptografía mal aplicada en el cliente

> ⚠️ **ADVERTENCIA:** esta aplicación es **intencionalmente vulnerable** y existe con fines **exclusivamente educativos**.
> Úsala solo en `localhost` o en una red de laboratorio aislada, en un entorno controlado y con autorización.
> **Nunca la expongas a Internet.** Todos los datos y credenciales son ficticios.

Segundo laboratorio del curso (LAB02). Mientras que LAB01 se centraba en lógica de negocio, reconocimiento y HTTP con Burp Suite, **LAB02 está enfocado por completo en criptografía mal aplicada en el front end**: claves, secretos y algoritmos que se envían al navegador y, por tanto, quedan al alcance de cualquiera.

La idea que recorre los 5 casos: **codificar no es cifrar, y una clave en el cliente es una clave pública de facto**. Las herramientas principales aquí no son el proxy, sino el **código fuente de la página**, las **DevTools del navegador** (Sources / Console) y utilidades como **CyberChef** o el **Decoder de Burp**.

## Laboratorios

| ID | Laboratorio | Primitiva | Dificultad |
|---|---|---|---|
| CF-01 | Login validado con un hash embebido | SHA-256 + crackeo de diccionario | Básico |
| CF-02 | Secreto "cifrado" con XOR y la clave en el JS | XOR reversible | Básico |
| CF-03 | AES en el navegador con la clave hardcodeada | AES-CBC (CryptoJS) | Intermedio |
| CF-04 | Código de activación forjado (hash débil en el cliente) | MD5 + sal en el cliente | Intermedio |
| CF-05 | Código VIP generado por un PRNG predecible | LCG / MINSTD con semilla pública | Avanzado |

Cada lab tiene 3 pistas escalonadas y un campo de verificación de flag. Son independientes: empieza por donde quieras.

## Requisitos

- Docker Engine / Docker Desktop con Docker Compose v2.
- Un navegador con DevTools (cualquiera). Opcional: Burp Suite, CyberChef.

## Cómo levantarlo

```bash
docker compose up --build -d
```

Abre **http://localhost:8080**, identifícate con tu nombre y elige un laboratorio.

```bash
LAB_PORT=8000 docker compose up --build -d      # lab en http://localhost:8000
```

### Variables de entorno

| Variable | Por defecto | Uso |
|---|---|---|
| `LAB_SECRET` | `dev-secret-cambiame` | Secreto con el que se derivan las flags. **Cámbialo en cada curso.** |
| `TEACHER_PASSWORD` | `profesor` | Contraseña del panel `/profesor.php`. |
| `LAB_PORT` | `8080` | Puerto del host. |
| `LAB_BIND` | `127.0.0.1` | Interfaz del host. Usa `0.0.0.0` **solo** en una red de laboratorio aislada. |

## Cómo funciona

### Alumnos y flags dinámicas
- En `login.php` el alumno ingresa su nombre y recibe un **token** (12 hex) que identifica su progreso.
- La flag de cada lab es `FLAG{ HMAC_SHA256(LAB_SECRET, token + ":" + lab_id)[0:16] }`: distinta por alumno, no se almacena y se recalcula al verificar.
- **Importante:** aunque el reto se resuelve en el cliente, la flag solo la revela el **servidor** cuando el alumno le entrega la respuesta correcta (contraseña, frase, código, etc.). Así el ejercicio demuestra el riesgo del front end *y* la flag sigue siendo verificable.
- La verificación de la flag es siempre en el servidor (`POST /api/verify.php`), con límite de 10 intentos por minuto.

### Pistas
3 por lab, de conceptual a casi-solución (nunca la flag). Se editan en `src/labs.php`.

### Panel del profesor
`http://localhost:8080/profesor.php` (contraseña `TEACHER_PASSWORD`): matriz alumnos × labs, tiempos, pistas usadas, export CSV, reinicio.

Ver las flags esperadas de un alumno (soporte en clase):
```bash
docker compose exec lab php seed/flags-alumno.php <student_token>
```

## Cómo resetear

| Qué | Cómo |
|---|---|
| Estado de **un** lab (volver a "sin resolver") | Botón «↺ Reiniciar estado del laboratorio» dentro del lab. |
| **Todo** el progreso de un alumno | Botón «Reiniciar todo mi progreso» en el portal, o desde el panel del profesor. |
| **Toda** la base | `docker compose exec lab php seed/init.php --reset` |
| Volver a cero absoluto | `docker compose down -v && docker compose up --build -d` |

## Estructura

```
├── docker-compose.yml / Dockerfile / docker/   # imagen todo-en-uno (php:8.2-apache + SQLite)
├── public/                 # document root de Apache
│   ├── index.php           # portal
│   ├── login.php           # identificación del alumno
│   ├── help-burp.php       # guía de Burp (opcional para estos labs)
│   ├── profesor.php        # panel del profesor
│   ├── api/                # verify.php, hint.php, reset.php
│   ├── assets/             # css + js del portal + vendor/crypto-js.min.js (CF-03/CF-04)
│   └── labs/               # cf01-… a cf05-…
├── src/                    # lógica común: config, db, auth, flags, hints, progress, labs, layout
├── seed/                   # init.php (BD idempotente) + flags-alumno.php (CLI profesor)
├── data/                   # SQLite (volumen persistente)
└── solutions/              # writeups SOLO profesor — fuera del docroot y fuera de la imagen
```

Cada laboratorio comenta la línea vulnerable con `⚠️ VULN`. CryptoJS (`public/assets/js/vendor/crypto-js.min.js`) se sirve **localmente**, así que CF-03 y CF-04 funcionan sin conexión a Internet.

## Seguridad del entorno

- Vulnerabilidades **intencionales, acotadas y documentadas**. No hay ejecución de comandos del sistema, subida de archivos ni conexiones salientes.
- Por defecto el contenedor solo escucha en `127.0.0.1`.
- `/solutions`, `/src`, `/seed` y `/data` no son accesibles vía web; los archivos `_*.php` están denegados por Apache.
- El propósito es la **formación en un entorno controlado y con autorización**. Aplicar estas técnicas contra sistemas sin permiso explícito es ilegal.
