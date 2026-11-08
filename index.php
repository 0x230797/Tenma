<?php
/**
 * =============================================================================
 *  TENMA — tablón anónimo para discusión, imágenes y debates breves
 * =============================================================================
 *
 * Aplicación completa en un único archivo. No requiere Composer ni separar el
 * código en más ficheros: la configuración, la lógica, las vistas y los
 * controladores viven aquí.
 *
 * ÍNDICE
 *   1. Configuración
 *   2. Arranque y endurecimiento
 *   3. Utilidades generales
 *   4. Rutas y URLs
 *   5. IP, proxies y secretos
 *   6. Sesión y autenticación de administración
 *   7. Base de datos
 *   8. Publicaciones, baneos, reportes y límites
 *   9. Limitador de peticiones (rate limiting)
 *  10. Formato de comentarios y vistas de publicaciones
 *  11. Plantilla HTML, estilos y script
 *  12. Generadores de páginas
 *  13. Controladores de peticiones
 *  14. Enrutador
 *
 * SEGURIDAD — LEE ANTES DE PUBLICAR
 *   - Cambia la contraseña de administración. Genera un hash nuevo con:
 *       php -r "echo password_hash('TU_CONTRASENA', PASSWORD_DEFAULT), PHP_EOL;"
 *     y sustitúyelo en 'adminpasswordhash'. Como alternativa, define la
 *     variable de entorno TENMA_ADMIN_PASSWORD_HASH para no guardar el hash
 *     dentro del archivo.
 *   - Publica el sitio por HTTPS: la cookie de administración se marca como
 *     Secure automáticamente cuando la petición llega por HTTPS.
 *   - Deja 'trustedproxies' vacío salvo que estés detrás de un proxy propio;
 *     de lo contrario cualquiera podría falsificar su IP con cabeceras.
 *   - Los archivos .htaccess se generan solos. No los edites a mano: se
 *     reescriben cuando cambia la versión del generador.
 *   - Da permisos de escritura solo a las carpetas del proyecto que lo
 *     necesitan y mantén el resto del servidor fuera del alcance de PHP.
 *
 * Compatible con PHP 7.4 o superior y SQLite (pdo_sqlite).
 */

/* =============================================================================
 * 1. CONFIGURACIÓN
 * ========================================================================== */

/**
 * Hash bcrypt de la contraseña de ejemplo que se distribuye con Tenma.
 * Se usa únicamente para avisar en el panel de que sigue activa; no se
 * guarda ni se compara ninguna contraseña en texto plano.
 */
const DEFAULT_ADMIN_PASSWORD_HASH = '$2y$10$zySRVu7lckJtryP6KgXkeufif94bcBvhbvecQLmhNAFLauqbI/jDy';

/** Intentos de acceso al panel antes de bloquear temporalmente la IP. */
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_SECS = 900; // 15 minutos

/** Longitud del hash de IP que se almacena en la base de datos. */
const IP_HASH_LENGTH = 16;

$config = [
    // Identidad y aspecto
    'title'                     => 'Tenma',
    'subtitle'                  => 'Tablón anónimo para discusión, imágenes y debates breves',
    'siteurl'                   => '', // URL pública canónica, p. ej. https://ejemplo.com/tenma
    'timezone'                  => 'America/Mexico_City',
    'gradient'                  => '#FED6AF',
    'background'                => '#FFFFEE',
    'section'                   => '#FCA',
    'textcolor'                 => '#800',
    'linkcolor'                 => '#0000EE',
    'linkhover'                 => '#FF0000',
    'fonts'                     => 'arial, helvetica, sans-serif',
    'formsidecolor'             => '#EA8',
    'border'                    => '#800',
    'postbackground'            => '#EAD6CA',
    'postborder'                => '#D9BFB7',
    'posternamecolor'           => '#117743',
    'postsubjectcolor'          => '#CC1105',
    'errortextcolor'            => '#B00020',
    'menubackground'            => '#F0E0D6',
    'menuborder'                => '#C9A79D',
    'menuhoverbackground'       => '#E4C8BD',
    'placeholderbackground'     => '#EEAA88',
    'reportcardbackground'      => '#FFF8F2',
    'reportcardborder'          => '#E09060',
    'mutedtextcolor'            => '#888888',
    'helpbordercolor'           => '#DDDDDD',
    'helpdesccolor'             => '#666666',
    'mobilelinkbackground'      => '#EAD6CA',
    'mobilebuttonbordercolor'   => '#C0A69D',

    // Textos del tablón
    'defaultname'               => 'Anónimo',
    'deletionphrase'            => 'Eliminado',
    'rules' => [
        [
            'title' => 'Respeto y convivencia',
            'desc'  => 'Debate las ideas sin atacar a las personas. No se permiten insultos dirigidos, acoso, amenazas ni incitación a la violencia.',
        ],
        [
            'title' => 'Contenido legal',
            'desc'  => 'No publiques material cuya creación, posesión o distribución infrinja las leyes aplicables.',
        ],
        [
            'title' => 'Privacidad',
            'desc'  => 'No compartas información privada o que permita identificar o localizar a alguien sin su consentimiento, por ejemplo, domicilio, teléfono, documentos o datos de contacto.',
        ],
        [
            'title' => 'Imágenes y archivos',
            'desc'  => 'Solo se aceptan imágenes JPG, PNG, GIF y WEBP de hasta 3 MB por archivo. No intentes subir archivos ejecutables ni disfrazar otros tipos de archivo como imágenes.',
        ],
        [
            'title' => 'Spam y publicidad',
            'desc'  => 'Evita publicar repetidamente, inundar los hilos o enviar publicidad, enlaces comerciales y propaganda no solicitada.',
        ],
        [
            'title' => 'Enlaces y seguridad',
            'desc'  => 'No publiques enlaces o archivos que distribuyan malware, intenten robar credenciales o datos, suplanten identidades o engañen a otros usuarios.',
        ],
        [
            'title' => 'Protección de menores',
            'desc'  => 'No publiques contenido sexual de menores ni información o imágenes que permitan identificarlos, localizarlos o ponerlos en riesgo.',
        ],
        [
            'title' => 'Uso de reportes',
            'desc'  => 'Reporta publicaciones que incumplan estas reglas e indica el motivo. Los reportes se revisan; no los uses para hostigar a otros usuarios.',
        ],
        [
            'title' => 'Moderación',
            'desc'  => 'El administrador puede retirar contenido o bloquear el acceso al tablón, incluso mediante la dirección IP o su hash. Las medidas pueden aplicarse sin aviso previo ante infracciones claras.',
        ],
        [
            'title' => 'Visibilidad del contenido',
            'desc'  => 'Al publicar, aceptas que el contenido se almacene y sea visible públicamente. No publiques nada que necesites mantener privado; el contenido permanecerá disponible hasta que sea eliminado.',
        ],
    ],
    'helps' => [
        [
            'title'  => 'Citar / responder',
            'syntax' => '>>123',
            'desc'   => 'Cita y enlaza a la publicación No.123.',
            'html'   => '<a href="#" style="color:#0000EE;">&gt;&gt;123</a>',
        ],
        [
            'title'  => 'Greentext',
            'syntax' => '>texto verde',
            'desc'   => 'Líneas que empiezan con &gt; se muestran en verde.',
            'html'   => '<span style="color:#789922;">&gt;texto verde</span>',
        ],
        [
            'title'  => 'Pinktext',
            'syntax' => '<texto rosa',
            'desc'   => 'Líneas que empiezan con &lt; se muestran en rosa.',
            'html'   => '<span style="color:#E0609A;">&lt;texto rosa</span>',
        ],
        [
            'title'  => 'Negrita',
            'syntax' => '**Negrita**',
            'desc'   => 'Rodea el texto con doble asterisco.',
            'html'   => '<strong>Negrita</strong>',
        ],
        [
            'title'  => 'Cursiva',
            'syntax' => '~Cursiva~',
            'desc'   => 'Rodea el texto con tilde.',
            'html'   => '<em>Cursiva</em>',
        ],
        [
            'title'  => 'Subrayado',
            'syntax' => '_Subrayado_',
            'desc'   => 'Rodea el texto con guiones bajos.',
            'html'   => '<u>Subrayado</u>',
        ],
        [
            'title'  => 'Tachado',
            'syntax' => '-Tachado-',
            'desc'   => 'Rodea el texto con guiones.',
            'html'   => '<s>Tachado</s>',
        ],
    ],

    // Administración
    'adminusername'      => 'admin',
    'adminpasswordhash'  => DEFAULT_ADMIN_PASSWORD_HASH,
    'managecookie'       => 'tenma_manage',
    'adminsessionttl'    => 12 * 3600, // validez de la cookie de administración

    // Rutas (relativas al directorio de este archivo)
    'lockfile'           => 'tenma.lock',
    'buildlockfile'      => 'tenma-build.lock',
    'tenmafile'          => 'index.php',
    'rulesfile'          => 'rules.html',
    'helpfile'           => 'help.html',
    'errorfile'          => 'error.html',
    'databasefolder'     => 'database/',
    'databasefile'       => 'database/tenma.sqlite',
    'uploadfolder'       => 'uploads/',
    'threaddir'          => 'threads/',

    // Límites del tablón
    'postsperpage'       => 10,
    'maxpages'           => 10,
    'forcedanonymity'    => false,
    'cooldown'           => 5,
    'namelimit'          => 20,
    'subjectlimit'       => 100,
    'commentlimit'       => 2000,
    'maxreplieshown'     => 5,

    // Límites de las imágenes
    'maximagesize'       => 3 * 1024 * 1024,
    'maximagewidth'      => 12000,
    'maximageheight'     => 12000,
    'maximagepixels'     => 40000000, // ~40 megapíxeles
    'allowedtypes'       => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],

    // Moderación automática y red
    'badstrings'         => [],
    'trustedproxies'     => [],
    'postsperhour'       => 40,
    'reportspershour'    => 12,
];

/* =============================================================================
 * 2. ARRANQUE Y ENDURECIMIENTO
 * ========================================================================== */

error_reporting(E_ALL);
ini_set('display_errors', '0');   // nunca mostrar trazas al visitante
ini_set('log_errors', '1');
ini_set('default_charset', 'UTF-8');

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
date_default_timezone_set((string)$config['timezone']);

// Resolver siempre las rutas relativas desde el directorio de la aplicación.
// Así el comportamiento no depende del directorio de trabajo de Apache/PHP.
if (getcwd() !== __DIR__) {
    @chdir(__DIR__);
}

/**
 * Manejo global de errores no controlados: registra el detalle y muestra una
 * página genérica para no filtrar rutas, consultas SQL ni versiones.
 */
set_exception_handler(static function (Throwable $error): void {
    error_log('[Tenma] ' . get_class($error) . ': ' . $error->getMessage()
        . ' en ' . $error->getFile() . ':' . $error->getLine());

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store');
    }

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>Error</title></head><body style="font-family:sans-serif;text-align:center;margin:3em 1em;">'
        . '<h1>Ha ocurrido un error</h1>'
        . '<p>No se pudo completar la operación. Inténtalo de nuevo en unos minutos.</p>'
        . '</body></html>';
});

if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Tenma requiere PHP 7.4 o superior. Estás usando PHP ' . PHP_VERSION);
}

foreach (['pdo_sqlite', 'mbstring'] as $requiredExtension) {
    if (!extension_loaded($requiredExtension)) {
        die('Tenma requiere la extensión de PHP "' . $requiredExtension . '", que no está activa en este servidor.');
    }
}

/* =============================================================================
 * 3. UTILIDADES GENERALES
 * ========================================================================== */

/**
 * Escapa texto para insertarlo en HTML (contenido y atributos).
 */
function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Devuelve un valor de cadena de $_GET/$_POST, o el valor por defecto si no
 * existe o si el cliente envió un array (evita errores de tipo y "array
 * injection").
 */
function inputString(array $source, string $key, string $default = ''): string {
    $value = $source[$key] ?? null;
    return is_string($value) ? $value : $default;
}

/**
 * Devuelve un entero no negativo validado a partir de $_GET/$_POST.
 */
function inputInt(array $source, string $key, int $default = 0, int $min = 0, int $max = PHP_INT_MAX): int {
    $value = $source[$key] ?? null;
    if (!is_string($value) || !ctype_digit($value)) {
        return $default;
    }
    $int = (int)$value;
    return ($int < $min || $int > $max) ? $default : $int;
}

function isPostRequest(): bool {
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Crea un directorio (y sus padres) si no existe, verificando el resultado.
 */
function ensureDir(string $dir, int $mode = 0755): void {
    if ($dir === '' || $dir === '.') {
        return;
    }
    if (is_dir($dir)) {
        return;
    }
    if (!@mkdir($dir, $mode, true) && !is_dir($dir)) {
        throw new RuntimeException('No se pudo crear el directorio: ' . $dir);
    }
    @chmod($dir, $mode);
}

/**
 * Escribe un archivo de forma atómica (temporal + rename) para que los
 * visitantes nunca lean una página a medio generar. Si rename() no está
 * disponible o falla (por ejemplo en Windows con el archivo abierto), se
 * recurre a una escritura bloqueante normal.
 */
function writeFileAtomic(string $path, string $contents): void {
    $directory = dirname($path);
    ensureDir($directory);

    // Nombre temporal aleatorio dentro del mismo directorio para que rename()
    // sea atómico y no cruce volúmenes. Se evita tempnam() porque en Windows
    // puede acabar creando el archivo en el directorio temporal del sistema.
    $temporary = $directory . '/.tenma-' . bin2hex(random_bytes(8)) . '.tmp';
    $written = @file_put_contents($temporary, $contents, LOCK_EX) === strlen($contents);

    if ($written) {
        @chmod($temporary, 0644);
    }
    if ($written && @rename($temporary, $path)) {
        return;
    }

    // Respaldo: escritura directa con bloqueo.
    if ($written && @file_put_contents($path, $contents, LOCK_EX) === strlen($contents)) {
        @unlink($temporary);
        return;
    }

    @unlink($temporary);
    throw new RuntimeException('No se pudo guardar el archivo: ' . $path);
}

/**
 * Cabeceras de seguridad para todo lo que sirve PHP. Las páginas estáticas
 * reciben las mismas cabeceras desde .htaccess.
 */
function sendSecurityHeaders(array $config): void {
    if (headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    // script-src 'self' solo funciona gracias a que no hay manejadores ni
    // bloques <script> en línea: todo el JS vive en tenma.js.
    header(
        "Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; "
        . "frame-ancestors 'self'; form-action 'self'; img-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "script-src 'self'; font-src 'self'; connect-src 'self'"
    );

    if (isHttps($config)) {
        header('Strict-Transport-Security: max-age=31536000');
    }
}

/**
 * Genera los .htaccess que protegen las carpetas de datos y de subidas.
 * Devuelve un mapa ruta => contenido.
 */
function htaccessFiles(array $config, string $errorDocumentUrl): array {
    $denyAuthz = <<<'HTACCESS'
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order allow,deny
    Deny from all
</IfModule>
HTACCESS;

    return [
        $config['uploadfolder'] . '.htaccess' => <<<HTACCESS
# Tenma - archivo generado automáticamente. No editar a mano.
# Tenma-htaccess-version: 3
Options -ExecCGI -Indexes -Includes
RemoveHandler .php .php3 .php4 .php5 .php7 .phtml .pl .py .cgi .sh .asp .aspx .shtml
AddType text/plain .php .php3 .php4 .php5 .php7 .phtml .pl .py .cgi .sh .asp .aspx .shtml

<IfModule mod_php.c>
    php_flag engine off
</IfModule>

<FilesMatch "\.(php\d?|phtml|pl|py|cgi|sh|asp|aspx|shtml|exe|htm|html)$">
{$denyAuthz}
</FilesMatch>

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set Content-Security-Policy "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox"
</IfModule>
HTACCESS,

        $config['databasefolder'] . '.htaccess' => <<<HTACCESS
# Tenma - archivo generado automáticamente. No editar a mano.
# Tenma-htaccess-version: 3
Options -Indexes
{$denyAuthz}
HTACCESS,

        $config['threaddir'] . '.htaccess' => <<<HTACCESS
# Tenma - archivo generado automáticamente. No editar a mano.
# Tenma-htaccess-version: 3
Options -Indexes

<FilesMatch "\.(php\d?|phtml|pl|py|cgi|sh|asp|aspx|shtml|exe|db|sqlite3|sqlite|sql|secret|lock)$">
{$denyAuthz}
</FilesMatch>
HTACCESS,

        '.htaccess' => <<<HTACCESS
# Tenma - archivo generado automáticamente. No editar a mano.
# Tenma-htaccess-version: 3
Options -Indexes

ErrorDocument 404 {$errorDocumentUrl}

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^(index\.html|board(-[0-9]+)?\.html)$ index.php?static=$1 [L,QSA]
    RewriteRule ^thread-([0-9]+)\.html$ threads/$1/ [L,R=301]
    RewriteRule ^threads/([0-9]+)/?$ index.php?staticthread=$1 [L,QSA]
    RewriteRule ^threads/([0-9]+)/(.*)$ index.php?staticthread=$1 [L,QSA]
</IfModule>

<FilesMatch "\.(sqlite|sqlite3|db|db3|sql|bak|backup|log|ini|conf|env|lock|secret|sh)$">
{$denyAuthz}
</FilesMatch>

<FilesMatch "^\.(?!htaccess$)">
{$denyAuthz}
</FilesMatch>

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "same-origin"
    Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"
    Header always set Content-Security-Policy "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; img-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self'; font-src 'self'; connect-src 'self'"
</IfModule>
HTACCESS,
    ];
}

/**
 * Escribe los .htaccess solo cuando faltan o cuando son de una versión
 * anterior del generador. Así se respetan las personalizaciones del usuario.
 */
function syncHtaccessFiles(array $config, string $errorDocumentUrl): void {
    foreach (htaccessFiles($config, $errorDocumentUrl) as $path => $content) {
        $needsWrite = !is_file($path);
        if (!$needsWrite) {
            $existing = (string)@file_get_contents($path);
            $needsWrite = strpos($existing, 'Tenma-htaccess-version: 3') === false;
        }
        if ($needsWrite) {
            writeFileAtomic($path, $content);
        }
    }
}

// Cabeceras y preparación del entorno de datos.
sendSecurityHeaders($config);

try {
    ensureDir($config['uploadfolder'], 0755);
    ensureDir($config['databasefolder'], 0700);
    ensureDir($config['threaddir'], 0755);
    syncHtaccessFiles($config, siteUrl($config['errorfile']));
} catch (RuntimeException $setupError) {
    error_log('[Tenma] Error de preparación: ' . $setupError->getMessage());
    die('Tenma no pudo preparar sus carpetas de datos. Revisa los permisos de escritura del proyecto.');
}

/* =============================================================================
 * 4. RUTAS Y URLs
 * ========================================================================== */

/**
 * Ruta base de la aplicación a partir de SCRIPT_NAME (sin barra final).
 */
function siteBasePath(): string {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string)$_SERVER['SCRIPT_NAME']) : '';
    // dirname() usa el separador de la plataforma (en Windows devuelve "\"),
    // así que se normaliza antes de recortar para no generar URLs como "\/x".
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $scriptDir = rtrim($scriptDir, '/');
    return $scriptDir === '' || $scriptDir === '.' ? '' : $scriptDir;
}

/**
 * Construye una URL absoluta de ruta (sin esquema ni host) para el sitio.
 */
function siteUrl(string $path = ''): string {
    $base = siteBasePath();
    $path = '/' . ltrim($path, '/');
    return $base === '' ? $path : $base . $path;
}

/**
 * Nombre de host de la petición (sin puerto), validado. Se usa para comparar
 * el origen de los formularios.
 */
function requestHostName(): string {
    $host = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '')));
    if ($host === '') {
        return '';
    }
    $name = parse_url('http://' . $host, PHP_URL_HOST);
    if (!is_string($name) || $name === '') {
        return '';
    }
    $name = strtolower($name);
    return preg_match('/\A[\[\]a-z0-9.\-]+\z/', $name) ? $name : '';
}

/**
 * Host de la petición para construir URLs absolutas. Conserva el puerto
 * cuando no es el predeterminado del esquema.
 */
function requestHost(): string {
    $host = strtolower(trim((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '')));
    if (!preg_match('/\A(\[[0-9a-f:.]+\]|[a-z0-9.\-]+)(:\d{1,5})?\z/', $host)) {
        return requestHostName();
    }
    return (string)preg_replace('/:(?:80|443)\z/', '', $host);
}

/**
 * URL absoluta con esquema y host, útil para sitemap.xml y robots.txt.
 */
function absoluteSiteUrl(array $config, string $path = ''): string {
    $configured = trim((string)($config['siteurl'] ?? ''));
    if ($configured !== '') {
        // URL canónica fijada en la configuración: evita depender de la
        // cabecera Host (que un cliente puede falsificar) en sitemap.xml.
        return rtrim($configured, '/') . '/' . ltrim($path, '/');
    }

    $host = requestHost();
    if ($host === '') {
        $host = 'localhost';
    }
    $base = rtrim(siteBasePath(), '/');
    return (isHttps($config) ? 'https://' : 'http://') . $host . $base . '/' . ltrim($path, '/');
}

/**
 * Ruta de la cookie de sesión/administración, limitada al subdirectorio de
 * la aplicación cuando no está en la raíz del dominio.
 */
function cookiePath(): string {
    $base = siteBasePath();
    return $base === '' ? '/' : $base . '/';
}

function threadFilePath(int $num, array $config): string {
    return rtrim($config['threaddir'], '/') . '/thread-' . $num . '.html';
}

function threadUrl(int $num): string {
    return siteUrl('threads/' . $num . '/');
}

/**
 * Indica si la petición se sirve por HTTPS. Solo confía en cabeceras de proxy
 * si la IP de origen figura como proxy de confianza.
 */
function isHttps(array $config): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $remote = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($remote !== '' && isTrustedProxy($remote, $config)
        && strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        return true;
    }
    return false;
}

/* =============================================================================
 * 5. IP, PROXIES Y SECRETOS
 * ========================================================================== */

/**
 * Comprueba si una IP pertenece a un rango CIDR (IPv4 o IPv6).
 */
function ipMatchesCidr(string $ip, string $cidr): bool {
    $ipBin = @inet_pton($ip);
    if ($ipBin === false) {
        return false;
    }

    if (strpos($cidr, '/') === false) {
        $subnetBin = @inet_pton($cidr);
        return $subnetBin !== false && strlen($ipBin) === strlen($subnetBin) && $ipBin === $subnetBin;
    }

    [$subnet, $bits] = explode('/', $cidr, 2);
    if ($subnet === '' || !ctype_digit($bits)) {
        return false;
    }
    $subnetBin = @inet_pton($subnet);
    if ($subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
        return false;
    }

    $bits = (int)$bits;
    if ($bits > strlen($ipBin) * 8) {
        return false;
    }

    $bytes     = intdiv($bits, 8);
    $remainder = $bits % 8;
    if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
        return false;
    }
    if ($remainder > 0) {
        $mask = ~(0xFF >> $remainder) & 0xFF;
        if ((ord($ipBin[$bytes]) & $mask) !== (ord($subnetBin[$bytes]) & $mask)) {
            return false;
        }
    }
    return true;
}

/**
 * Indica si una IP de origen está en la lista de proxies inversos de confianza.
 */
function isTrustedProxy(string $remoteAddr, array $config): bool {
    $trustedProxies = $config['trustedproxies'] ?? [];
    if (!is_array($trustedProxies)) {
        return false;
    }
    foreach ($trustedProxies as $cidr) {
        if (is_string($cidr) && ipMatchesCidr($remoteAddr, $cidr)) {
            return true;
        }
    }
    return false;
}

/**
 * Determina la IP real del visitante. Solo se confía en cabeceras como
 * CF-Connecting-IP o X-Forwarded-For cuando la petición llega desde un proxy
 * declarado en 'trustedproxies'; en caso contrario se usa REMOTE_ADDR para
 * impedir que un cliente falsifique su IP y evada baneos.
 */
function clientIp(array $config): string {
    $remote = (string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    if (!empty($config['trustedproxies']) && isTrustedProxy($remote, $config)) {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR'] as $header) {
            if (!empty($_SERVER[$header])) {
                $candidate = trim(explode(',', (string)$_SERVER[$header])[0]);
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }
    }
    return $remote;
}

/**
 * Carga (creando si hace falta) un secreto local de 32 bytes. Se usa para
 * derivar el hash de IP y para firmar la sesión de administración. Sin estos
 * secretos, los hashes serían reversibles o falsificables.
 */
function loadOrCreateSecret(string $path, string $label): string {
    ensureDir(dirname($path), 0700);

    $lock = @fopen($path . '.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('No se pudo abrir el bloqueo del secreto de ' . $label . '.');
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        throw new RuntimeException('No se pudo bloquear el secreto de ' . $label . '.');
    }

    try {
        if (!is_file($path)) {
            $newSecret = bin2hex(random_bytes(32));
            if (file_put_contents($path, $newSecret, LOCK_EX) !== strlen($newSecret)) {
                throw new RuntimeException('No se pudo guardar el secreto de ' . $label . '.');
            }
            @chmod($path, 0600);
        }

        $secret = file_get_contents($path);
        if ($secret === false || preg_match('/\A[a-f0-9]{64}\z/', $secret) !== 1) {
            throw new RuntimeException('El secreto de ' . $label . ' no se pudo leer o no tiene un formato válido.');
        }
        return $secret;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function ipHashSecret(array $config): string {
    return loadOrCreateSecret(rtrim($config['databasefolder'], '/') . '/iphash.secret', 'IP');
}

function adminSessionSecret(array $config): string {
    return loadOrCreateSecret(rtrim($config['databasefolder'], '/') . '/admin.secret', 'administración');
}

/**
 * Compara un hash guardado con el actual y con el heredado (SHA-1 sin pepper)
 * para no perder baneos creados por versiones anteriores.
 */
function matchesClientIpHash(string $storedHash, string $currentHash, string $legacyHash): bool {
    return hash_equals($storedHash, $currentHash) || hash_equals($storedHash, $legacyHash);
}

/* =============================================================================
 * 6. SESIÓN Y AUTENTICACIÓN DE ADMINISTRACIÓN
 * ========================================================================== */

/**
 * Inicia la sesión de administración con cookies endurecidas. No se inicia
 * para visitantes anónimos: solo el panel la necesita (CSRF).
 */
function startAdminSession(array $config): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_name('tenma_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => cookiePath(),
        'domain'   => '',
        'secure'   => isHttps($config),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Hash de la contraseña de administración. Permite definirlo por variable de
 * entorno (TENMA_ADMIN_PASSWORD_HASH) para no guardarlo en el archivo.
 */
function adminPasswordHash(array $config): string {
    $environmentHash = getenv('TENMA_ADMIN_PASSWORD_HASH');
    if (is_string($environmentHash) && preg_match('/\A\$(2[aby]|argon2i|argon2id)\$/', $environmentHash) === 1) {
        return $environmentHash;
    }
    return (string)$config['adminpasswordhash'];
}

function adminCookieOptions(array $config, int $expires = 0): array {
    return [
        'expires'  => $expires,
        'path'     => cookiePath(),
        'domain'   => '',
        'secure'   => isHttps($config),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

/**
 * Token de sesión firmado con caducidad: "<expiración>.<firma HMAC>".
 * Incluye el hash de la contraseña, de modo que cambiarla invalida todas las
 * cookies emitidas anteriormente.
 */
function adminSessionToken(array $config, int $expiresAt): string {
    $payload = $config['adminusername'] . '|' . $expiresAt . '|'
        . hash('sha256', adminPasswordHash($config));
    return $expiresAt . '.' . hash_hmac('sha256', $payload, adminSessionSecret($config));
}

/**
 * Emite/renueva la cookie de administración (caducidad deslizante).
 */
function issueAdminCookie(array $config): void {
    $ttl = max(300, (int)($config['adminsessionttl'] ?? 43200));
    $token = adminSessionToken($config, time() + $ttl);
    setcookie($config['managecookie'], $token, adminCookieOptions($config));
}

/**
 * Comprueba la cookie de administración: firma válida y sin caducar.
 */
function isAdminAuthenticated(array $config): bool {
    $cookie = $_COOKIE[$config['managecookie']] ?? '';
    if (!is_string($cookie) || $cookie === '') {
        return false;
    }

    $parts = explode('.', $cookie, 2);
    if (count($parts) !== 2 || !ctype_digit($parts[0])) {
        return false;
    }

    $expiresAt = (int)$parts[0];
    if ($expiresAt < time()) {
        return false;
    }

    return hash_equals(adminSessionToken($config, $expiresAt), $cookie);
}

/**
 * Fuerza la invalidación de todas las cookies de administrador emitidas.
 */
function rotateAdminSessionSecret(array $config): void {
    $path = rtrim($config['databasefolder'], '/') . '/admin.secret';
    ensureDir(dirname($path), 0700);

    $lock = fopen($path . '.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('No se pudo abrir el bloqueo del secreto de administración.');
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        throw new RuntimeException('No se pudo bloquear el secreto de administración.');
    }

    try {
        $newSecret = bin2hex(random_bytes(32));
        if (file_put_contents($path, $newSecret, LOCK_EX) !== strlen($newSecret)) {
            throw new RuntimeException('No se pudo renovar el secreto de administración.');
        }
        @chmod($path, 0600);
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function generateAdminCsrfToken(array $config): string {
    startAdminSession($config);

    if (empty($_SESSION['tenma_admin_csrf']) || !is_string($_SESSION['tenma_admin_csrf'])) {
        $_SESSION['tenma_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['tenma_admin_csrf'];
}

function verifyAdminCsrfToken(array $config, $token): bool {
    startAdminSession($config);

    $expected = $_SESSION['tenma_admin_csrf'] ?? '';
    return is_string($expected) && $expected !== '' && is_string($token) && hash_equals($expected, $token);
}

/**
 * Comprueba que una petición POST provenga del mismo sitio. Es una defensa
 * CSRF adicional para los formularios públicos (reportes y borrado), que no
 * tienen sesión ni token propio.
 */
function assertSameOriginRequest(array $config): void {
    if (!isPostRequest()) {
        return;
    }

    $fetchSite = strtolower(trim((string)($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '')));
    if ($fetchSite !== '' && !in_array($fetchSite, ['same-origin', 'same-site', 'none'], true)) {
        showError($config, 'La solicitud fue bloqueada por seguridad. Recarga la página e inténtalo de nuevo.', 403);
    }

    $host = requestHostName();
    if ($host === '') {
        return;
    }

    $origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
    if ($origin !== '' && $origin !== 'null') {
        $originHost = strtolower((string)parse_url($origin, PHP_URL_HOST));
        if ($originHost === '' || $originHost !== $host) {
            showError($config, 'La solicitud fue bloqueada por seguridad. Recarga la página e inténtalo de nuevo.', 403);
        }
        return;
    }

    $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
    if ($referer !== '') {
        $refererHost = strtolower((string)parse_url($referer, PHP_URL_HOST));
        if ($refererHost === '' || $refererHost !== $host) {
            showError($config, 'La solicitud fue bloqueada por seguridad. Recarga la página e inténtalo de nuevo.', 403);
        }
    }
}

/**
 * Valida una ruta de retorno interna ("/Tenma/board.html") para evitar
 * redirecciones abiertas.
 */
function isSafeReturnPath(string $path): bool {
    if ($path === '' || $path[0] !== '/') {
        return false;
    }
    if (strpos($path, '//') === 0 || strpos($path, '\\') !== false) {
        return false;
    }
    if (preg_match('/[\x00-\x1F\x7F]/', $path) === 1) {
        return false;
    }
    return preg_match('#\A/[A-Za-z0-9._~!$&\'()*+,;=:@%/?-]*\z#', $path) === 1;
}

/* =============================================================================
 * 7. BASE DE DATOS
 * ========================================================================== */

/**
 * Devuelve una conexión PDO reutilizable a la base de datos SQLite del tablón.
 */
function getPDO(array $config): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    ensureDir(dirname($config['databasefile']), 0700);

    $pdo = new PDO('sqlite:' . $config['databasefile']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    $pdo->exec('PRAGMA synchronous = NORMAL');

    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        num         INTEGER PRIMARY KEY AUTOINCREMENT,
        name        TEXT    NOT NULL,
        subject     TEXT    NOT NULL DEFAULT '',
        comment     TEXT    NOT NULL DEFAULT '',
        time        TEXT    NOT NULL,
        now         INTEGER NOT NULL,
        postiphash  TEXT    NOT NULL,
        deleted     INTEGER NOT NULL DEFAULT 0,
        image       TEXT    NOT NULL DEFAULT '',
        parent      INTEGER NOT NULL DEFAULT 0,
        filesize    INTEGER NOT NULL DEFAULT 0,
        locked      INTEGER NOT NULL DEFAULT 0,
        pinned      INTEGER NOT NULL DEFAULT 0,
        author_admin INTEGER NOT NULL DEFAULT 0
    )");

    // Migraciones incrementales para instalaciones anteriores.
    $postColumns = $pdo->query('PRAGMA table_info(posts)')->fetchAll(PDO::FETCH_COLUMN, 1);
    foreach ([
        'locked'       => 'ALTER TABLE posts ADD COLUMN locked INTEGER NOT NULL DEFAULT 0',
        'pinned'       => 'ALTER TABLE posts ADD COLUMN pinned INTEGER NOT NULL DEFAULT 0',
        'author_admin' => 'ALTER TABLE posts ADD COLUMN author_admin INTEGER NOT NULL DEFAULT 0',
    ] as $column => $migration) {
        if (!in_array($column, $postColumns, true)) {
            $pdo->exec($migration);
        }
    }

    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_posts_parent ON posts(parent)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_posts_ip_time ON posts(postiphash, now)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_posts_visible ON posts(deleted, parent, now)');

    $pdo->exec("CREATE TABLE IF NOT EXISTS bans (
        hash       TEXT    PRIMARY KEY,
        expires_at INTEGER NOT NULL DEFAULT 0
    )");
    $banColumns = $pdo->query('PRAGMA table_info(bans)')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('expires_at', $banColumns, true)) {
        $pdo->exec('ALTER TABLE bans ADD COLUMN expires_at INTEGER NOT NULL DEFAULT 0');
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
        id     INTEGER PRIMARY KEY AUTOINCREMENT,
        num    INTEGER NOT NULL,
        reason TEXT    NOT NULL DEFAULT '',
        time   TEXT    NOT NULL
    )");
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reports_num ON reports(num)');

    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        hash        TEXT    PRIMARY KEY,
        attempts    INTEGER NOT NULL DEFAULT 0,
        lastattempt INTEGER NOT NULL DEFAULT 0
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key   TEXT PRIMARY KEY,
        setting_value TEXT NOT NULL DEFAULT ''
    )");

    // Ventanas de limitación de peticiones (reportes, publicaciones, etc.).
    $pdo->exec("CREATE TABLE IF NOT EXISTS rate_limits (
        bucket       TEXT    PRIMARY KEY,
        hits         INTEGER NOT NULL DEFAULT 0,
        window_start INTEGER NOT NULL DEFAULT 0
    )");

    return $pdo;
}

/**
 * Lee un ajuste del sitio y devuelve un valor vacío si todavía no está guardado.
 */
function getSiteSetting(array $config, string $key): string {
    $stmt = getPDO($config)->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :key');
    $stmt->execute(['key' => $key]);
    $value = $stmt->fetchColumn();
    return $value !== false ? (string)$value : '';
}

/**
 * Guarda un ajuste editable desde el panel de administración.
 */
function setSiteSetting(array $config, string $key, string $value): void {
    getPDO($config)->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value)
         ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value'
    )->execute(['key' => $key, 'value' => $value]);
}

/* =============================================================================
 * 8. PUBLICACIONES, BANEOS, REPORTES Y LÍMITES
 * ========================================================================== */

/**
 * Crea los iconos de estado de hilo a partir de los GIF proporcionados.
 */
function ensureThreadActionIcons(): void {
    $icons = [
        'admin.gif'       => 'R0lGODlhEAAQAJECAAAAAPAAAP///wAAACH5BAEAAAIALAAAAAAQABAAAAIvlI+pq+D9DAgUoFkPDlbs7lFZKIJOJJ3MyraoB14jFpOcVMpzrnF3OKlZYsMWowAAOw==',
        'thread-lock.gif' => 'R0lGODlhEAAQANUhAKGhoWqp6Qh1uYWs5hZl0Jq56HSk42Oc4TCG1lOW3UGO2aqqqoO16cHS6t7e3tDb6sXFxezs7Le3twxyvhBuxRVpzBpnzLm5uQlzusrY6rjM6RJqyA1wwKrC6Obm5tvg6sHBwQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAACEALAAAAAAQABAAAAZywJBwSCwaAcikcQiIQJ4XCWDZdCSRl2kRYCUCpMcuE0wkLDwLC4FgWYAWlXLk88g0GppOgUEpPwoDAwYHCQoIDBN+gIKEhgwYZRmLg4UIAQJlDZONlphDBJqBlIaXRBUMAamqqp5CGxQcExgCtLWtS7hBADs=',
        'thread-pin.gif'  => 'R0lGODlhEAAQAOZhADNVoDhbplWQ1jt4yDJvwZW036fE6KPC50JkrFuT1StKl2if3VuR1Ehxtxw2h0mCzVeR1iBBkzBUoW+e2tzc3CVGlXR0dCpVpqG+5DRxxSVXqyI+jS5Qnpq75WJvl16T1FuU13un3IaGhjhzxHFxcZKw3FV+wGxsbGx5n6ioqJ+fny9Pm9TU1FuP05G25TFSnS5Pm4Sx5C5qv6/J7F6X2SxJlo+Pjz98yoOn2VyW2ZW136fE6Udts7LN7kVrsYSt30lrsHim3Gyc2VN6vEOAzIWv4UNorz1qs5SUlDNvwXyq4aTC52ae3Zy44T15x0tyt0RstE6J0Hd3d8jIyDhkr1R9wEtsr6LB50uFzkBkrlKN04uy4mGV1Hir4oKCgo+46BkyhAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAGEALAAAAAAQABAAAAeFgGGCg4RVJoSIhA1XM0OJiVBdXwZPj4Q8C0wxGD6WYUY6RTQ5Sk1ZhAGpCAVbIBACAhNAg1YlO0sHPwlaRwC+gy84QT0dIR9RVDCPEk4MLlxYRAqeHCMPQjcDNZ4oKwQDLUkblioUHhUyGReWNixIghEaDo9eUyKEYI8WKVKehCQn/BEKBAA7',
    ];

    foreach ($icons as $filename => $encoded) {
        if (is_file($filename)) {
            continue;
        }
        $image = base64_decode($encoded, true);
        if ($image === false || file_put_contents($filename, $image, LOCK_EX) !== strlen($image)) {
            throw new RuntimeException('No se pudo generar el icono ' . $filename . '.');
        }
    }
}

/**
 * Lee todas las publicaciones almacenadas y las devuelve como un array asociativo.
 */
function readPosts(array $config): array {
    return getPDO($config)->query('SELECT * FROM posts ORDER BY num ASC')->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca una publicación concreta por su identificador numérico.
 */
function getPostByNum(array $config, int $num): ?array {
    $stmt = getPDO($config)->prepare('SELECT * FROM posts WHERE num = :num');
    $stmt->execute(['num' => $num]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row !== false ? $row : null;
}

/**
 * Cambia el estado de bloqueo o fijado de un hilo.
 */
function setThreadFlag(array $config, int $num, string $flag, bool $enabled): void {
    if (!in_array($flag, ['locked', 'pinned'], true)) {
        throw new InvalidArgumentException('El estado del hilo no es válido.');
    }
    getPDO($config)->prepare("UPDATE posts SET $flag = :enabled WHERE num = :num AND parent = 0")
        ->execute(['enabled' => $enabled ? 1 : 0, 'num' => $num]);
}

/**
 * Recupera todas las respuestas asociadas a un hilo.
 */
function getRepliesOf(array $config, int $parent): array {
    $stmt = getPDO($config)->prepare('SELECT * FROM posts WHERE parent = :parent');
    $stmt->execute(['parent' => $parent]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Inserta una nueva publicación y devuelve su ID asignado.
 */
function insertPost(array $config, array $data): int {
    $pdo  = getPDO($config);
    $stmt = $pdo->prepare('INSERT INTO posts
        (name, subject, comment, time, now, postiphash, deleted, image, parent, filesize, author_admin)
        VALUES (:name, :subject, :comment, :time, :now, :postiphash, 0, :image, :parent, :filesize, :author_admin)');
    $stmt->execute($data);
    return (int)$pdo->lastInsertId();
}

/**
 * Marca una publicación como eliminada sin borrar su registro.
 */
function markPostDeleted(array $config, int $num): void {
    getPDO($config)->prepare('UPDATE posts SET deleted = 1 WHERE num = :num')->execute(['num' => $num]);
}

/**
 * Comprueba que un nombre de archivo de subida sea simple (sin rutas ni
 * caracteres raros). Se aplica al leerlo de la base de datos para que un
 * registro manipulado no pueda apuntar fuera de uploads/.
 */
function isSafeUploadName(string $name): bool {
    return $name !== ''
        && basename($name) === $name
        && preg_match('/\A[A-Za-z0-9._-]+\z/', $name) === 1;
}

/**
 * Elimina el archivo de imagen asociado a una publicación, si existe.
 * Valida el nombre para descartar rutas manipuladas almacenadas en la BD.
 */
function deletePostImageFile(array $config, array $post): void {
    $image = (string)($post['image'] ?? '');
    if (!isSafeUploadName($image)) {
        return;
    }
    $path = rtrim($config['uploadfolder'], '/') . '/' . $image;
    if (is_file($path)) {
        @unlink($path);
    }
}

/**
 * Elimina la imagen de una publicación y la marca como eliminada. Si es el
 * primer mensaje de un hilo, marca también todas sus respuestas para no
 * dejar registros huérfanos ni imágenes accesibles.
 */
function deletePostCompletely(array $config, int $num): void {
    $post = getPostByNum($config, $num);
    if ($post === null) {
        return;
    }

    deletePostImageFile($config, $post);
    markPostDeleted($config, $num);
    deleteReportsForPost($config, $num);

    if ((int)$post['parent'] === 0) {
        foreach (getRepliesOf($config, $num) as $reply) {
            deletePostImageFile($config, $reply);
            markPostDeleted($config, (int)$reply['num']);
            deleteReportsForPost($config, (int)$reply['num']);
        }
    }
}

/**
 * Borra un hilo completo (registros, imágenes, reportes y HTML generado).
 */
function deleteThreadCompletely(array $config, int $threadnum): void {
    $op = getPostByNum($config, $threadnum);
    if ($op !== null) {
        deletePostImageFile($config, $op);
    }
    foreach (getRepliesOf($config, $threadnum) as $reply) {
        deletePostImageFile($config, $reply);
    }

    $pdo = getPDO($config);
    $pdo->prepare('DELETE FROM reports WHERE num = :num OR num IN (SELECT num FROM posts WHERE parent = :parent)')
        ->execute(['num' => $threadnum, 'parent' => $threadnum]);
    $pdo->prepare('DELETE FROM posts WHERE num = :num OR parent = :parent')
        ->execute(['num' => $threadnum, 'parent' => $threadnum]);

    $threadFile = threadFilePath($threadnum, $config);
    if (is_file($threadFile)) {
        @unlink($threadFile);
    }
}

/**
 * Elimina hilos antiguos cuando se supera el límite de páginas permitido.
 */
function pruneOldThreads(array $config): void {
    $limit   = $config['postsperpage'] * $config['maxpages'];
    $threads = getThreads(readPosts($config));

    while (count($threads) > $limit) {
        $last = array_pop($threads);
        deleteThreadCompletely($config, (int)$last['op']['num']);
    }
}

/**
 * Comprueba si una IP o hash de IP está baneado. Devuelve la marca de tiempo
 * de expiración (0 = permanente) o null si no hay baneo activo.
 */
function getActiveBanExpiration(array $config, string $hash, string $legacyHash = ''): ?int {
    if ($legacyHash !== '') {
        $stmt = getPDO($config)->prepare('SELECT expires_at FROM bans
            WHERE (hash = :hash OR hash = :legacy) AND (expires_at = 0 OR expires_at > :now)
            ORDER BY CASE WHEN expires_at = 0 THEN 0 ELSE 1 END, expires_at DESC LIMIT 1');
        $stmt->execute(['hash' => $hash, 'legacy' => $legacyHash, 'now' => time()]);
    } else {
        $stmt = getPDO($config)->prepare('SELECT expires_at FROM bans
            WHERE hash = :hash AND (expires_at = 0 OR expires_at > :now)');
        $stmt->execute(['hash' => $hash, 'now' => time()]);
    }
    $expiresAt = $stmt->fetchColumn();
    return $expiresAt === false ? null : (int)$expiresAt;
}

function formatBanTimeRemaining(int $seconds): string {
    if ($seconds < 60) {
        return 'menos de un minuto';
    }

    $units = [
        'día'    => 86400,
        'hora'   => 3600,
        'minuto' => 60,
    ];
    $parts = [];
    foreach ($units as $unit => $unitSeconds) {
        $amount = intdiv($seconds, $unitSeconds);
        if ($amount > 0) {
            $parts[] = $amount . ' ' . $unit . ($amount === 1 ? '' : 's');
            $seconds %= $unitSeconds;
        }
        if (count($parts) === 2) {
            break;
        }
    }

    return implode(' y ', $parts);
}

/**
 * Añade o actualiza un baneo. expiresAt = 0 representa un baneo permanente.
 */
function addBan(array $config, string $hash, int $expiresAt = 0): void {
    getPDO($config)->prepare('INSERT INTO bans (hash, expires_at) VALUES (:hash, :expires_at)
        ON CONFLICT(hash) DO UPDATE SET expires_at = excluded.expires_at')
        ->execute(['hash' => $hash, 'expires_at' => $expiresAt]);
}

/**
 * Devuelve todos los baneos registrados.
 */
function getBans(array $config): array {
    return getPDO($config)->query('SELECT hash, expires_at FROM bans ORDER BY expires_at DESC, hash ASC')
        ->fetchAll(PDO::FETCH_ASSOC);
}

function removeBan(array $config, string $hash): void {
    getPDO($config)->prepare('DELETE FROM bans WHERE hash = :hash')->execute(['hash' => $hash]);
}

/**
 * Registra un reporte de moderación para una publicación concreta.
 */
function insertReport(array $config, int $num, string $reason): void {
    getPDO($config)->prepare('INSERT INTO reports (num, reason, time) VALUES (:num, :reason, :time)')
        ->execute(['num' => $num, 'reason' => $reason, 'time' => spanishDate()]);
}

function getReports(array $config): array {
    return getPDO($config)->query('SELECT * FROM reports ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

function deleteReport(array $config, int $id): void {
    getPDO($config)->prepare('DELETE FROM reports WHERE id = :id')->execute(['id' => $id]);
}

function deleteReportsForPost(array $config, int $num): void {
    getPDO($config)->prepare('DELETE FROM reports WHERE num = :num')->execute(['num' => $num]);
}

/**
 * Lanza un error si el hash de IP indicado ha superado el número máximo de
 * intentos fallidos de acceso al panel en la ventana de tiempo configurada.
 */
function checkLoginRateLimit(array $config, string $hash): void {
    $stmt = getPDO($config)->prepare('SELECT attempts, lastattempt FROM login_attempts WHERE hash = :hash');
    $stmt->execute(['hash' => $hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && (int)$row['attempts'] >= LOGIN_MAX_ATTEMPTS
        && (time() - (int)$row['lastattempt']) < LOGIN_LOCKOUT_SECS) {
        $wait = (int)ceil((LOGIN_LOCKOUT_SECS - (time() - (int)$row['lastattempt'])) / 60);
        header('Retry-After: ' . max(60, $wait * 60));
        showError($config, 'Demasiados intentos fallidos. Espera unos ' . max(1, $wait) . ' minuto(s) antes de volver a intentarlo.', 429);
    }
}

/**
 * Registra un intento fallido de acceso al panel de administración.
 */
function registerFailedLogin(array $config, string $hash): void {
    $now = time();
    getPDO($config)->prepare('INSERT INTO login_attempts (hash, attempts, lastattempt)
        VALUES (:hash, 1, :now)
        ON CONFLICT(hash) DO UPDATE SET
            attempts = CASE
                WHEN excluded.lastattempt - login_attempts.lastattempt > ' . LOGIN_LOCKOUT_SECS . ' THEN 1
                ELSE login_attempts.attempts + 1
            END,
            lastattempt = excluded.lastattempt')
        ->execute(['hash' => $hash, 'now' => $now]);
}

/**
 * Limpia el contador de intentos fallidos tras un inicio de sesión correcto.
 */
function clearLoginAttempts(array $config, string $hash): void {
    getPDO($config)->prepare('DELETE FROM login_attempts WHERE hash = :hash')->execute(['hash' => $hash]);
}

/**
 * Última publicación de una IP (para el tiempo de espera y la detección de
 * duplicados). El límite se aplica por IP, no de forma global.
 */
function getLastPostByHash(array $config, string $hash): ?array {
    $stmt = getPDO($config)->prepare('SELECT * FROM posts WHERE postiphash = :hash ORDER BY num DESC LIMIT 1');
    $stmt->execute(['hash' => $hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row !== false ? $row : null;
}

/**
 * Cuenta las publicaciones de una IP desde un instante dado.
 */
function countPostsByHashSince(array $config, string $hash, int $since): int {
    $stmt = getPDO($config)->prepare('SELECT COUNT(*) FROM posts WHERE postiphash = :hash AND now >= :since');
    $stmt->execute(['hash' => $hash, 'since' => $since]);
    return (int)$stmt->fetchColumn();
}

/* =============================================================================
 * 9. LIMITADOR DE PETICIONES
 * ========================================================================== */

/**
 * Limitador de ventana fija por "bucket" (por ejemplo "report:<hash>").
 * Devuelve true si la petición se permite y false si se superó el límite.
 */
function rateLimitAllow(array $config, string $bucket, int $max, int $window): bool {
    $pdo = getPDO($config);
    $now = time();

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT hits, window_start FROM rate_limits WHERE bucket = :bucket');
        $stmt->execute(['bucket' => $bucket]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false || ($now - (int)$row['window_start']) >= $window) {
            $pdo->prepare('INSERT INTO rate_limits (bucket, hits, window_start) VALUES (:bucket, 1, :now)
                ON CONFLICT(bucket) DO UPDATE SET hits = 1, window_start = excluded.window_start')
                ->execute(['bucket' => $bucket, 'now' => $now]);
            $pdo->commit();
            return true;
        }

        if ((int)$row['hits'] >= $max) {
            $pdo->commit();
            return false;
        }

        $pdo->prepare('UPDATE rate_limits SET hits = hits + 1 WHERE bucket = :bucket')
            ->execute(['bucket' => $bucket]);
        $pdo->commit();
        return true;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

/**
 * Elimina ventanas caducadas para que la tabla no crezca sin control.
 */
function pruneRateLimits(array $config): void {
    getPDO($config)->prepare('DELETE FROM rate_limits WHERE window_start < :limit')
        ->execute(['limit' => time() - 86400]);
}

/**
 * Muestra el error de baneo activo y termina la petición.
 */
function enforceBan(array $config, string $hash, string $legacyHash): void {
    // El administrador autenticado nunca queda fuera del panel: de lo
    // contrario un baneo por error de su propia IP sería irreversible.
    if (isAdminAuthenticated($config)) {
        return;
    }

    $expiresAt = getActiveBanExpiration($config, $hash, $legacyHash);
    if ($expiresAt === null) {
        return;
    }

    $message = $expiresAt === 0
        ? '<h3>Estás baneado de este tablón.</h3><br><h4>Tu baneo es permanente.</h4>'
        : '<h3>Estás baneado temporalmente de este tablón.</h3><br><h4>Tiempo restante: '
            . e(formatBanTimeRemaining(max(0, $expiresAt - time()))) . '.</h4>';

    showError($config, $message, 403, true);
}

/* =============================================================================
 * 10. FORMATO DE COMENTARIOS Y VISTAS DE PUBLICACIONES
 * ========================================================================== */

/**
 * Devuelve la fecha y hora actual en el formato legible usado por el tablón.
 */
function spanishDate(): string {
    $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    return date('d/m/y') . '(' . $days[(int)date('w')] . ')' . date('H:i:s');
}

/**
 * Convierte un timestamp en un texto humano como 'Hace 5min' o 'Hace 2h'.
 */
function relativeTime(int $timestamp): string {
    $diff = max(0, time() - $timestamp);
    if ($diff < 60)       return 'Ahora';
    if ($diff < 3600)     return 'Hace ' . floor($diff / 60) . 'min';
    if ($diff < 86400)    return 'Hace ' . floor($diff / 3600) . 'h';
    if ($diff < 2592000)  return 'Hace ' . floor($diff / 86400) . 'd';
    if ($diff < 31536000) return 'Hace ' . floor($diff / 2592000) . 'mes';
    return 'Hace ' . floor($diff / 31536000) . 'a';
}

/**
 * Muestra una página de error y termina la ejecución. El mensaje se escapa
 * por defecto; solo se permite HTML de confianza con $allowHtml = true.
 */
function showError(array $config, string $message, int $status = 400, bool $allowHtml = false): void {
    if (!headers_sent()) {
        http_response_code($status);
        header('Cache-Control: no-store');
    }

    $content = '<div style="background:#fff;border:1px solid #800">'
        . '<h3 style="margin:0 0 10px;padding:3px 10px;background:#FCA">Error</h3>'
        . '<div style="text-align:center;margin:0 0 10px">'
        . '<span class="error-message">' . ($allowHtml ? $message : e($message)) . '</span>'
        . '<br>[<a href="' . e(siteUrl('index.html')) . '">Inicio</a>]'
        . '</div></div>';

    echo renderPage($config['title'] ?: 'Tenma', $content, $config);
    exit;
}

/**
 * Formatea un número de bytes en una unidad legible.
 */
function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow   = min((int)floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);
    return round(max($bytes, 0) / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

/**
 * Botones para insertar formatos en un comentario.
 */
function commentFormatToolbar(): string {
    return '<div class="comment-format-toolbar" role="toolbar" aria-label="Formatos del comentario">'
        . '<button type="button" class="comment-format-button" data-format-open="**" data-format-close="**" aria-label="Negrita" title="Negrita"><strong>N</strong></button>'
        . '<button type="button" class="comment-format-button" data-format-open="~" data-format-close="~" aria-label="Cursiva" title="Cursiva"><em>C</em></button>'
        . '<button type="button" class="comment-format-button" data-format-open="_" data-format-close="_" aria-label="Subrayado" title="Subrayado"><u>S</u></button>'
        . '<button type="button" class="comment-format-button" data-format-open="-" data-format-close="-" aria-label="Tachado" title="Tachado"><s>T</s></button>'
        . '</div>';
}

/**
 * Aplica el formato visual del tablón al texto de un comentario:
 * >>citas, >greentext, <pinktext, **negrita**, ~cursiva~, _subrayado_, -tachado-.
 * Todo el texto se escapa antes de generar el HTML, por lo que el usuario no
 * puede inyectar etiquetas.
 */
function formatComment(string $text, array $allposts): string {
    $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');

    // >>citas con enlace
    $text = preg_replace_callback('/&gt;&gt;(\d+)/', function (array $match) use ($allposts): string {
        $num = (int)$match[1];
        foreach ($allposts as $post) {
            if ((int)$post['num'] === $num) {
                $thread = (int)$post['parent'] > 0 ? (int)$post['parent'] : (int)$post['num'];
                return '<a href="' . e(threadUrl($thread)) . '#p' . $num . '" class="quotelink">&gt;&gt;' . $num . '</a>';
            }
        }
        return '<s style="color:red;">&gt;&gt;' . $num . '</s>';
    }, $text);

    // Negrita: **texto**
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);

    // Cursiva: ~texto~
    $text = preg_replace('/~(.+?)~/s', '<em>$1</em>', $text);

    // Subrayado: _texto_
    $text = preg_replace('/_(.+?)_/s', '<u>$1</u>', $text);

    // Tachado: -texto-
    $text = preg_replace('/-(.+?)-/s', '<s>$1</s>', $text);

    // >greentext y <pinktext, por línea.
    $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
    foreach ($lines as &$line) {
        if (preg_match('/^&gt;[^>]/', $line) === 1) {
            $line = '<span style="color:#789922;">' . $line . '</span>';
        } elseif (preg_match('/^&lt;/', $line) === 1) {
            $line = '<span style="color:#E0609A;">' . $line . '</span>';
        }
    }
    unset($line);

    return implode('<br/>', $lines);
}

/**
 * Organiza las publicaciones en hilos, agrupando cada respuesta bajo su padre.
 */
function getThreads(array $posts): array {
    $threads = [];
    foreach ($posts as $post) {
        if ((int)$post['parent'] === 0 && (int)$post['deleted'] === 0) {
            $threads[(int)$post['num']] = ['op' => $post, 'replies' => []];
        }
    }
    foreach ($posts as $post) {
        $parent = (int)$post['parent'];
        if ($parent > 0 && isset($threads[$parent])) {
            $threads[$parent]['replies'][] = $post;
        }
    }

    $list = array_values($threads);
    usort($list, function (array $a, array $b): int {
        $aPinned = (int)($a['op']['pinned'] ?? 0);
        $bPinned = (int)($b['op']['pinned'] ?? 0);
        if ($aPinned !== $bPinned) {
            return $bPinned <=> $aPinned;
        }
        $aTime = !empty($a['replies']) ? (int)end($a['replies'])['now'] : (int)$a['op']['now'];
        $bTime = !empty($b['replies']) ? (int)end($b['replies'])['now'] : (int)$b['op']['now'];
        return $bTime - $aTime;
    });

    return $list;
}

/**
 * Pie de página reutilizable.
 */
function footerHtml(array $config): string {
    $title = $config['title'] ?: 'Tenma';
    return '<div style="text-align:center;font-size:.9rem;color:#800;margin-top:8px;padding:4px 0;">'
        . e($title) . ' &mdash; Tablón anónimo para discusión, imágenes y debates breves</div>';
}

/**
 * Envuelve el contenido en la plantilla HTML completa. El título y la
 * navegación se escapan aquí, así que los generadores pueden pasarlos en
 * crudo.
 */
function renderPage(string $title, string $content, array $config, array $nav = []): string {
    sendSecurityHeaders($config);

    if (!headers_sent()) {
        header('Vary: Cookie');
    }

    $navHtml = '';
    if ($nav !== []) {
        $navHtml = '<div class="nav">';
        foreach ($nav as $href => $label) {
            $navHtml .= '[<a href="' . e(siteUrl((string)$href)) . '">' . e((string)$label) . '</a>] ';
        }
        $navHtml .= '</div>';
    }

    $footer        = footerHtml($config);
    $threadBaseUrl = siteUrl('threads/');
    $faviconUrl    = siteUrl('favicon.ico');
    $styleUrl      = siteUrl('style.css');
    $scriptUrl     = siteUrl('tenma.js');

    return '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>' . e($title) . '</title>
<link rel="icon" type="image/x-icon" href="' . e($faviconUrl) . '">
<link rel="stylesheet" href="' . e($styleUrl) . '">
</head>
<body data-thread-base="' . e($threadBaseUrl) . '">
<div class="container">
' . $content . '
<hr>
' . $navHtml . '
' . $footer . '
</div>
<script src="' . e($scriptUrl) . '"></script>
</body>
</html>';
}

/**
 * Sustituye el campo de nombre por uno de solo lectura cuando la página
 * estática se sirve a un administrador autenticado.
 */
function applyAdminNameField(string $html): string {
    $input = '<input type="text" name="name" size="28" value="Administrador" style="color:#f00;font-weight:bold;" readonly autocomplete="off" placeholder="Nombre">';
    $result = preg_replace('/<input type="text" name="name"[^>]*>/', $input, $html);
    return $result ?? $html;
}

/**
 * Renderiza el bloque de imagen de una publicación.
 */
function renderImageBlock(array $post, bool $large, array $config): string {
    $imageName = (string)($post['image'] ?? '');
    if (!isSafeUploadName($imageName)) {
        return '';
    }

    $diskPath = rtrim($config['uploadfolder'], '/') . '/' . $imageName;
    if (!is_file($diskPath)) {
        return '';
    }

    $url      = siteUrl(rtrim($config['uploadfolder'], '/') . '/' . $imageName);
    $size     = formatBytes((int)($post['filesize'] ?? 0));
    $dims     = @getimagesize($diskPath);
    $dimsText = $dims !== false ? $dims[0] . 'x' . $dims[1] : '';
    $maxCss   = $large ? 'max-width:250px;max-height:250px;' : 'max-width:150px;max-height:150px;';
    $extensionLabel = strtoupper((string)pathinfo($imageName, PATHINFO_EXTENSION));

    $fileInfo = '<div class="fileinfo desktop-fileinfo">Archivo: <a href="' . e($url) . '" target="_blank" rel="noopener noreferrer">'
        . e($imageName) . '</a> (' . e($size) . ($dimsText !== '' ? ', ' . e($dimsText) : '') . ')</div>';

    $image = '<div class="post-media">'
        . '<a class="post-image-link" href="' . e($url) . '" aria-expanded="false">'
        . '<img src="' . e($url) . '" style="' . e($maxCss) . '" alt="Imagen adjunta">'
        . '<span class="mobile-file-info mobile">' . e($size) . ' ' . e($extensionLabel) . '</span>'
        . '</a></div>';

    return $fileInfo . $image;
}

/**
 * Iconos de hilo fijado / bloqueado.
 */
function renderThreadStatusIcons(array $post): string {
    $icons = '';
    if (!empty($post['pinned'])) {
        $icons .= '<img src="' . e(siteUrl('thread-pin.gif')) . '" alt="Hilo fijado" title="Hilo fijado">';
    }
    if (!empty($post['locked'])) {
        $icons .= '<img src="' . e(siteUrl('thread-lock.gif')) . '" alt="Hilo bloqueado" title="Hilo bloqueado">';
    }
    return $icons !== '' ? '<span class="thread-status-icons">' . $icons . '</span>' : '';
}

/**
 * Nombre del autor, con distintivo si la publicación es del administrador.
 */
function renderPostAuthor(array $post, array $config): string {
    $name = (string)($post['name'] ?? '');
    if (empty($post['author_admin'])) {
        return '<span style="color:' . e((string)$config['posternamecolor']) . ';font-weight:bold;">' . e($name) . '</span>';
    }
    return '<span style="color:#f00;font-weight:bold;">' . e($name) . '</span> '
        . '<img src="' . e(siteUrl('admin.gif')) . '" alt="Administrador" title="Administrador" width="16" height="16" style="vertical-align:top;">';
}

/**
 * Enlaces de vuelta hacia las publicaciones que citan a esta.
 */
function renderPostBacklinks(array $post, array $allposts): string {
    $num = (int)$post['num'];
    $backlinks = [];

    foreach ($allposts as $referencingPost) {
        $referencingNum = (int)$referencingPost['num'];
        if ($referencingNum === $num || (int)$referencingPost['deleted'] > 0) {
            continue;
        }
        if (preg_match_all('/>>(\d+)/', (string)$referencingPost['comment'], $matches) === 0) {
            continue;
        }
        if (!in_array((string)$num, $matches[1], true)) {
            continue;
        }
        $referencingThread = (int)$referencingPost['parent'] > 0
            ? (int)$referencingPost['parent']
            : $referencingNum;
        $backlinks[$referencingNum] = '<a class="quotelink post-backlink" href="'
            . e(threadUrl($referencingThread)) . '#p' . $referencingNum
            . '" title="Respuesta que cita esta publicación">&gt;&gt;' . $referencingNum . '</a>';
    }

    return $backlinks !== []
        ? '<span class="post-backlinks"> ' . implode(' ', $backlinks) . '</span>'
        : '';
}

/**
 * Cabecera de publicación para móvil.
 */
function renderMobilePostInfo(array $post, array $allposts, int $threadnum, bool $isOp, array $config, bool $showCheckbox = true, bool $adminView = false): string {
    $num         = (int)$post['num'];
    $name        = renderPostAuthor($post, $config);
    $subject     = (string)($post['subject'] ?? '');
    $subjectHtml = $isOp && $subject !== '' ? '<span class="mobile-subject">' . e($subject) . '</span>' : '';
    $statusIcons = $isOp ? renderThreadStatusIcons($post) : '';
    $threadUrl   = threadUrl($threadnum);
    $postMenu    = $adminView ? '' : renderPostMenu($post, $threadnum, $config);
    $lockedPost  = $isOp && !empty($post['locked']);

    $postNumber = $adminView || $lockedPost
        ? 'No.' . $num
        : '<a href="' . e($threadUrl) . '#p' . $num . '">No.</a>'
            . '<a href="' . e($threadUrl) . '#q' . $num . '" data-quote="' . $num . '">' . $num . '</a>';

    $backlinks = renderPostBacklinks($post, $allposts);
    $checkbox  = $showCheckbox
        ? '<input class="post-checkbox" type="checkbox" name="delete_posts[]" value="' . $num . '" aria-label="Seleccionar publicación">'
        : '';

    return '<div class="mobile mobile-post-info">'
        . $checkbox
        . $postMenu
        . '<span class="name-block">' . $name . ' ' . $subjectHtml . '</span>'
        . '<span class="mobile-date">' . e((string)$post['time']) . '<span class="mobile-number">'
        . $postNumber
        . $backlinks
        . $statusIcons
        . '</span></span></div>';
}

/**
 * Menú de acciones de una publicación (reportar, ocultar, buscar imagen).
 */
function renderPostMenu(array $post, int $threadnum, array $config): string {
    $num = (int)$post['num'];
    $reportUrl = siteUrl($config['tenmafile']) . '?mode=report&num=' . $num;

    $reportLink = !empty($post['deleted']) || !empty($post['pinned'])
        || !empty($post['locked']) || !empty($post['author_admin'])
        ? ''
        : '<a href="' . e($reportUrl) . '">Reportar</a>';

    $menu = '<details class="post-menu"><summary title="Más acciones"></summary><div class="post-menu-content">'
        . $reportLink
        . '<button type="button" data-hide-post="' . $num . '">Ocultar</button>';

    if (!empty($post['image']) && isSafeUploadName((string)$post['image'])
        && is_file(rtrim($config['uploadfolder'], '/') . '/' . (string)$post['image'])) {
        $imageUrl = siteUrl(rtrim($config['uploadfolder'], '/') . '/' . (string)$post['image']);
        $menu .= '<div class="post-menu-label">Buscar imagen »<div class="post-menu-submenu">'
            . '<a href="https://www.google.com/searchbyimage?image_url=' . rawurlencode($imageUrl) . '" target="_blank" rel="noopener noreferrer">Google</a>'
            . '<a href="https://yandex.com/images/search?rpt=imageview&url=' . rawurlencode($imageUrl) . '" target="_blank" rel="noopener noreferrer">Yandex</a>'
            . '<a href="https://saucenao.com/search.php?url=' . rawurlencode($imageUrl) . '" target="_blank" rel="noopener noreferrer">SauceNAO</a>'
            . '</div></div>';
    }

    return ' ' . $menu . '</div></details>';
}

/**
 * Renderiza el mensaje inicial (OP) de un hilo.
 */
function renderOP(array $post, array $allposts, bool $isthread, bool $summarize, array $config, bool $showCheckbox = true, bool $adminView = false): string {
    $num      = (int)$post['num'];
    $postMenu = $adminView ? '' : renderPostMenu($post, $num, $config);

    if ((int)$post['deleted'] > 0) {
        return '<div id="p' . $num . '" class="op-post" data-post-id="' . $num . '" data-thread-id="' . $num . '" style="background-color:' . e((string)$config['postbackground']) . ';">'
            . '<i>' . e((string)$config['deletionphrase']) . '</i>'
            . '<span class="postactions">' . $postMenu . '</span>'
            . '</div>';
    }

    $name        = renderPostAuthor($post, $config);
    $subject     = (string)($post['subject'] ?? '');
    $subjectHtml = $subject !== '' ? '<span class="subject">' . e($subject) . '</span> ' : '';
    $statusIcons = renderThreadStatusIcons($post);

    $comment = (string)$post['comment'];
    if ($summarize && mb_strlen($comment) > 150) {
        $comment = mb_substr($comment, 0, 150) . '...';
    }
    $comment = formatComment($comment, $allposts);

    $imageHtml = renderImageBlock($post, true, $config);
    $mobileInfo = renderMobilePostInfo($post, $allposts, $num, true, $config, $showCheckbox, $adminView);

    $replyLink = !$isthread && empty($post['locked'])
        ? ' [<a href="' . e(threadUrl($num)) . '">Responder</a>]'
        : '';

    $replyCount = count(array_filter($allposts, fn(array $p): bool => (int)$p['parent'] === $num));
    $imageCount = count(array_filter($allposts, fn(array $p): bool => (int)$p['parent'] === $num && !empty($p['image'])));

    $mobileLink = !$isthread
        ? '<div class="mobile mobile-post-link"><span class="info">' . $replyCount . ' respuestas / '
            . $imageCount . ' imágenes</span><a class="button" href="' . e(threadUrl($num)) . '">Ver hilo</a></div>'
        : '';

    $checkbox = $showCheckbox
        ? ' <input class="post-checkbox" type="checkbox" name="delete_posts[]" value="' . $num . '" aria-label="Seleccionar publicación">'
        : '';

    $postNumber = $adminView || !empty($post['locked'])
        ? 'No.' . $num
        : '<span class="postnum" role="button" tabindex="0" data-quote="' . $num . '" title="Responder citando esta publicación">No.' . $num . '</span>';

    $backlinks = renderPostBacklinks($post, $allposts);

    return '<div id="p' . $num . '" class="op-post" data-post-id="' . $num . '" data-thread-id="' . $num . '">'
        . $mobileInfo
        . $imageHtml
        . '<div class="postheader desktop">'
        . $checkbox
        . $subjectHtml
        . $name . ' '
        . '<span>' . e((string)$post['time']) . '</span> '
        . $postNumber
        . $statusIcons
        . '<span class="postactions">' . $replyLink . $postMenu . '</span>'
        . $backlinks
        . '</div>'
        . '<div class="post-content mobile-post-content">' . $comment . '</div>'
        . $mobileLink
        . '</div>';
}

/**
 * Renderiza una respuesta dentro de un hilo.
 */
function renderReply(array $post, array $allposts, bool $summarize, array $config, bool $showCheckbox = true, bool $adminView = false): string {
    $num      = (int)$post['num'];
    $postMenu = $adminView ? '' : renderPostMenu($post, (int)$post['parent'], $config);
    $name     = renderPostAuthor($post, $config);

    if ((int)$post['deleted'] > 0) {
        $comment = '<i>' . e((string)$config['deletionphrase']) . '</i>';
    } else {
        $comment = (string)$post['comment'];
        if ($summarize && mb_strlen($comment) > 150) {
            $comment = mb_substr($comment, 0, 150) . '...';
        }
        $comment = formatComment($comment, $allposts);
    }

    $imageHtml  = renderImageBlock($post, false, $config);
    $mobileInfo = renderMobilePostInfo($post, $allposts, (int)$post['parent'], false, $config, $showCheckbox, $adminView);

    $checkbox = $showCheckbox
        ? ' <input class="post-checkbox" type="checkbox" name="delete_posts[]" value="' . $num . '" aria-label="Seleccionar publicación">'
        : '';

    $postNumber = $adminView
        ? 'No.' . $num
        : '<span class="postnum" role="button" tabindex="0" data-quote="' . $num . '" title="Responder citando esta publicación">No.' . $num . '</span>';

    $backlinks = renderPostBacklinks($post, $allposts);

    return '<div id="p' . $num . '" class="reply-post" data-post-id="' . $num . '" data-thread-id="' . (int)$post['parent'] . '">'
        . $mobileInfo
        . $imageHtml
        . '<div class="postheader desktop">'
        . $checkbox
        . $name . ' '
        . '<span>' . e((string)$post['time']) . '</span> '
        . $postNumber
        . '<span class="postactions">' . $postMenu . '</span>'
        . $backlinks
        . '</div>'
        . '<div class="post-content mobile-post-content">' . $comment . '<br clear="all"></div>'
        . '</div>';
}

/* =============================================================================
 * 11. PLANTILLA HTML, ESTILOS Y SCRIPT
 * ========================================================================== */

/**
 * Genera la hoja de estilos del tablón a partir de los colores configurados.
 */
function generateStyles(array $config): string {
    return <<<CSS
    /* ===== Variables de color y tipografía (configurables desde \$config) ===== */
    :root {
        --font-family: {$config['fonts']};
        --text-color: {$config['textcolor']};
        --link-color: {$config['linkcolor']};
        --link-hover-color: {$config['linkhover']};
        --gradient-color: {$config['gradient']};
        --background-color: {$config['background']};
        --border-color: {$config['border']};
        --section-color: {$config['section']};
        --form-side-color: {$config['formsidecolor']};
        --post-background: {$config['postbackground']};
        --poster-name-color: {$config['posternamecolor']};
        --post-subject-color: {$config['postsubjectcolor']};
        --error-text-color: {$config['errortextcolor']};
        --menu-background: {$config['menubackground']};
        --menu-border: {$config['menuborder']};
        --menu-hover-background: {$config['menuhoverbackground']};
        --placeholder-background: {$config['placeholderbackground']};
        --report-card-background: {$config['reportcardbackground']};
        --report-card-border: {$config['reportcardborder']};
        --muted-text-color: {$config['mutedtextcolor']};
        --help-border-color: {$config['helpbordercolor']};
        --help-desc-color: {$config['helpdesccolor']};
        --mobile-link-background: {$config['mobilelinkbackground']};
        --mobile-button-border: {$config['mobilebuttonbordercolor']};
        --post-border: {$config['postborder']};
    }

    /* ===== Base y reset ===== */
    html { font-size: clamp(1rem, calc(.9375rem + .25vw), 1rem) }
    * { margin: 0; padding: 0; box-sizing: border-box; overflow-wrap: break-word; word-wrap: break-word }
    body { font-family: var(--font-family); color: var(--text-color); padding: 10px; background: linear-gradient(to bottom, var(--gradient-color) 0, var(--background-color) 190px) no-repeat; background-color: var(--background-color); overflow-x: hidden }
    button,
    input,
    select,
    textarea { font-size: 1rem }
    button { padding: 0 .3em }
    hr { border: none; opacity: .3; border-top: 1px solid var(--text-color) }
    ul { list-style-type: none; margin: 1em; padding: 0 }
    .error-message { color: var(--error-text-color); font-weight: bold }
    .mobile { display: none }

    /* ===== Layout general ===== */
    .container { width: 100%; max-width: 800px; margin: 0 auto}
    .board-column { width: 100%; }
    .nav { text-align: center; margin: .3em 0 }

    /* ===== Enlaces ===== */
    a { text-decoration: none; color: var(--link-color) }
    a:hover { color: var(--link-hover-color) }
    .postnum { cursor: pointer; color: var(--link-color) }
    .postnum:hover { text-decoration: underline; color: var(--link-hover-color) }

    /* ===== Formularios ===== */
    input[type=text],
    input[type=password],
    textarea { font-family:sans-serif; padding:.2em; border: 1px solid var(--border-color) }
    .post-form ::placeholder { opacity: 0 }
    .post-form-toggle { padding: 0; border: 0; background: none; color: #0000EE; font: inherit; font-weight: bold; cursor: pointer }
    .post-form-toggle:hover { color: var(--link-hover-color) }
    .comment-format-toolbar { display: flex; flex-wrap: wrap; gap: 1px; margin-bottom: .5px }
    .comment-format-button { padding: .1em .35em; cursor: pointer; font-size: .9em }
    .comment-format-button:hover { color: var(--link-hover-color) }

    /* ===== Publicaciones ===== */
    .fileinfo  { font-size: .92rem; margin-bottom: 5px }
    .quotelink { font-size: .92rem; }
    .post-media { float: left; margin: 0 15px 5px 0 }
    .post-media img { display: block; height: auto }
    .post-image-link img { cursor: zoom-in }
    .post-image-link img.is-expanded { max-width: 90vw !important; max-height: 90vh !important; cursor: zoom-out }
    .postheader { font-size: .92rem; display: contents }
    .postheader .subject { color: var(--post-subject-color); font-weight: bold }
    .postactions { font-size: .92rem }
    .thread-status-icons { display: inline-flex; align-items: center; gap: 2px; margin:0 5px; vertical-align: top }
    .thread-status-icons img { width: 16px; height: 16px; vertical-align: middle }
    .post-checkbox { margin: 0 5px 0 0; vertical-align: middle }
    .op-post { width: 100%; padding: 5px 0; overflow: visible }
    .reply-post { display: flow-root; padding: 5px; margin: 0 0 4px 0; border: 1px solid var(--post-border); background-color: var(--post-background); overflow: visible; max-width: fit-content; }
    .post-highlight { animation: post-highlight 3s ease-in-out; }
    @keyframes post-highlight {
        0% { background-color: var(--post-background); }
        50% { background-color: #F0C0B0; }
        100% { background-color: var(--post-background); }
    }
    .index-image-gallery { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); justify-items: center; gap: 16px; padding: 10px; }
    .index-image-card { display: flex; flex-direction: column; align-items: center; justify-self: center; width: fit-content; max-width: 100%; min-width: 0; text-align: center; color: var(--text-color); }
    .index-image-card img { display: block; max-width: 100%; max-height: 190px; width: auto; height: auto; border: 1px solid #800; object-fit: contain; }
    .index-image-caption { display: -webkit-box; max-width: 190px; margin-top: 5px; overflow: hidden; color: var(--text-color); font-size: .9rem; line-height: 1.3; -webkit-box-orient: vertical; -webkit-line-clamp: 5; }
    .index-image-caption b { color: var(--post-subject-color); }
    .post-content { margin-top: 4px }
    .omitted { padding: 0 0 5px; font-size: .875rem }
    .hidden-post-placeholder { display: inline-block; padding: 4px 10px; margin: 4px 0; border: 1px dashed var(--border-color); background: var(--placeholder-background); color: var(--text-color); }
    .delete { clear: both; text-align: right; margin: 5px 0; }
    .delete button { padding: .15em .3em; }

    /* ===== Cabecera de publicación en móvil ===== */
    .mobile-post-info { line-height: 1.25; }
    .mobile-post-info .post-menu { float: left; margin: 0 5px 0 20px; }
    .mobile-post-info .post-menu summary::before { content: '...'; font-weight: bold; }
    .mobile-post-info .post-menu[open] summary::before { content: '...'; }
    .mobile-post-info .name-block { display: inline-block; vertical-align: top; }
    .mobile-post-info .mobile-subject { color: var(--post-subject-color); font-weight: bold; }
    .mobile-post-info .mobile-date { float: right; text-align: right; }
    .mobile-post-info .mobile-number a { white-space: nowrap; }
    .mobile-file-info { display: none; }
    .mobile-post-link { display: none; }

    /* ===== Menú de acciones de publicación ===== */
    .post-menu { display: inline-block; position: relative; margin-left: 4px; font-size: .875rem }
    .post-menu summary { display: inline-block; width: 16px; cursor: pointer; color: var(--text-color); list-style: none; font-size: .9em; }
    .post-menu summary::-webkit-details-marker { display: none }
    .post-menu summary::before { content: '►'; }
    .post-menu[open] summary::before { content: '▼'; }
    .post-menu summary:hover { color: var(--link-hover-color); }
    .post-menu-content { position: absolute; z-index: 10; top: 1.35em; left: 0; min-width: 115px; padding: 0; background: var(--menu-background); border: 1px solid var(--menu-border); text-align: left }
    .post-menu-content a,
    .post-menu-content button,
    .post-menu-label { display: block; width: 100%; padding: 3px 6px; border: 0; background: transparent; color: var(--text-color); font: inherit; text-align: left; white-space: nowrap; cursor: pointer }
    .post-menu-content a:hover,
    .post-menu-content button:hover,
    .post-menu-label:hover { background: var(--menu-hover-background); color: var(--link-hover-color); }
    .post-menu-submenu { display: none; position: absolute; top: 44px; left: 100%; min-width: 87px; padding: 0; background: var(--menu-background); border: 1px solid var(--menu-border); }
    .post-menu-submenu.flip { left: auto; right: 100%; }
    .post-menu-label:hover .post-menu-submenu,
    .post-menu-label:focus-within .post-menu-submenu { display: block; }
    .post-menu-submenu a { padding: 3px 6px; }

    /* ===== Reportes (panel de administración) ===== */
    .report-grid { display: flex; flex-wrap: wrap; gap: 10px; padding-bottom: 10px }
    .report-grid > form { flex: 1 1 100%; min-width: 0; margin: 0 }
    .report-card { background: var(--report-card-background); border: 1px solid var(--report-card-border); padding: 10px 14px; min-width: 260px; flex: 1 1 260px }
    .reported-post { flex: 1 1 100%; min-width: 0 }
    .reported-publication { margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--report-card-border) }
    .report-card .rc-num  { font-weight: bold; font-size: .97rem; color: var(--post-subject-color) }
    .report-card .rc-reason { margin: 4px 0 8px; }
    .report-card .rc-time { font-size: .8125rem; color: var(--muted-text-color); margin-bottom: 8px }
    .report-card .rc-actions { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }
    .report-card .rc-actions button { cursor: pointer }

    /* ===== Página de ayuda ===== */
    .help-list { overflow: hidden }
    .help-row { display: flex; align-items: center; padding: 8px 12px; border-bottom: 1px solid var(--help-border-color); gap: 14px }
    .help-row:last-child { border-bottom: none }
    .help-syntax { font-family: monospace; flex: 0 0 110px }
    .help-preview { flex: 0 0 100px; font-size: .97rem }
    .help-desc { flex: 1 1 auto; font-size: .9rem; color: var(--help-desc-color) }
    .rules-intro { margin: 10px auto; text-align: center }
    .rules-list { padding: 0 10px; list-style: none; }
    .rules-list li { margin-bottom: 10px; }
    .rules-list h2 { font-size: 1rem; }
    .rules-list p { font-size: .92rem; line-height: 1.55 }

    /* ===== Panel de administración ===== */
    .admin-page { width: 100% }
    .admin-heading { margin: 8px 0 5px; text-align: center }
    .admin-heading h1 { margin: 0 0 5px }
    .admin-warning { margin: 10px 0; text-align: center }
    .admin-tab-nav { display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; margin: 10px 0 16px }
    .admin-tab-nav form { display: flex; margin: 0 }
    .admin-tab-nav button[aria-current="page"] { font-weight: bold }
    .admin-content { width: 100% }
    .admin-section { margin: 10px 0 16px }
    .admin-section-title { margin: 8px 0; text-align: center }
    .admin-button-group { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin: 8px 0 }
    .admin-toolbar { justify-content: flex-start }
    .admin-toolbar form { margin: 0 }
    .admin-settings-block { margin: 10px 0 }
    .admin-settings-block h3 { margin: 8px 0; }
    .admin-notice-toggle { display: block; margin: 8px 0; }
    .admin-notice-input { display: block; width: calc(100% - 16px); max-width: 600px; resize: vertical }
    .admin-settings-submit { justify-content: flex-start }
    .admin-status { margin-top: 12px; }
    .admin-status p { margin: 10px 0 }
    .admin-post-entry { padding: 5px 0 }
    .admin-post-controls { clear: both; display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 5px 0; font-size: .9rem }
    .admin-post-hash { color: var(--muted-text-color); overflow-wrap: anywhere }
    .admin-post-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 5px }
    .admin-post-actions .thread-admin-action { display: inline-flex; align-items: center; gap: 3px; white-space: nowrap }

    /* ===== Responsive (móvil) ===== */
    @media (max-width: 600px) {
        body { padding: 5px }
        .container { max-width: none; margin: 0 }
        .board-column { width: 100%; }
        .postheader { display: block; line-height: 1.35 }
        .desktop { display: none !important; }
        .mobile { display: inline; }
        .mobile-post-info { position: relative; }
        .mobile-post-info .post-checkbox { position: absolute; top: 7px; left: 6px; width: 16px; height: 16px; margin: 0; }
        .post-checkbox { margin-left: 0 }
        .op-post { padding: 0; margin: 0 0 5px; background: var(--post-background); border: 1px solid var(--post-border); }
        .reply-post { display: block; width: fit-content; max-width: 100%; padding: 0; margin: 0 0 6px; background: var(--post-background); }
        .mobile-post-info { display: block; padding: 7px 6px; background: var(--post-background); border-bottom: 1px solid var(--post-border); }
        .mobile-post-info .mobile-date { display: flex }
        .mobile-post-info .mobile-number { display: block; padding-left: 5px; }
        .mobile-file-info { display: block; clear: both; padding: 7px 6px 4px; color: var(--muted-text-color); }
        .post-media { float: none; max-width: 100%; margin: 0; padding: 7px 6px 0; }
        .post-media img { max-width: 100% !important; max-height: 250px; }
        .post-media .desktop-fileinfo { display: none; }
        .post-media .mobile-file-info { text-align: center; }
        .mobile-post-link { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 6px; border-top: 1px solid var(--post-border); background: var(--mobile-link-background); }
        .mobile-post-link .button { color: var(--text-color); padding: 4px 9px; border: 1px solid var(--mobile-button-border); border-radius: 3px; background: var(--menu-background); }
        .mobile-post-content { display: block; padding: 7px 6px; }
        .fileinfo { display: none !important }
        .post-content { margin-top: 3px; line-height: 1.25 }
        .post-menu-content { position: absolute; top: 1.35em; left: 0; right: auto }
        .post-menu-submenu { position: static; margin-left: 8px; box-shadow: none }
        .post-menu-submenu.flip { left: auto; right: auto }
        table { max-width: 100%; overflow: hidden }
        input[type=text], input[type=password], textarea { max-width: 100%; }
        .post-form { width: 100%; }
        .post-form ::placeholder { opacity: 1; }
        .post-form-table { display: block; width: 100%; max-width: 480px; margin: 0 auto !important; border-spacing: 0 !important; }
        .post-form-table tbody, .post-form-table tr { display: grid; width: 100%; }
        .post-form-table tr { margin-bottom: 3px; }
        .post-form-table td { display: block; width: 100%; }
        .post-form-table td:first-child { display: none; }
        .post-form-table td + td { padding: 0; }
        .post-form-table tr:first-child td + td { display: flex; align-items: center; gap: 6px; }
        .post-form-table tr:first-child input[type=text] { flex: 1; width: auto; min-width: 0; }
        .post-form-table input[type=text], .post-form-table textarea { display: block; width: 100%; max-width: none; }
        .post-form-table textarea { min-height: 100px; resize: vertical; }
        .post-form-table input[type=submit] { display: block; flex: none; margin-top: 0; }
        .post-form-table input[type=file] { display: block; width: 100%; max-width: 100%; }
        .post-form-table small { display: block; margin-top: 4px; }
        .post-form p { padding: 0 8px; line-height: 1.4; }
        .admin-post-controls { align-items: flex-start; flex-direction: column }
        .admin-post-actions { width: 100% }
    }
    CSS;
}

/**
 * Genera el script cliente compartido por todas las páginas. No hay manejadores
 * en línea ni bloques <script> incrustados: así la CSP puede usar
 * script-src 'self' sin 'unsafe-inline'.
 */
function generateScript(): string {
    return <<<'JS'
    (function () {
        'use strict';

        var threadBaseUrl = document.body ? (document.body.getAttribute('data-thread-base') || '') : '';

        function eachNode(selector, callback) {
            var nodes = document.querySelectorAll(selector);
            for (var i = 0; i < nodes.length; i++) {
                callback(nodes[i]);
            }
        }

        function quotePost(num) {
            var allTextareas = document.querySelectorAll('textarea[name="com"]');

            // Sin formulario en la página: ir al hilo para responder.
            if (allTextareas.length === 0) {
                window.location.href = threadBaseUrl + num + '/?quote=' + num;
                return;
            }

            // En el tablón solo existe el formulario de creación: ir al hilo.
            if (allTextareas.length === 1) {
                var form = allTextareas[0].closest('form');
                var isCreateForm = form && form.querySelector('input[name="subject"]');
                if (isCreateForm) {
                    window.location.href = threadBaseUrl + num + '/?quote=' + num;
                    return;
                }
            }

            var textarea = allTextareas[allTextareas.length - 1];
            textarea.focus();
            textarea.value += '>>' + num + '\n';
            textarea.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }

        // Mostrar/ocultar el formulario de creación.
        document.addEventListener('click', function (event) {
            var toggle = event.target.closest && event.target.closest('.post-form-toggle');
            if (!toggle) {
                return;
            }
            var formContainer = document.getElementById(toggle.getAttribute('aria-controls'));
            if (!formContainer) {
                return;
            }
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isExpanded));
            formContainer.hidden = isExpanded;
            var heading = toggle.closest('h3');
            if (heading) {
                heading.hidden = !isExpanded;
            }
        });

        // Botones de formato del comentario.
        document.addEventListener('click', function (event) {
            var button = event.target.closest && event.target.closest('.comment-format-button');
            if (!button) {
                return;
            }
            var cell = button.closest('td');
            var textarea = cell ? cell.querySelector('textarea[name="com"]') : null;
            if (!textarea) {
                return;
            }

            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var selectedText = textarea.value.slice(start, end) || 'texto';
            var open = button.getAttribute('data-format-open');
            var close = button.getAttribute('data-format-close');
            textarea.value = textarea.value.slice(0, start) + open + selectedText + close + textarea.value.slice(end);
            textarea.focus();
            textarea.setSelectionRange(start + open.length, start + open.length + selectedText.length);
        });

        // Citar una publicación (clic o teclado).
        function handleQuoteElement(element) {
            var num = element.getAttribute('data-quote');
            if (num) {
                quotePost(num);
            }
        }

        document.addEventListener('click', function (event) {
            if (!event.target.closest) {
                return;
            }
            var quoteElement = event.target.closest('[data-quote]');
            if (!quoteElement) {
                return;
            }
            event.preventDefault();
            handleQuoteElement(quoteElement);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }
            if (!event.target.closest) {
                return;
            }
            var quoteElement = event.target.closest('[data-quote]');
            if (!quoteElement) {
                return;
            }
            event.preventDefault();
            handleQuoteElement(quoteElement);
        });

        // Ocultar publicaciones (solo en este navegador).
        function tenmaHiddenPosts() {
            try {
                var hidden = JSON.parse(localStorage.getItem('tenma-hidden-posts') || '[]');
                return Array.isArray(hidden) ? hidden : [];
            } catch (error) {
                return [];
            }
        }

        function createHiddenPlaceholder(postId) {
            var placeholder = document.createElement('div');
            placeholder.className = 'hidden-post-placeholder';
            placeholder.setAttribute('data-hidden-post-id', postId);
            placeholder.appendChild(document.createTextNode('Publicación oculta - '));

            var link = document.createElement('a');
            link.href = '#';
            link.setAttribute('data-show-post', postId);
            link.textContent = 'Mostrar';
            placeholder.appendChild(link);

            return placeholder;
        }

        function tenmaApplyHiddenPosts() {
            var hidden = tenmaHiddenPosts();
            eachNode('[data-post-id]', function (node) {
                var postId = node.getAttribute('data-post-id');
                var threadId = node.getAttribute('data-thread-id');
                var isPostHidden = hidden.indexOf(postId) !== -1;
                var isThreadHidden = threadId !== postId && hidden.indexOf(threadId) !== -1;
                var isHidden = isPostHidden || isThreadHidden;
                var placeholder = node.previousElementSibling;

                if (isHidden) {
                    node.style.display = 'none';
                    if (isPostHidden && !isThreadHidden) {
                        if (!placeholder || placeholder.getAttribute('data-hidden-post-id') !== postId) {
                            node.parentNode.insertBefore(createHiddenPlaceholder(postId), node);
                        }
                    } else if (placeholder && placeholder.getAttribute('data-hidden-post-id') === postId) {
                        placeholder.remove();
                    }
                } else {
                    node.style.display = '';
                    if (placeholder && placeholder.getAttribute('data-hidden-post-id') === postId) {
                        placeholder.remove();
                    }
                }
            });
        }

        function tenmaHidePost(postId) {
            var hidden = tenmaHiddenPosts();
            postId = String(postId);
            if (hidden.indexOf(postId) === -1) {
                hidden.push(postId);
            }
            try {
                localStorage.setItem('tenma-hidden-posts', JSON.stringify(hidden));
            } catch (error) {
                return;
            }
            window.location.reload();
        }

        function tenmaShowHiddenPost(postId) {
            var hidden = tenmaHiddenPosts().filter(function (id) {
                return id !== String(postId);
            });
            try {
                localStorage.setItem('tenma-hidden-posts', JSON.stringify(hidden));
            } catch (error) {
                // Sin almacenamiento local: solo se vuelve a pintar la lista.
            }
            tenmaApplyHiddenPosts();
        }

        document.addEventListener('click', function (event) {
            if (!event.target.closest) {
                return;
            }

            var hideElement = event.target.closest('[data-hide-post]');
            if (hideElement) {
                tenmaHidePost(hideElement.getAttribute('data-hide-post'));
                return;
            }

            var showElement = event.target.closest('[data-show-post]');
            if (showElement) {
                event.preventDefault();
                tenmaShowHiddenPost(showElement.getAttribute('data-show-post'));
            }
        });

        // Confirmación para acciones destructivas (atributo data-confirm).
        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!form || !form.getAttribute) {
                return;
            }
            var message = form.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });

        // Resaltar la publicación apuntada por el hash (#p123).
        function tenmaHighlightPostFromHash() {
            var match = window.location.hash.match(/^#p(\d+)$/);
            if (!match) {
                return;
            }
            var post = document.getElementById('p' + match[1]);
            if (!post) {
                return;
            }
            post.classList.remove('post-highlight');
            void post.offsetWidth;
            post.classList.add('post-highlight');
        }

        window.addEventListener('load', tenmaHighlightPostFromHash);
        window.addEventListener('hashchange', tenmaHighlightPostFromHash);

        document.addEventListener('click', function (event) {
            var link = event.target.closest && event.target.closest('a.quotelink');
            if (link && link.pathname === window.location.pathname && link.hash === window.location.hash) {
                tenmaHighlightPostFromHash();
            }
        });

        // Ampliar/cerrar la vista previa de una imagen.
        document.addEventListener('click', function (event) {
            var image = event.target.closest && event.target.closest('.post-image-link img');
            if (!image) {
                return;
            }
            event.preventDefault();
            var expanded = image.classList.toggle('is-expanded');
            image.closest('.post-image-link').setAttribute('aria-expanded', String(expanded));
        });

        // Añadir la cita indicada en la URL (?quote=123).
        window.addEventListener('load', function () {
            var match = window.location.href.match(/[?&]quote=(\d+)/);
            if (!match) {
                return;
            }
            var quoteNum = match[1];
            eachNode('textarea[name="com"]', function (textarea) {
                if (textarea.value.indexOf('>>' + quoteNum) === -1) {
                    textarea.value = '>>' + quoteNum + '\n' + textarea.value;
                    textarea.focus();
                }
            });
        });

        // Actualizar los tiempos relativos sin recargar.
        function tenmaUpdateTimes() {
            var now = Math.floor(Date.now() / 1000);
            eachNode('[data-ts]', function (node) {
                var timestamp = parseInt(node.getAttribute('data-ts'), 10);
                if (!timestamp) {
                    return;
                }
                var diff = Math.max(0, now - timestamp);
                var text;
                if (diff < 60) {
                    text = 'Ahora';
                } else if (diff < 3600) {
                    text = 'Hace ' + Math.floor(diff / 60) + 'min';
                } else if (diff < 86400) {
                    text = 'Hace ' + Math.floor(diff / 3600) + 'h';
                } else if (diff < 2592000) {
                    text = 'Hace ' + Math.floor(diff / 86400) + 'd';
                } else if (diff < 31536000) {
                    text = 'Hace ' + Math.floor(diff / 2592000) + 'mes';
                } else {
                    text = 'Hace ' + Math.floor(diff / 31536000) + 'a';
                }
                node.textContent = text;
            });
        }

        // Evitar que el submenú de búsqueda de imagen se salga de la pantalla.
        eachNode('.post-menu-label', function (label) {
            label.addEventListener('mouseenter', function () {
                var submenu = label.querySelector('.post-menu-submenu');
                if (!submenu) {
                    return;
                }
                submenu.classList.remove('flip');
                window.setTimeout(function () {
                    var labelBox = label.getBoundingClientRect();
                    var submenuWidth = submenu.getBoundingClientRect().width;
                    if (labelBox.right + submenuWidth > window.innerWidth) {
                        submenu.classList.add('flip');
                    }
                }, 0);
            });
        });

        tenmaApplyHiddenPosts();
        tenmaUpdateTimes();
        window.setInterval(tenmaUpdateTimes, 30000);
    })();
    JS;
}

/* =============================================================================
 * 12. GENERADORES DE PÁGINAS
 * ========================================================================== */

/**
 * Página de error 404 para hilos o recursos que ya no existen.
 */
function generateErrorPage(array $config): string {
    $title = ($config['title'] ?: 'Tenma') . ' - No encontrado';
    $content = '<div style="background:#fff;border:1px solid #800">
        <h3 style="margin:0 0 10px;padding:3px 10px;background:#FCA">Error 404</h3>
        <div style="text-align:center;margin:0 0 10px">
            <p>El hilo o la imagen que buscas ya no existe.<br>Puede que haya sido eliminado por el administrador o eliminado automáticamente por antigüedad.</p>
            <br>
            [<a href="' . e(siteUrl('index.html')) . '">Volver</a>]
        </div>
    </div>';
    return renderPage($title, $content, $config);
}

/**
 * Genera la página de reglas del sitio.
 */
function generateRules(array $config): string {
    $title = ($config['title'] ?: 'Tenma') . ' - Reglas';
    $items = '';
    foreach ($config['rules'] as $rule) {
        $items .= '<li><h2>' . e((string)($rule['title'] ?? '')) . '</h2><p>'
            . e((string)($rule['desc'] ?? '')) . '</p></li>';
    }

    $content = '<center><h1 style="margin-bottom:5px;">' . e($title) . '</h1></center><hr>
<div class="nav">[<a href="' . e(siteUrl('index.html')) . '">Inicio</a>]</div><hr>
<div style="margin:1em 0;border:1px solid #800;background:#fff;">
<h2 style="margin:0;padding:8px 12px;background:' . e((string)$config['section']) . ';font-size:1.15rem;">Reglas de convivencia</h2>
<p class="rules-intro">Para mantener un espacio útil y seguro, sigue estas reglas al publicar y participar.<br>El contenido puede ser retirado si las incumple.</p>
<ol class="rules-list">' . $items . '</ol>
</div>';

    return renderPage($title, $content, $config);
}

/**
 * Genera la página de ayuda con ejemplos de formato.
 */
function generateHelp(array $config): string {
    $title = ($config['title'] ?: 'Tenma') . ' - Ayuda';
    $rows  = '';

    foreach ($config['helps'] as $help) {
        // 'html' y 'desc' son contenido de confianza definido en $config;
        // el resto de campos se escapa.
        $rows .= '<div class="help-row">
<span class="help-syntax">' . e((string)$help['syntax']) . '</span>
<span class="help-preview">' . (string)$help['html'] . '</span>
<span class="help-desc">' . (string)$help['desc'] . '</span>
</div>';
    }

    $content = '<center><h1 style="margin-bottom:5px;">' . e($title) . '</h1></center><hr>
<div class="nav">[<a href="' . e(siteUrl('index.html')) . '">Inicio</a>]</div><hr>
<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="padding:3px 10px;background:' . e((string)$config['section']) . ';">Formatos disponibles</h3>
<div class="help-list">' . $rows . '</div>
</div>';

    return renderPage($title, $content, $config);
}

/**
 * Genera la página principal con actividad reciente, galería y estadísticas.
 */
function generateIndex(array $allposts, array $config): string {
    $title = $config['title'] ?: 'Tenma';

    $visiblePosts = array_values(array_filter($allposts, fn(array $post): bool => (int)$post['deleted'] === 0));
    $recent = array_reverse(array_slice($visiblePosts, -10));

    $activityItems = '';
    foreach ($recent as $post) {
        $thread  = (int)$post['parent'] > 0 ? (int)$post['parent'] : (int)$post['num'];
        $comment = (string)$post['comment'];
        $preview = $comment === '' && !empty($post['image'])
            ? '[imagen]'
            : e(mb_substr($comment, 0, 60)) . (mb_strlen($comment) > 60 ? '...' : '');

        $nameColor = !empty($post['author_admin']) ? '#f00' : (string)$config['posternamecolor'];
        $timestamp = (int)$post['now'];

        $activityItems .= '<li><small style="color:#888;float:inline-end;margin-right:1em;" data-ts="' . $timestamp . '">('
            . e(relativeTime($timestamp)) . ')</small><b style="color:' . e($nameColor) . '">'
            . e((string)$post['name']) . '</b>: ' . $preview . ' <a href="' . e(threadUrl($thread))
            . '#p' . (int)$post['num'] . '">&gt;&gt;</a></li>';
    }

    $activity = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . e((string)$config['section']) . ';">Última actividad</h3>
<ul style="margin:0;padding:0 0 10px 10px;">' . $activityItems . '</ul></div>';

    $images = array_slice(array_reverse(array_values(array_filter(
        $allposts,
        fn(array $post): bool => !empty($post['image'])
            && isSafeUploadName((string)$post['image'])
            && (int)$post['deleted'] === 0
            && (int)$post['parent'] === 0
            && is_file(rtrim($config['uploadfolder'], '/') . '/' . (string)$post['image'])
    ))), 0, 8);

    $gallery = '';
    if ($images !== []) {
        $thumbs = '';
        foreach ($images as $image) {
            $thread  = (int)$image['parent'] > 0 ? (int)$image['parent'] : (int)$image['num'];
            $postUrl = threadUrl($thread) . '#p' . (int)$image['num'];

            $label = trim((string)($image['subject'] ?? ''));
            if ($label === '') {
                $label = (string)$image['name'];
            }
            $comment = trim((string)$image['comment']);
            $preview = $comment !== '' ? mb_substr($comment, 0, 120) . (mb_strlen($comment) > 120 ? '...' : '') : '';
            $imageUrl = siteUrl(rtrim($config['uploadfolder'], '/') . '/' . (string)$image['image']);

            $thumbs .= '<a class="index-image-card" href="' . e($postUrl) . '">
<img src="' . e($imageUrl) . '" alt="Imagen de la publicación No.' . (int)$image['num'] . '">
<span class="index-image-caption"><b>' . e($label) . '</b>'
                . ($preview !== '' ? ' ' . e($preview) : '')
                . '</span></a>';
        }

        $gallery = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . e((string)$config['section']) . ';">Imágenes recientes</h3>
<div class="index-image-gallery">' . $thumbs . '</div></div>';
    }

    $totalPosts  = count($allposts);
    $totalImages = count(array_filter($allposts, fn(array $post): bool => !empty($post['image'])));
    $totalSize   = array_sum(array_map(fn(array $post): int => (int)$post['filesize'], $allposts));

    $stats = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . e((string)$config['section']) . ';">Estadísticas</h3>
<div style="font-size:.9rem;padding:0 0 10px 10px;display:flex;flex-wrap:wrap;justify-content:center;gap:15px;text-align:center;">
<span><b>Publicaciones:</b> ' . $totalPosts . '</span>
<span><b>Imágenes:</b> ' . $totalImages . '</span>
<span><b>Contenido:</b> ' . e(formatBytes($totalSize)) . '</span>
</div></div>';

    $content = '<center style="margin-bottom:5px;"><h1>' . e($title) . '</h1></center><hr>
<div class="nav">[<a href="' . e(siteUrl('board.html')) . '">Tablón</a>] [<a href="' . e(siteUrl($config['rulesfile'])) . '">Reglas</a>] [<a href="' . e(siteUrl($config['helpfile'])) . '">Ayuda</a>]</div><hr>
' . $activity . $gallery . $stats . '
<hr><div class="nav" style="float:right;">[<a href="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">Admin</a>]</div>
<br clear="all">';

    return renderPage($title, $content, $config);
}

/**
 * Genera una página del tablón con los hilos recientes y el formulario.
 */
function generateBoard(array $threads, int $pagenumber, int $totalpages, array $allposts, array $config): string {
    $title     = ($config['title'] ?: 'Tenma') . ' - Tablón';
    $subtitle  = (string)$config['subtitle'];
    $notice    = '<hr>';
    $noticeRaw = getSiteSetting($config, 'notice_message');

    if (getSiteSetting($config, 'notice_enabled') === '1' && trim($noticeRaw) !== '') {
        $noticeRaw = str_replace(["\r\n", "\r"], "\n", trim($noticeRaw));
        $notice = '<hr><h2 style="color:#f00;text-align:center;margin:.3em 0;">'
            . e($noticeRaw) . '</h2><hr>';
    }

    $nameField = $config['forcedanonymity']
        ? '<input type="text" name="name" size="28" value="' . e((string)$config['defaultname']) . '" disabled autocomplete="off" placeholder="Nombre">'
        : '<input type="text" name="name" size="28" maxlength="' . (int)$config['namelimit'] . '" autocomplete="off" placeholder="Nombre">';

    $formHtml = '<center>
<h3 style="margin:.5em 0; font-size: 1.5em;">[<button class="post-form-toggle" type="button" aria-controls="new-thread-form" aria-expanded="false">Crear nueva publicación</button>]</h3>
<div id="new-thread-form" hidden>
<br>
<form class="post-form" method="POST" action="' . e(siteUrl($config['tenmafile'])) . '" enctype="multipart/form-data">
<table class="post-form-table" style="margin:0 auto;border-spacing:1px;">
    <tbody>
        <tr>
            <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                <b style="margin:.3em;">Nombre</b></td>
            <td>' . $nameField . ' <input type="submit" value="Publicar" style="padding:.15em .3em"></td>
        </tr>
        <tr>
            <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                <b style="margin:.3em;">Asunto</b>
            </td>
            <td>
                <input type="text" name="subject" size="28" maxlength="' . (int)$config['subjectlimit'] . '" autocomplete="off" placeholder="Asunto" required>
            </td>
        </tr>
        <tr>
            <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                <b style="margin:.3em;">Comentario</b>
            </td>
            <td>
                ' . commentFormatToolbar() . '
                <textarea name="com" cols="48" rows="4" maxlength="' . (int)$config['commentlimit'] . '" style="vertical-align:bottom" autocomplete="off" placeholder="Comentario" required></textarea>
            </td>
        </tr>
        <tr>
            <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                <b style="margin:.3em;">Imagen</b>
            </td>
            <td>
                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" autocomplete="off" required>
            </td>
        </tr>
    </tbody>
</table>
<p style="font-size:.9rem">Puedes leer las <a href="' . e(siteUrl($config['rulesfile'])) . '">reglas</a> y la <a href="' . e(siteUrl($config['helpfile'])) . '">ayuda de formato</a>.</p>
</form><br></div></center>';

    $postHtml = '';
    foreach ($threads as $thread) {
        $op      = $thread['op'];
        $replies = $thread['replies'];

        $postHtml .= renderOP($op, $allposts, false, true, $config);

        $omitted       = 0;
        $omittedImages = 0;
        $displayReplies = $replies;

        if (count($replies) > $config['maxreplieshown']) {
            $omitted        = count($replies) - $config['maxreplieshown'];
            $hiddenReplies  = array_slice($replies, 0, $omitted);
            $omittedImages  = count(array_filter($hiddenReplies, fn(array $reply): bool => !empty($reply['image'])));
            $displayReplies = array_slice($replies, -$config['maxreplieshown']);
        }

        if ($omitted > 0) {
            $imageText = $omittedImages > 0 ? ' y ' . $omittedImages . ' imágenes' : '';
            $postHtml .= '<div class="omitted"><i>' . $omitted . ' respuestas' . $imageText
                . ' omitidas. <a href="' . e(threadUrl((int)$op['num'])) . '">Haz clic para ver</a>.</i></div>';
        }

        foreach ($displayReplies as $reply) {
            $postHtml .= renderReply($reply, $allposts, true, $config);
        }

        $postHtml .= '<hr style="clear:both;">';
    }

    $deleteButton  = '<div class="delete"><button type="submit">Eliminar</button></div>';
    $lastSeparator = '<hr style="clear:both;">';
    if (substr($postHtml, -strlen($lastSeparator)) === $lastSeparator) {
        $postHtml = substr($postHtml, 0, -strlen($lastSeparator)) . $deleteButton . $lastSeparator;
    }

    $pageHtml = paginationHtml($pagenumber, $totalpages, 'board', $config);

    $content = '<div class="board-column"><center style="margin-bottom:5px"><h1>' . e($title) . '</h1><h3>' . e($subtitle) . '</h3></center><hr>
<div class="nav">[<a href="' . e(siteUrl('index.html')) . '">Inicio</a>]</div><hr>
' . $formHtml . $notice . '<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '" data-confirm="¿Deseas eliminar las publicaciones seleccionadas?">'
        . '<input type="hidden" name="return_to" value="' . e(siteUrl('board.html')) . '">'
        . $postHtml . $pageHtml . '</form></div>';

    return renderPage($title, $content, $config);
}

/**
 * Genera la página completa de un hilo con su OP y sus respuestas.
 */
function generateThread(array $thread, array $allposts, array $config): string {
    $op        = $thread['op'];
    $threadnum = (int)$op['num'];
    $title     = ($config['title'] ?: 'Tenma') . ' - Publicación #' . $threadnum;

    $postHtml = renderOP($op, $allposts, true, false, $config);
    foreach ($thread['replies'] as $reply) {
        $postHtml .= renderReply($reply, $allposts, false, $config);
    }

    $nameField = $config['forcedanonymity']
        ? '<input type="text" name="name" size="28" value="' . e((string)$config['defaultname']) . '" disabled autocomplete="off" placeholder="Nombre">'
        : '<input type="text" name="name" size="28" maxlength="' . (int)$config['namelimit'] . '" autocomplete="off" placeholder="Nombre">';

    $replyForm = '<center>
<h3 style="margin:.5em 0">Responder al hilo</h3>
<form class="post-form" method="POST" action="' . e(siteUrl($config['tenmafile'])) . '" enctype="multipart/form-data" style="margin:10px 0;">
<input type="hidden" name="parent" value="' . $threadnum . '">
<table class="post-form-table" style="margin:0 auto;border-spacing:1px;">
    <tbody>
        <tr>
            <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                <b style="margin:.3em;">Nombre</b>
            </td>
            <td>' . $nameField . ' <input type="submit" value="Responder" style="padding:.15em .3em"></td>
        </tr>
            <tr>
                <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                    <b style="margin:.3em;">Comentario</b>
                </td>
            <td>
                ' . commentFormatToolbar() . '
                <textarea name="com" cols="48" rows="4" maxlength="' . (int)$config['commentlimit'] . '" style="vertical-align:bottom" autocomplete="off" placeholder="Comentario"></textarea>
            </td>
        </tr>
        <tr>
            <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;">
                <b style="margin:.3em;">Imagen</b>
            </td>
            <td><input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp" autocomplete="off"> <small>(Máx 3 MB)</small></td>
        </tr>
    </tbody>
</table>
<p style="font-size:.9rem">Puedes leer las <a href="' . e(siteUrl($config['rulesfile'])) . '">reglas</a> y la <a href="' . e(siteUrl($config['helpfile'])) . '">ayuda de formato</a>.</p>
</form>
</center>';

    $replySection = !empty($op['locked'])
        ? '<p style="text-align:center;color:#f00;font-size:1.4rem;font-weight:bold;margin:2em 0;">Hilo cerrado.<br>No se permite responder en este momento.</p>'
        : $replyForm;

    $deleteForm = '<div class="delete"><button type="submit">Eliminar</button></div>';
    $postHtml .= '<hr style="clear:both;">';

    $content = '<div class="board-column"><center><h1 style="margin-bottom:5px;">' . e($title) . '</h1></center>
<hr>
<div class="nav">[<a href="' . e(siteUrl('index.html')) . '">Inicio</a>] [<a href="' . e(siteUrl('board.html')) . '">Tablón</a>]</div>
<hr>
' . $replySection . '<hr>
<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '" data-confirm="¿Deseas eliminar las publicaciones seleccionadas?">'
        . '<input type="hidden" name="return_to" value="' . e(threadUrl($threadnum)) . '">'
        . $postHtml . $deleteForm . '</form></div>';

    return renderPage($title, $content, $config);
}

/**
 * Devuelve el icono ICO del sitio.
 */
function generateFavicon(): string {
    $icon = base64_decode(
        'AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAABILAAASCwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/wAAAAAAAAD/AAAAAAAAAAAAAAAAAAAAAAAAAP8AAAAAAAAA/wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/zPMZv8AAAD/M8xm/wAAAP8AAAAAAAAAAAAAAP8zzGb/AAAA/zPMZv8AAAD/AAAAAAAAAAAAAAAAAAAAAAAAAP8zzGb/M8xm/zPMZv8AAAD/AAAAAAAAAAAAAAD/M8xm/zPMZv8zzGb/AAAA/wAAAAAAAAAAAAAAAAAAAAAAAAD/M8xm/zPMZv8zzGb/AAAA/wAAAAAAAAAAAAAA/zPMZv8zzGb/M8xm/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP8zzGb/M8xm/wAAAP8AAAAAAAAAAAAAAP8zzGb/M8xm/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAA/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAP8AAAD/AAAA/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAA/wAAAP8AAAD/AAAA/wAAAAAAAAAAAAAAAAAAAP8zzGb/M8xm/zPMZv8zzGb/AAAA/wAAAAAAAAAAAAAA/zPMZv8zzGb/M8xm/zPMZv8AAAD/AAAAAAAAAAAAAAAAAAAA/zPMZv8zzGb/M8xm/wAAAP8AAAAAAAAAAAAAAP8zzGb/M8xm/zPMZv8AAAD/AAAAAAAAAAAAAAAAAAAA/zPMZv8zzGb/M8xm/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAA/zPMZv8zzGb/M8xm/wAAAP8AAAAAAAAAAAAAAAAAAAD/AAAA/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD/AAAA/wAAAP8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA//8AAOvXAADBgwAAwYMAAMGDAADhhwAA888AAP//AAD//wAAw8MAAIGBAADBgwAAg8EAAMfjAAD//////////w==',
        true
    );
    if ($icon === false) {
        throw new RuntimeException('No se pudo decodificar el favicon.');
    }
    return $icon;
}

/**
 * Genera robots.txt con la URL absoluta del sitemap.
 */
function generateRobots(array $config): string {
    return "User-agent: *\nAllow: /\nDisallow: /index.php?mode=manage\n\nSitemap: "
        . absoluteSiteUrl($config, 'sitemap.xml') . "\n";
}

/**
 * Genera un sitemap.xml con las URLs principales y los hilos existentes.
 */
function generateSitemap(array $threads, array $config): string {
    $base = htmlspecialchars(
        rtrim(absoluteSiteUrl($config), '/'),
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );

    $urls = ['index.html', 'board.html', $config['rulesfile'], $config['helpfile']];
    $xml  = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];

    foreach ($urls as $page) {
        $xml[] = '<url><loc>' . $base . '/' . htmlspecialchars((string)$page, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }
    foreach ($threads as $thread) {
        $xml[] = '<url><loc>' . $base . '/threads/' . (int)$thread['op']['num'] . '/</loc></url>';
    }
    $xml[] = '</urlset>';

    return implode("\n", $xml);
}

/**
 * Construye el bloque de paginación del tablón o del panel de administración.
 */
function paginationHtml(int $current, int $total, string $base, array $config, string $extraQuery = ''): string {
    $query = $extraQuery !== '' ? '&' . $extraQuery : '';

    if ($base === 'board') {
        $href = fn(int $index): string => $index === 0 ? siteUrl('board.html') : siteUrl('board-' . $index . '.html');
    } else {
        $href = fn(int $index): string => siteUrl($config['tenmafile']) . '?mode=manage&page=' . $index . $query;
    }

    $numbers = '';
    for ($index = 0; $index < $total; $index++) {
        if ($index === $current) {
            $numbers .= '<td style="padding:2px 3px;text-align:center;">[' . $index . ']</td>';
        } else {
            $numbers .= '<td style="padding:2px 3px;text-align:center;">[<a href="' . e($href($index)) . '" style="text-decoration:none;color:#0000ee;">' . $index . '</a>]</td>';
        }
    }

    return '<table align="center" style="border-collapse:collapse;margin:10px 0;background-color:#F0E0D6"><tbody><tr>'
        . $numbers . '</tr></tbody></table>';
}

/**
 * Regenera todas las páginas estáticas a partir de las publicaciones actuales.
 * Usa un bloqueo para que dos peticiones simultáneas no generen a la vez, y
 * escribe cada archivo de forma atómica para que nunca se sirva a medias.
 */
function buildPages(array $posts, array $config): void {
    $buildLock = fopen($config['buildlockfile'], 'c');
    if ($buildLock === false) {
        throw new RuntimeException('No se pudo abrir el bloqueo de generación.');
    }
    if (!flock($buildLock, LOCK_EX)) {
        fclose($buildLock);
        throw new RuntimeException('No se pudo bloquear la generación de páginas.');
    }

    try {
        ensureDir($config['threaddir'], 0755);

        $threadList = getThreads($posts);
        $threadList = array_slice($threadList, 0, $config['postsperpage'] * $config['maxpages']);
        $totalPages = max(1, (int)ceil(count($threadList) / $config['postsperpage']));
        $pages      = array_chunk($threadList, $config['postsperpage']) ?: [[]];

        // Elimina el HTML de hilos eliminados.
        foreach ($posts as $post) {
            if ((int)$post['parent'] !== 0 || (int)$post['deleted'] === 0) {
                continue;
            }
            $deletedThreadPath = threadFilePath((int)$post['num'], $config);
            if (is_file($deletedThreadPath)) {
                @unlink($deletedThreadPath);
            }
        }

        ensureThreadActionIcons();

        writeFileAtomic('index.html', generateIndex($posts, $config));
        writeFileAtomic($config['rulesfile'], generateRules($config));
        writeFileAtomic($config['helpfile'], generateHelp($config));
        writeFileAtomic($config['errorfile'], generateErrorPage($config));
        writeFileAtomic('favicon.ico', generateFavicon());
        writeFileAtomic('robots.txt', generateRobots($config));
        writeFileAtomic('sitemap.xml', generateSitemap($threadList, $config));
        writeFileAtomic('style.css', generateStyles($config));
        writeFileAtomic('tenma.js', generateScript());

        foreach ($pages as $index => $pageThreads) {
            $filename = $index === 0 ? 'board.html' : 'board-' . $index . '.html';
            writeFileAtomic($filename, generateBoard($pageThreads, $index, $totalPages, $posts, $config));
        }

        foreach ($threadList as $thread) {
            writeFileAtomic(threadFilePath((int)$thread['op']['num'], $config), generateThread($thread, $posts, $config));
        }

        foreach (glob('board-*.html') ?: [] as $file) {
            $index = (int)str_replace(['board-', '.html'], '', $file);
            if ($index >= $totalPages) {
                @unlink($file);
            }
        }

        pruneRateLimits($config);
    } finally {
        flock($buildLock, LOCK_UN);
        fclose($buildLock);
    }
}

/* =============================================================================
 * 13. CONTROLADORES DE PETICIONES
 * ========================================================================== */

/**
 * Traduce los códigos de error de subida de PHP a mensajes comprensibles.
 */
function getUploadErrorMessage(int $errorCode): string {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'El archivo supera el límite permitido por el servidor.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'El archivo supera el límite permitido por el formulario.';
        case UPLOAD_ERR_PARTIAL:
            return 'La subida se interrumpió antes de completarse.';
        case UPLOAD_ERR_NO_FILE:
            return 'No se seleccionó ningún archivo.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'No hay un directorio temporal disponible para la subida.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'No se pudo escribir el archivo en disco.';
        case UPLOAD_ERR_EXTENSION:
            return 'Una extensión de PHP detuvo la subida.';
        default:
            return 'Ocurrió un error desconocido al subir el archivo.';
    }
}

/**
 * Valida y guarda una imagen subida. Devuelve el nombre final, el tamaño real
 * y el MIME. Comprueba que el archivo venga de una subida real, que sea una
 * imagen, sus dimensiones y su tamaño; el nombre se genera al azar para evitar
 * colisiones y extensiones peligrosas.
 */
function storeUploadedImage(array $config, array $file): array {
    if (!isset($file['error']) || !is_int($file['error'])) {
        throw new RuntimeException('La información del archivo no es válida.');
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(getUploadErrorMessage((int)$file['error']));
    }

    $tmpName = $file['tmp_name'] ?? '';
    if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName) || !is_readable($tmpName)) {
        throw new RuntimeException('El archivo no pasó la validación de subida.');
    }

    // Tamaño real en disco; no se confía en el tamaño declarado por el cliente.
    $fileSize = @filesize($tmpName);
    if ($fileSize === false || $fileSize <= 0) {
        throw new RuntimeException('No se pudo comprobar el tamaño de la imagen.');
    }
    if ($fileSize > $config['maximagesize']) {
        throw new RuntimeException('La imagen supera el tamaño máximo de 3 MB.');
    }

    // getimagesize() valida la cabecera real, no solo el MIME declarado.
    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }

    $mimeType = (string)($imageInfo['mime'] ?? '');
    if (!in_array($mimeType, $config['allowedtypes'], true)) {
        throw new RuntimeException('Tipo de imagen no permitido. Usa JPG, PNG, GIF o WEBP.');
    }

    $width  = (int)($imageInfo[0] ?? 0);
    $height = (int)($imageInfo[1] ?? 0);
    if ($width <= 0 || $height <= 0) {
        throw new RuntimeException('No se pudieron leer las dimensiones de la imagen.');
    }
    if ($width > $config['maximagewidth']
        || $height > $config['maximageheight']
        || ($width * $height) > $config['maximagepixels']) {
        throw new RuntimeException('La imagen tiene dimensiones demasiado grandes.');
    }

    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $extension = $extensions[$mimeType] ?? '';
    if ($extension === '') {
        throw new RuntimeException('Tipo de imagen no permitido.');
    }

    $targetDir = rtrim($config['uploadfolder'], '/');
    ensureDir($targetDir, 0755);

    $filename    = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Error al guardar la imagen en el servidor.');
    }
    @chmod($destination, 0644);

    return [
        'filename' => $filename,
        'filesize' => $fileSize,
        'mimetype' => $mimeType,
    ];
}

/**
 * Sirve una página estática del tablón (?static=index.html|board.html).
 */
function handleStaticRequest(array $config): void {
    $staticPage = inputString($_GET, 'static');
    if ($staticPage === '' || preg_match('/\A(?:index\.html|board(?:-[0-9]+)?\.html)\z/', $staticPage) !== 1) {
        return;
    }

    $staticPath = __DIR__ . DIRECTORY_SEPARATOR . $staticPage;
    if (!is_file($staticPath)) {
        http_response_code(404);
        echo generateErrorPage($config);
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    header('Vary: Cookie');

    $html = file_get_contents($staticPath);
    if ($html === false) {
        throw new RuntimeException('No se pudo leer la página estática.');
    }

    if (isAdminAuthenticated($config)) {
        ensureThreadActionIcons();
        header('Cache-Control: private, no-store');
        $html = applyAdminNameField($html);
    }

    echo $html;
    exit;
}

/**
 * Sirve la página estática de un hilo (?staticthread=N).
 */
function handleStaticThreadRequest(array $config): void {
    $threadNum = inputInt($_GET, 'staticthread');
    if ($threadNum <= 0) {
        return;
    }

    $threadPost = getPostByNum($config, $threadNum);
    $staticPath = __DIR__ . DIRECTORY_SEPARATOR . threadFilePath($threadNum, $config);

    if ($threadPost === null
        || (int)$threadPost['parent'] !== 0
        || (int)$threadPost['deleted'] !== 0
        || !is_file($staticPath)) {
        http_response_code(404);
        echo generateErrorPage($config);
        exit;
    }

    header('Content-Type: text/html; charset=UTF-8');
    header('Vary: Cookie');

    $html = file_get_contents($staticPath);
    if ($html === false) {
        throw new RuntimeException('No se pudo leer la página del hilo.');
    }

    if (isAdminAuthenticated($config)) {
        ensureThreadActionIcons();
        header('Cache-Control: private, no-store');
        $html = applyAdminNameField($html);
    }

    echo $html;
    exit;
}

/**
 * Formulario y envío de reportes de moderación.
 */
function handleReportRequest(array $config, string $hashedip): void {
    $num = inputInt($_GET, 'num');
    if ($num <= 0) {
        $num = inputInt($_POST, 'num');
    }
    if ($num <= 0) {
        showError($config, 'Publicación inválida.');
    }

    $post = getPostByNum($config, $num);
    if ($post === null) {
        showError($config, 'La publicación no existe.', 404);
    }
    if ((int)$post['deleted'] !== 0) {
        showError($config, 'La publicación ya fue eliminada.');
    }
    if (!empty($post['pinned']) || !empty($post['locked']) || !empty($post['author_admin'])) {
        showError($config, 'No se pueden reportar publicaciones del administrador, fijadas o bloqueadas.');
    }

    if (isPostRequest()) {
        assertSameOriginRequest($config);

        $reason = trim(inputString($_POST, 'reason'));
        if ($reason === '') {
            showError($config, 'Debes indicar un motivo de reporte.');
        }

        // Límite por IP y por publicación para evitar inundar la moderación.
        if (!rateLimitAllow($config, 'report:' . $hashedip, (int)$config['reportspershour'], 3600)
            || !rateLimitAllow($config, 'report:' . $hashedip . ':' . $num, 3, 21600)) {
            header('Retry-After: 3600');
            showError($config, 'Has enviado demasiados reportes. Espera un rato antes de volver a intentarlo.', 429);
        }

        insertReport($config, $num, mb_substr($reason, 0, 500));

        $content = '<center><h1 style="margin-bottom:5px;">Reportar publicación No.' . $num
            . '</h1><hr><br><h2>Gracias</h2><br><p>Tu reporte para la publicación No.' . $num
            . ' fue enviado al equipo de moderación.</p></center><br>';

        echo renderPage($config['title'] ?: 'Tenma', $content, $config, ['index.html' => 'Inicio']);
        exit;
    }

    $title   = $config['title'] ?: 'Tenma';
    $content = '<center><h1 style="margin-bottom:5px;">Reportar publicación No.' . $num . '</h1><hr>
<div class="nav">[<a href="' . e(siteUrl('index.html')) . '">Inicio</a>]</div><hr>
<br>
<p>Antes de realizar un reporte asegúrate de que la publicación infrinja las <a href="'
        . e(siteUrl($config['rulesfile'])) . '">reglas</a>; de lo contrario, no lo hagas.</p><br>
<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=report&amp;num=' . $num . '">
<textarea name="reason" cols="48" rows="4" maxlength="500" placeholder="Motivo del reporte" style="display:block;margin:0 auto 8px;" autocomplete="off" required></textarea>
<input type="submit" value="Enviar reporte" style="padding: 0 .3em">
</form>
<br></center>';

    echo renderPage($title, $content, $config);
    exit;
}

/**
 * Eliminación de publicaciones por parte de su autor (dentro de los primeros
 * 30 segundos) o del administrador.
 */
function handleDeleteRequest(array $config, string $hashedip, string $legacyhashedip): void {
    if (!isPostRequest() || !isset($_POST['delete_posts'])) {
        return;
    }

    assertSameOriginRequest($config);

    $postNumbers = is_array($_POST['delete_posts']) ? $_POST['delete_posts'] : [$_POST['delete_posts']];
    $isAdmin     = isAdminAuthenticated($config);
    $expiredPostIds = [];

    foreach ($postNumbers as $postNumber) {
        if (!is_string($postNumber) || !ctype_digit($postNumber) || (int)$postNumber <= 0) {
            continue;
        }

        $num  = (int)$postNumber;
        $post = getPostByNum($config, $num);
        if ($post === null || (int)$post['deleted'] !== 0) {
            continue;
        }

        $age = time() - (int)$post['now'];
        $isAuthorWithinDeletionWindow = $age >= 0 && $age <= 30
            && matchesClientIpHash((string)$post['postiphash'], $hashedip, $legacyhashedip);

        if ($isAdmin || $isAuthorWithinDeletionWindow) {
            deletePostCompletely($config, $num);
        } elseif ($age > 30) {
            $expiredPostIds[$num] = $num;
        }
    }

    buildPages(readPosts($config), $config);

    $returnTo = inputString($_POST, 'return_to', siteUrl('board.html'));
    if (!isSafeReturnPath($returnTo)) {
        $returnTo = siteUrl('board.html');
    }

    if ($expiredPostIds !== []) {
        $postIds = implode(', ', array_values($expiredPostIds));
        $message = count($expiredPostIds) === 1
            ? 'No puedes eliminar la publicación No. ' . $postIds . ' porque se excedió el tiempo límite de 30 segundos.'
            : 'No puedes eliminar las publicaciones No. ' . $postIds . ' porque se excedió el tiempo límite de 30 segundos.';

        $content = '<div style="background:#fff;border:1px solid #800;padding:12px;text-align:center;">'
            . '<p class="error-message">' . e($message) . '</p>'
            . '<p style="margin-top:10px;"><a href="' . e($returnTo) . '">Volver</a></p>'
            . '</div>';

        echo renderPage($config['title'] ?: 'Tenma', $content, $config);
        exit;
    }

    header('Location: ' . $returnTo, true, 303);
    exit;
}

/**
 * Panel de administración: acceso, reportes, baneos, publicaciones y ajustes.
 */
function handleAdminRequest(array $config, string $hashedip, string $legacyhashedip): void {
    startAdminSession($config);

    if (!headers_sent()) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store');
    }

    $csrfToken = generateAdminCsrfToken($config);
    $canManage = false;

    // ---- Acceso ----
    if (isPostRequest() && isset($_POST['managepassword'])) {
        assertSameOriginRequest($config);

        if (!verifyAdminCsrfToken($config, inputString($_POST, 'csrf_token'))) {
            showError($config, 'La sesión de administración ha caducado. Recarga la página e inténtalo de nuevo.', 403);
        }

        checkLoginRateLimit($config, $hashedip);

        $username = inputString($_POST, 'manageusername');
        $password = inputString($_POST, 'managepassword');
        $hash     = adminPasswordHash($config);

        // Se evalúan ambos factores siempre para no revelar por tiempos si el
        // nombre de usuario existe.
        $validUser = hash_equals((string)$config['adminusername'], trim($username));
        $validPass = password_verify($password, $hash);

        if ($validUser && $validPass) {
            clearLoginAttempts($config, $hashedip);
            session_regenerate_id(true);
            issueAdminCookie($config);
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage', true, 303);
            exit;
        }

        registerFailedLogin($config, $hashedip);
        showError($config, 'Usuario o contraseña incorrectos.', 401);
    } elseif (isAdminAuthenticated($config)) {
        $canManage = true;
        // Renueva la caducidad de la cookie en cada visita (sesión deslizante),
        // salvo cuando la propia petición es el cierre de sesión.
        if (!(isPostRequest() && isset($_POST['logout']))) {
            issueAdminCookie($config);
        }
    }

    if (!$canManage) {
        $title = ($config['title'] ?: 'Tenma') . ' - Admin';

        $content = '<center><h1 style="margin-bottom:5px;">' . e($config['title'] ?: 'Tenma') . '</h1></center>
<hr>
<center>
<h3 style="margin:.5em 0;">Acceso de administrador</h3>
<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage" style="margin:10px 0;">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<table style="margin:0 auto;border-spacing:1px;"><tbody>
<tr>
  <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;"><b style="margin:.3em;">Usuario</b></td>
  <td><input type="text" name="manageusername" size="28" autocomplete="username" required></td>
</tr>
<tr>
  <td style="background:' . e((string)$config['formsidecolor']) . ';border:1px solid ' . e((string)$config['border']) . ';padding:.1em;"><b style="margin:.3em;">Contraseña</b></td>
  <td><input type="password" name="managepassword" size="28" autocomplete="current-password" required> <input type="submit" value="Entrar" style="padding:.15em .3em"></td>
</tr>
</tbody></table>
</form>
</center>';

        echo renderPage($title, $content, $config, ['index.html' => 'Inicio']);
        exit;
    }

    // ---- Acciones del panel (POST) ----
    if (isPostRequest() && !isset($_POST['managepassword'])) {
        assertSameOriginRequest($config);

        if (!verifyAdminCsrfToken($config, inputString($_POST, 'csrf_token'))) {
            showError($config, 'La sesión de administración ha caducado. Recarga la página e inténtalo de nuevo.', 403);
        }

        $page = inputInt($_POST, 'page');
        $tab  = inputString($_POST, 'tab', 'posts');
        $tab  = in_array($tab, ['posts', 'reports', 'bans', 'settings'], true) ? $tab : 'posts';

        if (isset($_POST['logout'])) {
            // Invalida de inmediato todas las cookies de administrador emitidas.
            rotateAdminSessionSecret($config);
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
            setcookie($config['managecookie'], '', adminCookieOptions($config, time() - 3600));
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage', true, 303);
            exit;
        }

        if (isset($_POST['togglelock'])) {
            $lockPath = $config['lockfile'];
            if (is_file($lockPath)) {
                if (!@unlink($lockPath)) {
                    throw new RuntimeException('No se pudo quitar el bloqueo de publicaciones.');
                }
            } elseif (file_put_contents($lockPath, 'locked', LOCK_EX) === false) {
                throw new RuntimeException('No se pudo activar el bloqueo de publicaciones.');
            }
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=settings', true, 303);
            exit;
        }

        if (isset($_POST['rebuild'])) {
            buildPages(readPosts($config), $config);
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=settings', true, 303);
            exit;
        }

        if (isset($_POST['save_notice'])) {
            $noticeMessage = inputString($_POST, 'notice_message');
            $noticeEnabled = inputString($_POST, 'notice_enabled');

            if (!in_array($noticeEnabled, ['', '1'], true)) {
                showError($config, 'El aviso no es válido.');
            }

            $noticeMessage = trim($noticeMessage);
            if (mb_strlen($noticeMessage) > $config['commentlimit']) {
                showError($config, 'El aviso no puede tener más de ' . (int)$config['commentlimit'] . ' caracteres.');
            }

            setSiteSetting($config, 'notice_message', $noticeMessage);
            setSiteSetting($config, 'notice_enabled', $noticeEnabled);
            buildPages(readPosts($config), $config);
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=settings', true, 303);
            exit;
        }

        if (isset($_POST['toggle_thread_lock']) || isset($_POST['toggle_thread_pin'])) {
            $action = isset($_POST['toggle_thread_lock']) ? 'toggle_thread_lock' : 'toggle_thread_pin';
            $threadValue = $_POST[$action];

            if (!is_string($threadValue) || !ctype_digit($threadValue) || (int)$threadValue <= 0) {
                showError($config, 'El hilo seleccionado no es válido.');
            }

            $threadNum  = (int)$threadValue;
            $threadPost = getPostByNum($config, $threadNum);
            if ($threadPost === null || (int)$threadPost['parent'] !== 0 || (int)$threadPost['deleted'] !== 0) {
                showError($config, 'El hilo seleccionado no existe o no se puede modificar.');
            }

            $flag = $action === 'toggle_thread_lock' ? 'locked' : 'pinned';
            setThreadFlag($config, $threadNum, $flag, empty($threadPost[$flag]));
        }

        if (isset($_POST['delete'])) {
            $deleteValue = $_POST['delete'];
            if (!is_string($deleteValue) || !ctype_digit($deleteValue) || (int)$deleteValue <= 0) {
                showError($config, 'La publicación seleccionada no es válida.');
            }
            deletePostCompletely($config, (int)$deleteValue);
        }

        if (isset($_POST['delete_ban'])) {
            $deleteBan = $_POST['delete_ban'];
            $parts = is_string($deleteBan) ? explode('|', $deleteBan, 2) : [];

            if (count($parts) !== 2 || !ctype_digit($parts[0]) || (int)$parts[0] <= 0
                || ($parts[1] !== '' && preg_match('/\A[a-f0-9]{16}\z/i', $parts[1]) !== 1)) {
                showError($config, 'La publicación o el hash del baneo no son válidos.');
            }

            deletePostCompletely($config, (int)$parts[0]);
            if ($parts[1] !== '') {
                addBan($config, $parts[1]);
            }
        }

        if (isset($_POST['ban']) && $_POST['ban'] !== '') {
            $durationOptions = ['3600' => 3600, '86400' => 86400, '604800' => 604800, '2592000' => 2592000, '0' => 0];
            $banValue = $_POST['ban'];
            $duration = $_POST['ban_duration'] ?? '';
            $hash = $banValue;

            if (is_string($banValue) && strpos($banValue, '|') !== false) {
                [$hash, $postValue] = explode('|', $banValue, 2);
                if (!ctype_digit($postValue) || (int)$postValue <= 0) {
                    showError($config, 'La publicación seleccionada para el baneo no es válida.');
                }
                $banPost = getPostByNum($config, (int)$postValue);
                if ($banPost === null || !hash_equals((string)$banPost['postiphash'], $hash)) {
                    showError($config, 'El hash del baneo no corresponde a la publicación.');
                }
                $duration = is_array($duration) ? ($duration[$postValue] ?? '') : '';
            }

            if (!is_string($duration) || !isset($durationOptions[$duration])
                || !is_string($hash) || preg_match('/\A[a-f0-9]{16}\z/i', $hash) !== 1) {
                showError($config, 'La duración o el hash del baneo no son válidos.');
            }

            addBan($config, $hash, $durationOptions[$duration] === 0 ? 0 : time() + $durationOptions[$duration]);
        }

        if (isset($_POST['unban']) && $_POST['unban'] !== '') {
            $hash = $_POST['unban'];
            if (!is_string($hash) || preg_match('/\A[a-f0-9]{16}\z/i', $hash) !== 1) {
                showError($config, 'El hash del baneo no es válido.');
            }
            removeBan($config, $hash);
        }

        if (isset($_POST['dismiss_report'])) {
            $reportValue = $_POST['dismiss_report'];
            if (!is_string($reportValue) || !ctype_digit($reportValue) || (int)$reportValue <= 0) {
                showError($config, 'El reporte seleccionado no es válido.');
            }
            deleteReport($config, (int)$reportValue);
        }

        buildPages(readPosts($config), $config);
        header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=' . rawurlencode($tab) . '&page=' . $page, true, 303);
        exit;
    }

    // ---- Panel (GET) ----
    $tab = inputString($_GET, 'tab', 'posts');
    $tab = in_array($tab, ['posts', 'reports', 'bans', 'settings'], true) ? $tab : 'posts';

    ensureThreadActionIcons();

    $allPosts   = array_reverse(readPosts($config));
    $postsByNum = [];
    foreach ($allPosts as $post) {
        $postsByNum[(int)$post['num']] = $post;
    }

    $totalCount = count($allPosts);
    $totalPages = max(1, (int)ceil($totalCount / $config['postsperpage']));
    $pageNumber = min(inputInt($_GET, 'page'), $totalPages - 1);
    $pagePosts  = array_slice($allPosts, $pageNumber * $config['postsperpage'], $config['postsperpage']);
    $title      = ($config['title'] ?: 'Tenma') . ' - Admin';

    $content = '<div class="admin-page">
<header class="admin-heading"><h1>' . e($config['title'] ?: 'Tenma') . ' - Panel de administración</h1></header>
<hr>';

    if (hash_equals(DEFAULT_ADMIN_PASSWORD_HASH, adminPasswordHash($config))) {
        $content .= '<p class="admin-warning">Estás usando la contraseña de administrador de ejemplo. Cámbiala antes de publicar el sitio.</p>';
    }
    if (password_needs_rehash(adminPasswordHash($config), PASSWORD_DEFAULT)) {
        $content .= '<p class="admin-warning">El hash de la contraseña de administrador usa un algoritmo obsoleto; genera uno nuevo.</p>';
    }

    $reports = getReports($config);
    $bans    = getBans($config);

    $content .= '<nav class="admin-tab-nav admin-button-group" aria-label="Secciones de administración">';
    foreach ([
        'posts'    => 'Publicaciones',
        'reports'  => 'Reportes',
        'bans'     => 'Baneos',
        'settings' => 'Ajustes',
    ] as $tabValue => $tabLabel) {
        $current = $tab === $tabValue ? ' aria-current="page"' : '';
        $content .= '<form method="GET" action="' . e(siteUrl($config['tenmafile'])) . '">
<input type="hidden" name="mode" value="manage">
<button type="submit" name="tab" value="' . $tabValue . '"' . $current . '>' . $tabLabel . '</button>
</form>';
    }
    $content .= '<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage" data-confirm="Esto cerrará todas las sesiones de administrador activas. ¿Continuar?">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<button type="submit" name="logout" value="1">Cerrar sesión</button>
</form>
</nav>
<hr>
<main class="admin-content">';

    if ($tab === 'settings') {
        $noticeMessage = e(getSiteSetting($config, 'notice_message'));
        $noticeEnabled = getSiteSetting($config, 'notice_enabled') === '1' ? ' checked' : '';
        $lockStatus    = is_file($config['lockfile']) ? 'Bloqueadas' : 'Abiertas';

        $content .= '<section class="admin-section"><h2 class="admin-section-title">Ajustes</h2>
<section class="admin-settings-block">
<h3>Avisos importantes</h3>
<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<label class="admin-notice-toggle"><input type="checkbox" name="notice_enabled" value="1"' . $noticeEnabled . '> Mostrar aviso en el tablón</label>
<textarea class="admin-notice-input" name="notice_message" maxlength="' . (int)$config['commentlimit'] . '" rows="3" placeholder="Escribe el aviso que se mostrará en rojo y centrado">' . $noticeMessage . '</textarea>
<div class="admin-button-group admin-settings-submit"><button type="submit" name="save_notice" value="1">Guardar aviso</button></div>
</form>
</section>
<hr>
<section class="admin-settings-block">
<h3>Estado de publicaciones</h3>
<p class="admin-status">Estado actual: <b>' . $lockStatus . '</b></p>
<div class="admin-toolbar admin-button-group">
<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<button type="submit" name="togglelock" value="1">' . (is_file($config['lockfile']) ? 'Desbloquear publicaciones' : 'Bloquear publicaciones') . '</button>
</form>
</div>
</section>
<hr>
<section class="admin-settings-block">
<h3>Mantenimiento</h3>
<p class="admin-status">Regenera todos los archivos .html realizando una actualización completa del sitio.</p>
<div class="admin-toolbar admin-button-group">
<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<button type="submit" name="rebuild" value="1">Regenerar HTML</button>
</form>
</div>
</section>
<hr>
<section class="admin-settings-block">
<h3>Seguridad</h3>
<p class="admin-status">HTTPS: <b>' . (isHttps($config) ? 'Sí' : 'No') . '</b> &middot; PHP: <b>'
            . e(PHP_VERSION) . '</b> &middot; Sesión de administración: <b>'
            . (int)round(((int)$config['adminsessionttl']) / 3600) . ' h</b></p>
<p class="admin-status">La cookie de administración se firma con un secreto propio y caduca sola. Al cerrar sesión se invalidan todas las cookies emitidas.</p>
</section>
</section>';
    }

    if ($tab === 'reports') {
        $content .= '<section class="admin-section"><h2 class="admin-section-title">Reportes (' . count($reports) . ')</h2>';

        if ($reports !== []) {
            $content .= '<div class="report-grid">';
            foreach ($reports as $report) {
                $reportNum = (int)$report['num'];
                $reason    = e(mb_substr((string)$report['reason'], 0, 120));
                $reportTime = e((string)$report['time']);
                $reportedPost = $postsByNum[$reportNum] ?? null;
                $reportedHash = is_array($reportedPost) ? (string)$reportedPost['postiphash'] : '';

                $content .= '<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">
<input type="hidden" name="tab" value="reports">
<input type="hidden" name="page" value="' . $pageNumber . '">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<div class="report-card reported-post">
<div class="rc-num">Reporte de No.' . $reportNum . '</div>
<div class="rc-reason">' . $reason . '</div>
<div class="rc-time">' . $reportTime . '</div>
<div class="rc-actions admin-button-group">
  <button type="submit" name="dismiss_report" value="' . (int)$report['id'] . '">Descartar</button>
  <button type="submit" name="delete" value="' . $reportNum . '">Eliminar</button>';

                if (preg_match('/\A[a-f0-9]{16}\z/i', $reportedHash) === 1) {
                    $content .= '<label>Duración del baneo: <select name="ban_duration">
<option value="3600">1 hora</option>
<option value="86400">1 día</option>
<option value="604800">7 días</option>
<option value="2592000">30 días</option>
<option value="0">Permanente</option>
</select></label>
  <button type="submit" name="ban" value="' . e($reportedHash) . '">Banear IP</button>';
                }

                $content .= '
</div>
<div class="reported-publication">';

                if ($reportedPost !== null) {
                    $content .= (int)$reportedPost['parent'] > 0
                        ? renderReply($reportedPost, $allPosts, false, $config, false, true)
                        : renderOP($reportedPost, $allPosts, false, false, $config, false, true);
                } else {
                    $content .= '<p class="error-message">La publicación reportada ya no está disponible.</p>';
                }

                $content .= '</div></div></form>';
            }
            $content .= '</div>';
        } else {
            $content .= '<p style="text-align:center;margin:10px 0;">No hay reportes pendientes.</p>';
        }

        $content .= '</section>';
    }

    if ($tab === 'bans') {
        $content .= '<section class="admin-section"><h2 class="admin-section-title">Baneos (' . count($bans) . ')</h2>';

        if ($bans !== []) {
            $content .= '<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">
<input type="hidden" name="tab" value="bans">
<input type="hidden" name="page" value="' . $pageNumber . '">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<div class="report-grid">';

            foreach ($bans as $ban) {
                $banHash   = (string)$ban['hash'];
                $expiresAt = (int)$ban['expires_at'];

                if ($expiresAt === 0) {
                    $banStatus = 'Permanente';
                } elseif ($expiresAt <= time()) {
                    $banStatus = 'Vencido el ' . date('Y-m-d H:i:s', $expiresAt);
                } else {
                    $banStatus = 'Hasta el ' . date('Y-m-d H:i:s', $expiresAt);
                }

                $content .= '<div class="report-card">
<div class="rc-num">Hash: ' . e($banHash) . '</div>
<div class="rc-time">' . e($banStatus) . '</div>
<div class="rc-actions admin-button-group"><button type="submit" name="unban" value="' . e($banHash) . '">Quitar baneo</button></div>
</div>';
            }

            $content .= '</div></form>';
        } else {
            $content .= '<p style="text-align:center;margin:10px 0;">No hay baneos registrados.</p>';
        }

        $content .= '</section>';
    }

    if ($tab === 'posts') {
        $content .= '<section class="admin-section"><h2 class="admin-section-title">Publicaciones (' . $totalCount . ')</h2>';
        $content .= '<form method="POST" action="' . e(siteUrl($config['tenmafile'])) . '?mode=manage">
<input type="hidden" name="tab" value="posts">
<input type="hidden" name="page" value="' . $pageNumber . '">
<input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
<div class="admin-post-list">';

        foreach ($pagePosts as $post) {
            $num  = (int)$post['num'];
            $hash = (string)$post['postiphash'];

            $postHtml = (int)$post['parent'] > 0
                ? renderReply($post, $allPosts, true, $config, false, true)
                : renderOP($post, $allPosts, false, true, $config, false, true);

            $content .= '<div class="admin-post-entry">'
                . $postHtml
                . '<div class="admin-post-controls"><span class="admin-post-hash">ID: '
                . e($hash) . '</span><div class="admin-post-actions admin-button-group">';

            if ((int)$post['deleted'] > 0) {
                $content .= '<i>Eliminado</i>';
            } else {
                $content .= '<button type="submit" name="delete" value="' . $num . '">Eliminar</button>';
            }

            $content .= '<label>Duración del baneo: <select name="ban_duration[' . $num . ']">
<option value="3600">1 hora</option>
<option value="86400">1 día</option>
<option value="604800">7 días</option>
<option value="2592000">30 días</option>
<option value="0">Permanente</option>
</select></label>
<button type="submit" name="ban" value="' . e($hash . '|' . $num) . '">Banear IP</button>';

            if ((int)$post['parent'] === 0 && (int)$post['deleted'] === 0) {
                $locked = !empty($post['locked']);
                $pinned = !empty($post['pinned']);

                $content .= '<button class="thread-admin-action" type="submit" name="toggle_thread_lock" value="' . $num
                    . '" aria-label="' . ($locked ? 'Desbloquear hilo' : 'Bloquear hilo') . '" title="'
                    . ($locked ? 'Desbloquear hilo' : 'Bloquear hilo') . '">' . ($locked ? 'Desbloquear' : 'Bloquear') . '</button>'
                    . '<button class="thread-admin-action" type="submit" name="toggle_thread_pin" value="' . $num
                    . '" aria-label="' . ($pinned ? 'Quitar fijado' : 'Fijar hilo') . '" title="'
                    . ($pinned ? 'Quitar fijado' : 'Fijar hilo') . '">' . ($pinned ? 'Desfijar' : 'Fijar') . '</button>';
            }

            $content .= '</div></div><hr style="clear:both;"></div>';
        }

        $content .= '</div></form>';
        $content .= paginationHtml($pageNumber, $totalPages, 'manage', $config, 'tab=posts');
        $content .= '</section>';
    }

    $content .= '</main></div>';

    echo renderPage($title, $content, $config, ['index.html' => 'Inicio']);
    exit;
}

/**
 * Impide publicar mientras el tablón está bloqueado (los administradores
 * autenticados pueden seguir publicando).
 */
function enforceBoardLock(array $config): void {
    if (!is_file($config['lockfile']) || isAdminAuthenticated($config)) {
        return;
    }
    showError($config, 'Las publicaciones están bloqueadas temporalmente.', 403);
}

/**
 * Publicación de hilos y respuestas.
 */
function handlePublishRequest(array $config, string $hashedip, string $legacyhashedip): void {
    if (!isPostRequest()) {
        return;
    }

    assertSameOriginRequest($config);

    foreach (['name', 'subject', 'com'] as $field) {
        if (isset($_POST[$field]) && !is_string($_POST[$field])) {
            showError($config, 'Los datos de la publicación no son válidos.');
        }
    }

    $name    = trim(inputString($_POST, 'name'));
    $subject = trim(inputString($_POST, 'subject'));
    $comment = trim(inputString($_POST, 'com'));

    $parentValue = $_POST['parent'] ?? '0';
    if (!is_string($parentValue) || !ctype_digit($parentValue)) {
        showError($config, 'El hilo seleccionado no es válido.');
    }
    $parent = (int)$parentValue;

    if ($parent > 0) {
        $parentThread = getPostByNum($config, $parent);
        if ($parentThread === null || (int)$parentThread['parent'] !== 0 || (int)$parentThread['deleted'] !== 0) {
            showError($config, 'El hilo al que intentas responder no existe.', 404);
        }
        if (!empty($parentThread['locked'])) {
            showError($config, 'Este hilo está bloqueado y no admite nuevas respuestas.', 403);
        }
    }

    if (!mb_check_encoding($name . $subject . $comment, 'UTF-8')) {
        showError($config, 'El texto contiene caracteres no válidos.');
    }

    if (!empty($config['badstrings'])) {
        $pattern = '/' . implode('|', array_map(fn(string $bad): string => preg_quote($bad, '/'), $config['badstrings'])) . '/i';
        if (preg_match($pattern, $comment) === 1
            || preg_match($pattern, $name) === 1
            || preg_match($pattern, $subject) === 1) {
            showError($config, 'Tu publicación contiene frases prohibidas.');
        }
    }

    // Validación del texto ANTES de mover la imagen: así no quedan archivos
    // huérfanos cuando el formulario tiene un error.
    $hasImageUpload = isset($_FILES['image'])
        && is_array($_FILES['image'])
        && isset($_FILES['image']['error'])
        && is_int($_FILES['image']['error'])
        && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if (isset($_FILES['image']) && !is_array($_FILES['image'])) {
        showError($config, 'La información del archivo no es válida.');
    }
    if (isset($_FILES['image']) && is_array($_FILES['image'])
        && isset($_FILES['image']['error']) && !is_int($_FILES['image']['error'])) {
        showError($config, 'La información del archivo no es válida.');
    }

    if ($parent === 0) {
        if (!$hasImageUpload) {
            showError($config, 'Para crear un hilo debes adjuntar una imagen.');
        }
        if ($subject === '') {
            showError($config, 'Para crear un hilo debes indicar un asunto.');
        }
        if ($comment === '') {
            showError($config, 'Para crear un hilo debes escribir un comentario.');
        }
    } elseif ($comment === '' && !$hasImageUpload) {
        showError($config, 'Debes escribir un comentario o subir una imagen.');
    }

    if (mb_strlen($name) > $config['namelimit']) {
        showError($config, 'Tu nombre no puede tener más de ' . (int)$config['namelimit'] . ' caracteres.');
    }
    if (mb_strlen($subject) > $config['subjectlimit']) {
        showError($config, 'Tu asunto no puede tener más de ' . (int)$config['subjectlimit'] . ' caracteres.');
    }
    if (mb_strlen($comment) > $config['commentlimit']) {
        showError($config, 'Tu comentario no puede tener más de ' . (int)$config['commentlimit'] . ' caracteres.');
    }

    $isAdminPost = isAdminAuthenticated($config);
    $name = $name !== '' ? $name : (string)$config['defaultname'];
    if ($config['forcedanonymity']) {
        $name = (string)$config['defaultname'];
    }
    if ($isAdminPost) {
        $name = 'Administrador';
    }

    // Subida de la imagen (si la hay).
    $imageFile = '';
    $fileSize  = 0;
    if ($hasImageUpload) {
        try {
            $uploaded  = storeUploadedImage($config, $_FILES['image']);
            $imageFile = $uploaded['filename'];
            $fileSize  = $uploaded['filesize'];
        } catch (RuntimeException $uploadError) {
            showError($config, $uploadError->getMessage());
        }
    }

    // Tiempo de espera y duplicados, por IP (no globales).
    $now  = time();
    $last = getLastPostByHash($config, $hashedip);
    if ($last !== null) {
        $elapsed = $now - (int)$last['now'];
        if ($elapsed >= 0 && $elapsed < (int)$config['cooldown']) {
            header('Retry-After: ' . (int)$config['cooldown']);
            showError($config, 'Por favor espera ' . ((int)$config['cooldown'] - $elapsed) . ' segundo(s) entre publicaciones.', 429);
        }
        if ((string)$last['name'] === $name && (string)$last['comment'] === $comment) {
            showError($config, 'Ya dijiste eso recientemente.', 429);
        }
    }

    if (countPostsByHashSince($config, $hashedip, $now - 3600) >= (int)$config['postsperhour']) {
        header('Retry-After: 3600');
        showError($config, 'Has alcanzado el límite de publicaciones por hora. Inténtalo más tarde.', 429);
    }

    try {
        $num = insertPost($config, [
            'name'         => $name,
            'subject'      => $subject,
            'comment'      => $comment,
            'time'         => spanishDate(),
            'now'          => $now,
            'postiphash'   => $hashedip,
            'image'        => $imageFile,
            'parent'       => $parent,
            'filesize'     => $fileSize,
            'author_admin' => $isAdminPost ? 1 : 0,
        ]);
    } catch (Throwable $insertError) {
        if ($imageFile !== '') {
            @unlink(rtrim($config['uploadfolder'], '/') . '/' . $imageFile);
        }
        throw $insertError;
    }

    if ($parent === 0) {
        pruneOldThreads($config);
    }

    buildPages(readPosts($config), $config);

    $location = $parent > 0
        ? threadUrl($parent) . '#p' . $num
        : siteUrl('board.html');

    header('Location: ' . $location, true, 303);
    exit;
}

/**
 * Redirección de la raíz a la portada estática, generándola si no existe.
 */
function handleHomeRedirect(array $config): void {
    if (!is_file('index.html')) {
        buildPages(readPosts($config), $config);
    }

    header('Location: ' . siteUrl('index.html'), true, 302);
    exit;
}

/* =============================================================================
 * 14. ENRUTADOR
 * ========================================================================== */

// Si el cuerpo de la petición supera post_max_size, PHP vacía $_POST y $_FILES.
// Se detecta para devolver un error claro en lugar de un formulario vacío.
if (isPostRequest() && empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    showError($config, 'La solicitud supera el tamaño máximo permitido por el servidor.', 413);
}

$ip             = clientIp($config);
$hashedip       = substr(hash_hmac('sha256', $ip, ipHashSecret($config)), 0, IP_HASH_LENGTH);
$legacyhashedip = substr(sha1($ip), 0, IP_HASH_LENGTH);
$mode           = inputString($_GET, 'mode');

enforceBan($config, $hashedip, $legacyhashedip);

// Rutas estáticas (se sirven antes de cualquier otra cosa).
handleStaticRequest($config);
handleStaticThreadRequest($config);

// Reportes.
if ($mode === 'report') {
    handleReportRequest($config, $hashedip);
}

// Borrado de publicaciones propias o del administrador.
handleDeleteRequest($config, $hashedip, $legacyhashedip);

// Panel de administración.
if ($mode === 'manage') {
    handleAdminRequest($config, $hashedip, $legacyhashedip);
}

// Bloqueo temporal de publicaciones.
enforceBoardLock($config);

// Publicación de hilos y respuestas.
handlePublishRequest($config, $hashedip, $legacyhashedip);

// Portada.
handleHomeRedirect($config);