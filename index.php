<?php
// ===== CONFIGURACIÓN =====
$config = [
    'title'                     => 'Tenma',
    'subtitle'                  => 'Tablón anónimo para discusión, imágenes y debates breves',
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
    'defaultname'               => 'Anónimo',
    'deletionphrase'            => 'Eliminado',
    'rules'
    => [
        'Respeto: trata a los demás con respeto. No se permiten insultos, amenazas ni ataques personales.',
        'Contenido ilegal: no publiques contenido que infrinja las leyes aplicables.',
        'Privacidad: no publiques datos personales de otras personas, como direcciones, teléfonos, documentos o información que permita identificarlas.',
        'Archivos: se permiten imágenes JPG, PNG, GIF y WEBP de hasta 3 MB por archivo.',
        'Spam y publicidad: no repitas mensajes ni publiques publicidad, enlaces comerciales o propaganda no solicitada.',
        'Seguridad: no compartas enlaces ni archivos que contengan malware, intenten robar información o busquen engañar a otras personas.',
        'Reportes: el equipo de moderación revisará los reportes y podrá tomar medidas, como editar o eliminar publicaciones o aplicar baneos.',
        'Moderación: el administrador puede eliminar publicaciones o banear direcciones IP o sus hashes, sin previo aviso, ante infracciones claras.',
        'Protección de menores: no publiques imágenes ni información que exponga a menores de edad o pueda ponerlos en riesgo.',
        'Al publicar, aceptas que el contenido se almacene y sea visible públicamente hasta que lo elimine el equipo de moderación.',
    ],
    'helps'
    => [
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
    ],
    'adminusername'      => 'admin',
    'adminpasswordhash'  => '$2y$10$zySRVu7lckJtryP6KgXkeufif94bcBvhbvecQLmhNAFLauqbI/jDy', // admin123
    'managecookie'       => 'tenma_manage',
    'lockfile'           => 'tenma.lock',
    'tenmafile'          => 'index.php',
    'rulesfile'          => 'rules.html',
    'helpfile'           => 'help.html',
    'errorfile'          => 'error.html',
    'databasefolder'     => 'database/',
    'databasefile'       => 'database/tenma.sqlite',
    'uploadfolder'       => 'uploads/',
    'threaddir'          => 'threads/',
    'postsperpage'       => 10,
    'maxpages'           => 10,
    'forcedanonymity'    => false,
    'cooldown'           => 5,
    'namelimit'          => 20,
    'subjectlimit'       => 100,
    'commentlimit'       => 2000,
    'maxreplieshown'     => 5,
    'maximagesize'       => 3 * 1024 * 1024,
    'allowedtypes'       => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'badstrings'         => [],
    'trustedproxies'     => [],
];

function siteBasePath(): string {
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $scriptDir  = rtrim(dirname($scriptName), '/');
    return $scriptDir === '' ? '' : $scriptDir;
}

function siteUrl(string $path = ''): string {
    $base = siteBasePath();
    $path = '/' . ltrim($path, '/');
    return $base === '' ? $path : $base . $path;
}

function threadFilePath(int $num, array $config): string {
    return rtrim($config['threaddir'], '/') . '/thread-' . $num . '.html';
}

function threadUrl(int $num): string {
    return siteUrl('threads/' . $num . '/');
}

/**
 * Indica si la petición actual se está sirviendo mediante HTTPS.
 * Solo confía en cabeceras de proxy si la IP de origen figura como proxy de confianza.
 */
function isHttps(array $config): bool {
    if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    if ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($remote !== '' && isTrustedProxy($remote, $config) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
        return true;
    }
    return false;
}

/**
 * Comprueba si una IP pertenece a un rango CIDR (IPv4 o IPv6).
 */
function ipMatchesCidr(string $ip, string $cidr): bool {
    $ipBin     = @inet_pton($ip);
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
    $bytes      = intdiv($bits, 8);
    $remainder  = $bits % 8;
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
        if (is_string($cidr) && ipMatchesCidr($remoteAddr, $cidr)) return true;
    }
    return false;
}

/**
 * Determina la IP real del visitante. Solo se confía en cabeceras como
 * CF-Connecting-IP o X-Forwarded-For cuando la petición llega desde un
 * proxy declarado en 'trustedproxies'; en caso contrario se usa REMOTE_ADDR
 * para impedir que un cliente falsifique su IP y evada baneos.
 */
function clientIp(array $config): string {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (!empty($config['trustedproxies']) && isTrustedProxy($remote, $config)) {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR'] as $header) {
            if (!empty($_SERVER[$header])) {
                $candidate = trim(explode(',', $_SERVER[$header])[0]);
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }
    }
    return $remote;
}

/**
 * Devuelve (creando si es necesario) un secreto local usado como "pepper" para
 * derivar el hash de IP. Sin este secreto, un hash SHA1 de una IP es
 * trivialmente reversible (el espacio de IPs es pequeño), por lo que si la
 * base de datos se filtrara se podría desanonimizar a los usuarios.
 */
function ipHashSecret(array $config): string {
    static $secret = null;
    if ($secret !== null) return $secret;

    $path = rtrim($config['databasefolder'], '/') . '/iphash.secret';
    if (!is_dir(dirname($path))) {
        if (!mkdir(dirname($path), 0755, true) && !is_dir(dirname($path))) {
            throw new RuntimeException('No se pudo crear el directorio del secreto de IP.');
        }
    }

    $lock = fopen($path . '.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('No se pudo abrir el bloqueo del secreto de IP.');
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        throw new RuntimeException('No se pudo bloquear el secreto de IP.');
    }

    try {
        if (!file_exists($path)) {
            $newSecret = bin2hex(random_bytes(32));
            if (file_put_contents($path, $newSecret, LOCK_EX) !== strlen($newSecret)) {
                throw new RuntimeException('No se pudo guardar el secreto de IP.');
            }
            @chmod($path, 0600);
        }

        $secret = file_get_contents($path);
        if ($secret === false || !preg_match('/\A[a-f0-9]{64}\z/', $secret)) {
            throw new RuntimeException('El secreto de IP no se pudo leer o no tiene un formato válido.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    return $secret;
}

function matchesClientIpHash(string $storedHash, string $currentHash, string $legacyHash): bool {
    return hash_equals($storedHash, $currentHash) || hash_equals($storedHash, $legacyHash);
}

// ===== INICIALIZACIÓN =====
ini_set('default_charset', 'UTF-8');
date_default_timezone_set($config['timezone']);

function startAdminSession(array $config): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('tenma_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => isHttps($config),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

startAdminSession($config);

if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Tenma requiere PHP 7.4 o superior. Estás usando PHP ' . PHP_VERSION);
}
if (!extension_loaded('pdo_sqlite')) {
    die('Tenma requiere la extensión pdo_sqlite de PHP, que no está activa en este servidor.');
}

foreach ([$config['uploadfolder'], $config['databasefolder'], $config['threaddir']] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$errorDocumentUrl = siteUrl($config['errorfile']);

$htaccessFiles = [
    $config['uploadfolder'] . '.htaccess' => <<<HTACCESS
# Generado automáticamente para Tenma. No editar a mano.
Options -ExecCGI -Indexes -Includes
RemoveHandler .php .php3 .php4 .php5 .php7 .phtml .pl .py .cgi .sh .asp .aspx
AddType text/plain .php .php3 .php4 .php5 .php7 .phtml .pl .py .cgi .sh .asp .aspx

<IfModule mod_php.c>
    php_flag engine off
</IfModule>

<IfModule mod_authz_core.c>
    <FilesMatch "\.(php\d?|phtml|pl|py|cgi|sh|asp|aspx|exe)$">
        Require all denied
    </FilesMatch>
</IfModule>
<IfModule !mod_authz_core.c>
    <FilesMatch "\.(php\d?|phtml|pl|py|cgi|sh|asp|aspx|exe)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
</IfModule>
HTACCESS,

    $config['databasefolder'] . '.htaccess' => <<<HTACCESS
# Generado automáticamente para Tenma. No editar a mano.
<IfModule mod_authz_core.c>
    Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
    Order allow,deny
    Deny from all
</IfModule>
HTACCESS,

    $config['threaddir'] . '.htaccess' => <<<HTACCESS
# Generado automáticamente para Tenma. No editar a mano.
<IfModule mod_authz_core.c>
    <FilesMatch "\.(php\d?|phtml|pl|py|cgi|sh|asp|aspx|exe|db|sqlite3|sqlite|sql)$">
        Require all denied
    </FilesMatch>
</IfModule>
<IfModule !mod_authz_core.c>
    <FilesMatch "\.(php\d?|phtml|pl|py|cgi|sh|asp|aspx|exe|db|sqlite3|sqlite|sql)$">
        Order allow,deny
        Deny from all
    </FilesMatch>
</IfModule>
HTACCESS,

    '.htaccess' => <<<HTACCESS
# Generado automáticamente para Tenma. No editar a mano.
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

<FilesMatch "\.(sqlite|sqlite3|db|db3|sql|bak|backup|log|ini|conf|env|lock)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>

<FilesMatch "^\.(?!htaccess$)">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>

<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "same-origin"
    Header set Content-Security-Policy "default-src 'self'; img-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'"
</IfModule>
HTACCESS,
];

foreach ($htaccessFiles as $path => $content) {
    $dir = dirname($path);
    if ($dir !== '.' && !is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $needsWrite = !file_exists($path);
    if (!$needsWrite && $path === '.htaccess') {
        $existing = (string)@file_get_contents($path);
        $expectedErrorDocument = 'ErrorDocument 404 ' . $errorDocumentUrl;
        if (
            strpos($existing, $expectedErrorDocument) === false
            || strpos($existing, 'RewriteRule ^threads') === false
            || strpos($existing, 'staticthread=$1') === false
            || strpos($existing, 'RewriteRule ^(index\\.html|board') === false
        ) {
            $needsWrite = true;
        }
    }
    if ($needsWrite) {
        file_put_contents($path, $content);
    }
}

$ip       = clientIp($config);
$hashedip = substr(hash_hmac('sha256', $ip, ipHashSecret($config)), 0, 16);
$legacyhashedip = substr(sha1($ip), 0, 16);

// ===== CAPA DE BASE DE DATOS =====
/**
 * Devuelve una conexión PDO reutilizable a la base de datos SQLite del tablón.
 * Se mantiene en memoria para no abrirla en cada consulta.
 */
function getPDO(array $config): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $pdo = new PDO('sqlite:' . $config['databasefile']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode = WAL');
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
    $postColumns = $pdo->query('PRAGMA table_info(posts)')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('locked', $postColumns, true)) {
        $pdo->exec('ALTER TABLE posts ADD COLUMN locked INTEGER NOT NULL DEFAULT 0');
    }
    if (!in_array('pinned', $postColumns, true)) {
        $pdo->exec('ALTER TABLE posts ADD COLUMN pinned INTEGER NOT NULL DEFAULT 0');
    }
    if (!in_array('author_admin', $postColumns, true)) {
        $pdo->exec('ALTER TABLE posts ADD COLUMN author_admin INTEGER NOT NULL DEFAULT 0');
    }
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_parent ON posts(parent)");
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
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        hash        TEXT    PRIMARY KEY,
        attempts    INTEGER NOT NULL DEFAULT 0,
        lastattempt INTEGER NOT NULL DEFAULT 0
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key   TEXT PRIMARY KEY,
        setting_value TEXT NOT NULL DEFAULT ''
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

/**
 * Crea los iconos de estado de hilo a partir de los GIF proporcionados.
 */
function ensureThreadActionIcons(): void {
    $icons = [
        'admin.gif' => 'R0lGODlhEAAQAJECAAAAAPAAAP///wAAACH5BAEAAAIALAAAAAAQABAAAAIvlI+pq+D9DAgUoFkPDlbs7lFZKIJOJJ3MyraoB14jFpOcVMpzrnF3OKlZYsMWowAAOw==',
        'thread-lock.gif' => 'R0lGODlhEAAQANUhAKGhoWqp6Qh1uYWs5hZl0Jq56HSk42Oc4TCG1lOW3UGO2aqqqoO16cHS6t7e3tDb6sXFxezs7Le3twxyvhBuxRVpzBpnzLm5uQlzusrY6rjM6RJqyA1wwKrC6Obm5tvg6sHBwQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAACEALAAAAAAQABAAAAZywJBwSCwaAcikcQiIQJ4XCWDZdCSRl2kRYCUCpMcuE0wkLDwLC4FgWYAWlXLk88g0GppOgUEpPwoDAwYHCQoIDBN+gIKEhgwYZRmLg4UIAQJlDZONlphDBJqBlIaXRBUMAamqqp5CGxQcExgCtLWtS7hBADs=',
        'thread-pin.gif' => 'R0lGODlhEAAQAOZhADNVoDhbplWQ1jt4yDJvwZW036fE6KPC50JkrFuT1StKl2if3VuR1Ehxtxw2h0mCzVeR1iBBkzBUoW+e2tzc3CVGlXR0dCpVpqG+5DRxxSVXqyI+jS5Qnpq75WJvl16T1FuU13un3IaGhjhzxHFxcZKw3FV+wGxsbGx5n6ioqJ+fny9Pm9TU1FuP05G25TFSnS5Pm4Sx5C5qv6/J7F6X2SxJlo+Pjz98yoOn2VyW2ZW136fE6Udts7LN7kVrsYSt30lrsHim3Gyc2VN6vEOAzIWv4UNorz1qs5SUlDNvwXyq4aTC52ae3Zy44T15x0tyt0RstE6J0Hd3d8jIyDhkr1R9wEtsr6LB50uFzkBkrlKN04uy4mGV1Hir4oKCgo+46BkyhAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAGEALAAAAAAQABAAAAeFgGGCg4RVJoSIhA1XM0OJiVBdXwZPj4Q8C0wxGD6WYUY6RTQ5Sk1ZhAGpCAVbIBACAhNAg1YlO0sHPwlaRwC+gy84QT0dIR9RVDCPEk4MLlxYRAqeHCMPQjcDNZ4oKwQDLUkblioUHhUyGReWNixIghEaDo9eUyKEYI8WKVKehCQn/BEKBAA7',
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
 * Devuelve null cuando no existe.
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
 * Recupera todas las respuestas asociadas a un hilo o publicación padre.
 */
function getRepliesOf(array $config, int $parent): array {
    $stmt = getPDO($config)->prepare('SELECT * FROM posts WHERE parent = :parent');
    $stmt->execute(['parent' => $parent]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Inserta una nueva publicación en la base de datos y devuelve su ID asignado.
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
 * Marca una publicación como eliminada sin borrar su registro inmediatamente.
 */
function markPostDeleted(array $config, int $num): void {
    getPDO($config)->prepare('UPDATE posts SET deleted = 1 WHERE num = :num')->execute(['num' => $num]);
}

/**
 * Elimina el archivo de imagen asociado a una publicación, si existe.
 */
function deletePostImageFile(array $config, array $post): void {
    if (empty($post['image'])) return;
    $path = $config['uploadfolder'] . $post['image'];
    if (file_exists($path)) {
        @unlink($path);
    }
}

/**
 * Elimina una publicación de forma completa, incluyendo su imagen y sus reportes.
 */
function deletePostCompletely(array $config, int $num): void {
    $post = getPostByNum($config, $num);
    if ($post !== null) {
        deletePostImageFile($config, $post);
    }
    markPostDeleted($config, $num);
    deleteReportsForPost($config, $num);
}

/**
 * Borra un hilo completo y todos sus archivos relacionados.
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

    $threadfile = 'thread-' . $threadnum . '.html';
    if (file_exists($threadfile)) {
        @unlink($threadfile);
    }
}

/**
 * Elimina hilos antiguos cuando se supera el límite de páginas permitido.
 */
function pruneOldThreads(array $config): void {
    $limit   = $config['postsperpage'] * $config['maxpages'];
    $posts   = readPosts($config);
    $threads = getThreads($posts);

    while (count($threads) > $limit) {
        $last = array_pop($threads);
        deleteThreadCompletely($config, (int)$last['op']['num']);
    }
}

/**
 * Obtiene las publicaciones más recientes, limitadas a una cantidad concreta.
 */
function getRecentPosts(array $config, int $limit): array {
    $stmt = getPDO($config)->prepare('SELECT * FROM posts ORDER BY num DESC LIMIT :limit');
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

/**
 * Comprueba si una IP o hash de IP está baneado.
 * Acepta opcionalmente un hash heredado (versiones anteriores) para no
 * perder baneos ya existentes tras cambiar el algoritmo de hash.
 */
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_SECS = 900; // 15 minutos

/**
 * Lanza un error si el hash de IP indicado ha superado el número máximo de
 * intentos fallidos de acceso al panel de administración en la ventana de
 * tiempo configurada, mitigando ataques de fuerza bruta contra la contraseña.
 */
function checkLoginRateLimit(array $config, string $hash): void {
    $stmt = getPDO($config)->prepare('SELECT attempts, lastattempt FROM login_attempts WHERE hash = :hash');
    $stmt->execute(['hash' => $hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && (int)$row['attempts'] >= LOGIN_MAX_ATTEMPTS && (time() - (int)$row['lastattempt']) < LOGIN_LOCKOUT_SECS) {
        $wait = (int)ceil((LOGIN_LOCKOUT_SECS - (time() - (int)$row['lastattempt'])) / 60);
        showError($config, 'Demasiados intentos fallidos. Espera unos ' . max(1, $wait) . ' minuto(s) antes de volver a intentarlo.');
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
        'día' => 86400,
        'hora' => 3600,
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
 * Añade o actualiza un baneo. expiresAt igual a cero representa un baneo permanente.
 */
function addBan(array $config, string $hash, int $expiresAt = 0): void {
    getPDO($config)->prepare('INSERT INTO bans (hash, expires_at) VALUES (:hash, :expires_at)
        ON CONFLICT(hash) DO UPDATE SET expires_at = excluded.expires_at')
        ->execute(['hash' => $hash, 'expires_at' => $expiresAt]);
}

/**
 * Devuelve todos los baneos registrados para que el administrador pueda revisarlos y quitarlos.
 */
function getBans(array $config): array {
    return getPDO($config)->query('SELECT hash, expires_at FROM bans ORDER BY expires_at DESC, hash ASC')
        ->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Quita un baneo por su hash.
 */
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

/**
 * Recupera todos los reportes pendientes de moderación.
 */
function getReports(array $config): array {
    return getPDO($config)->query('SELECT * FROM reports ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Elimina un reporte concreto cuando se descarta la denuncia.
 */
function deleteReport(array $config, int $id): void {
    getPDO($config)->prepare('DELETE FROM reports WHERE id = :id')->execute(['id' => $id]);
}

/**
 * Borra todos los reportes asociados a una publicación eliminada.
 */
function deleteReportsForPost(array $config, int $num): void {
    getPDO($config)->prepare('DELETE FROM reports WHERE num = :num')->execute(['num' => $num]);
}

/**
 * Secreto rotable usado para firmar el token de sesión de administración.
 * Regenerarlo (rotateAdminSessionSecret) invalida de inmediato todas las
 * cookies de administrador existentes, incluso sin cambiar la contraseña.
 */
function adminSessionSecret(array $config): string {
    static $secret = null;
    if ($secret !== null) return $secret;

    $path = rtrim($config['databasefolder'], '/') . '/admin.secret';
    if (!is_dir(dirname($path))) {
        if (!mkdir(dirname($path), 0755, true) && !is_dir(dirname($path))) {
            throw new RuntimeException('No se pudo crear el directorio del secreto de administración.');
        }
    }
    $lock = fopen($path . '.lock', 'c');
    if ($lock === false) {
        throw new RuntimeException('No se pudo abrir el bloqueo del secreto de administración.');
    }
    if (!flock($lock, LOCK_EX)) {
        fclose($lock);
        throw new RuntimeException('No se pudo bloquear el secreto de administración.');
    }

    try {
        if (!file_exists($path)) {
            $newSecret = bin2hex(random_bytes(32));
            if (file_put_contents($path, $newSecret, LOCK_EX) !== strlen($newSecret)) {
                throw new RuntimeException('No se pudo guardar el secreto de administración.');
            }
            @chmod($path, 0600);
        }
        $secret = file_get_contents($path);
        if ($secret === false || !preg_match('/\A[a-f0-9]{64}\z/', $secret)) {
            throw new RuntimeException('El secreto de administración no se pudo leer o no tiene un formato válido.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }

    return $secret;
}

/**
 * Sobrescribe el secreto de sesión de administración, invalidando todas las
 * cookies de administrador emitidas anteriormente ("cerrar todas las sesiones").
 */
function rotateAdminSessionSecret(array $config): void {
    $path = rtrim($config['databasefolder'], '/') . '/admin.secret';
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

/**
 * Genera un token firmado para validar la sesión de administración.
 */
function adminSessionToken(array $config): string {
    return hash_hmac('sha256', $config['adminusername'] . '|' . adminSessionSecret($config), $config['adminpasswordhash']);
}

function isAdminAuthenticated(array $config): bool {
    $cookie = $_COOKIE[$config['managecookie']] ?? '';
    return is_string($cookie) && $cookie !== '' && hash_equals(adminSessionToken($config), $cookie);
}

function applyAdminNameField(string $html, array $config): string {
    $input = '<input type="text" name="name" size="28" value="Administrador" style="color:#f00;font-weight:bold;" readonly autocomplete="off" placeholder="Nombre">';
    return preg_replace('/<input type="text" name="name"[^>]*>/', $input, $html) ?? $html;
}

function generateAdminCsrfToken(array $config): string {
    startAdminSession($config);

    if (empty($_SESSION['tenma_admin_csrf'])) {
        $_SESSION['tenma_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['tenma_admin_csrf'];
}

function verifyAdminCsrfToken(array $config, $token): bool {
    startAdminSession($config);

    $expected = $_SESSION['tenma_admin_csrf'] ?? '';
    return is_string($expected) && is_string($token) && $expected !== '' && hash_equals($expected, $token);
}

$banExpiresAt = getActiveBanExpiration($config, $hashedip, $legacyhashedip);
if ($banExpiresAt !== null) {
    $banMessage = $banExpiresAt === 0
        ? '<h3>Estás baneado de este tablón.</h3><br><h4>Tu baneo es permanente.</h4>'
        : '<h3>Estás baneado temporalmente de este tablón.</h3><br><h4>Tiempo restante: ' . formatBanTimeRemaining($banExpiresAt - time()) . '.</h4>';
    showError($config, $banMessage);
}

$staticPage = $_GET['static'] ?? '';
if (is_string($staticPage) && preg_match('/\A(?:index\.html|board(?:-[0-9]+)?\.html)\z/', $staticPage)) {
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
        $html = applyAdminNameField($html, $config);
    }
    echo $html;
    exit;
}

$staticThread = $_GET['staticthread'] ?? '';
if (is_string($staticThread) && ctype_digit($staticThread) && (int)$staticThread > 0) {
    $threadPost = getPostByNum($config, (int)$staticThread);
    $staticPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, threadFilePath((int)$staticThread, $config));
    if ($threadPost === null || (int)$threadPost['parent'] !== 0 || (int)$threadPost['deleted'] !== 0 || !is_file($staticPath)) {
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
        $html = applyAdminNameField($html, $config);
    }
    echo $html;
    exit;
}

// ===== FUNCIONES AUXILIARES =====

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
    if ($diff < 60)        return 'Ahora';
    if ($diff < 3600)      return 'Hace ' . floor($diff / 60) . 'min';
    if ($diff < 86400)     return 'Hace ' . floor($diff / 3600) . 'h';
    if ($diff < 2592000)   return 'Hace ' . floor($diff / 86400) . 'd';
    if ($diff < 31536000)  return 'Hace ' . floor($diff / 2592000) . 'mes';
    return 'Hace ' . floor($diff / 31536000) . 'a';
}

/**
 * Muestra una página de error simple y termina la ejecución.
 */
function showError(array $config, string $message): void {
    $title    = htmlspecialchars($config['title']);
    $homeUrl  = siteUrl('index.html');

    $content = <<<HTML
    <div style="background:#fff;border:1px solid #800">
        <h3 style="margin:0 0 10px;padding:3px 10px;background:#FCA">Error</h3>
        <div style="text-align:center;margin:0 0 10px">
            <span class="error-message">$message</span>
            <br>
            [<a href="$homeUrl">Inicio</a>]
        </div>
    </div>
HTML;
    echo renderPage($title, $content, $config);
    exit;
}

/**
 * Formatea un número de bytes en una unidad legible para humanos.
 */
function formatBytes(int $bytes, int $precision = 2): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $pow   = min((int)floor(($bytes ? log($bytes) : 0) / log(1024)), count($units) - 1);
    return round(max($bytes, 0) / pow(1024, $pow), $precision) . ' ' . $units[$pow];
}

/**
 *  Aplica formato al texto de un comentario:
 *  >>citas, >greentext, <pinktext, **negrita**, ~cursiva~, _subrayado_
 */
/**
 * Aplica el formato visual del tablón al texto de un comentario.
 * Convierte citas, greentext, pinktext, negritas, cursivas y subrayados.
 */
function formatComment(string $text, array $allposts): string {
    $text = htmlspecialchars($text);

    // >>citas con enlace
    $text = preg_replace_callback('/&gt;&gt;(\d+)/', function ($m) use ($allposts) {
        $num = (int)$m[1];
        foreach ($allposts as $p) {
            if ((int)$p['num'] === $num) {
                $thread = $p['parent'] > 0 ? $p['parent'] : $p['num'];
                    return '<a href="' . threadUrl($thread) . '#p' . $num . '" class="quotelink">&gt;&gt;' . $num . '</a>';
            }
        }
        return '<span style="color:red;">&gt;&gt;' . $num . '</span>';
    }, $text);

    // Negrita: **texto**
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);

    // Cursiva: ~texto~
    $text = preg_replace('/~(.+?)~/s', '<em>$1</em>', $text);

    // Subrayado: _texto_
    $text = preg_replace('/_(.+?)_/s', '<u>$1</u>', $text);

    // >greentext y <pinktext — por línea
    $lines = explode('<br />', nl2br($text));
    foreach ($lines as &$line) {
        // Greentext: línea empieza con > (pero no >>)
        if (preg_match('/^&gt;[^>]/', $line)) {
            $line = '<span style="color:#789922;">' . $line . '</span>';
        // Pinktext: línea empieza con < (escapado como &lt;)
        } elseif (preg_match('/^&lt;/', $line)) {
            $line = '<span style="color:#E0609A;">' . $line . '</span>';
        }
    }
    return implode('<br/>', $lines);
}

/**
 * Organiza las publicaciones en hilos, agrupando cada respuesta bajo su publicación padre.
 */
function getThreads(array $posts): array {
    $threads = [];
    foreach ($posts as $p) {
        if ((int)$p['parent'] === 0 && (int)$p['deleted'] === 0) {
            $threads[$p['num']] = ['op' => $p, 'replies' => []];
        }
    }
    foreach ($posts as $p) {
        if ((int)$p['parent'] > 0 && isset($threads[$p['parent']])) {
            $threads[$p['parent']]['replies'][] = $p;
        }
    }
    $list = array_values($threads);
    usort($list, function ($a, $b) {
        $aPinned = (int)($a['op']['pinned'] ?? 0);
        $bPinned = (int)($b['op']['pinned'] ?? 0);
        if ($aPinned !== $bPinned) {
            return $bPinned <=> $aPinned;
        }
        $aTime = !empty($a['replies']) ? end($a['replies'])['now'] : $a['op']['now'];
        $bTime = !empty($b['replies']) ? end($b['replies'])['now'] : $b['op']['now'];
        return $bTime - $aTime;
    });
    return $list;
}

/** Footer simple reutilizable. */
/**
 * Genera el pie de página reutilizable que aparece al final de cada página.
 */
function footerHtml(array $config): string {
    $title = htmlspecialchars($config['title'] ?: 'Tenma');
    return '<div style="text-align:center;font-size:.9rem;color:#800;margin-top:8px;padding:4px 0;">' . $title . ' &mdash; Tablón anónimo para discusión, imágenes y debates breves</div>';
}

/**
 * Envuelve el contenido en una plantilla HTML completa con estilos y scripts comunes.
 */
function renderPage(string $title, string $content, array $config, array $nav = []): string {
    $navhtml = '';
    if (!empty($nav)) {
        $navhtml = '<div class="nav">';
        foreach ($nav as $href => $label) {
            $navhtml .= '[<a href="' . $href . '">' . $label . '</a>] ';
        }
        $navhtml .= '</div>';
    }

    $footer = footerHtml($config);
    $threadBaseUrl = siteUrl('threads/');
    $faviconUrl = siteUrl('favicon.ico');
    $styleUrl = siteUrl('style.css');
    $scriptUrl = siteUrl('tenma.js');

    return <<<HTML
    <!DOCTYPE html>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <html lang="es">
    <head>
    <title>$title</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="$faviconUrl">
    <link rel="stylesheet" href="$styleUrl">
    </head>
    <body>
    <div class="container">
    $content
    <hr>
    $navhtml
    $footer
    </div>
    <script>window.TENMA_THREAD_BASE_URL = "$threadBaseUrl";</script>
    <script src="$scriptUrl"></script>
    </body>
    </html>
    HTML;
}

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
    button, input, select, textarea { font-size: 1rem }
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
    input[type=text], input[type=password], textarea { font-family:sans-serif; padding:.2em; border: 1px solid var(--border-color) }
    .post-form ::placeholder { opacity: 0 }
    .post-form-toggle { padding: 0; border: 0; background: none; color: #0000EE; font: inherit; font-weight: bold; cursor: pointer }
    .post-form-toggle:hover { color: var(--link-hover-color) }

    /* ===== Publicaciones ===== */
    .fileinfo  { font-size: .92rem; margin-bottom: 5px }
    .quotelink { font-size: .92rem; }
    .post-media { float: left; margin: 0 15px 5px 0 }
    .post-media img { display: block; height: auto }
    .postheader { font-size: .92rem; display: contents }
    .postheader .subject { color: var(--post-subject-color); font-weight: bold }
    .postactions { font-size: .92rem }
    .thread-status-icons { display: inline-flex; align-items: center; gap: 2px; margin:0 5px; vertical-align: top }
    .thread-status-icons img { width: 16px; height: 16px; vertical-align: middle }
    .post-checkbox { margin: 0 5px 0 0; vertical-align: middle }
    .op-post { width: 100%; padding: 5px 0; overflow: visible }
    .reply-post { display: block; padding: 5px; margin: 0 0 4px 0; border: 1px solid var(--post-border); background-color: var(--post-background); overflow: visible; max-width: fit-content; }
    .post-highlight { animation: post-highlight 3s ease-in-out forwards; }
    @keyframes post-highlight {
        0% { background-color: var(--post-background); }
        20% { background-color: #F0C0B0; }
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
    .delete { text-align: right; margin: 5px 0; }
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
    .post-menu summary { display: inline-block; width: 16px; cursor: pointer; color: var(--text-color); list-style: none }
    .post-menu summary::-webkit-details-marker { display: none }
    .post-menu summary::before { content: '▶'; }
    .post-menu[open] summary::before { content: '▼'; }
    .post-menu summary:hover { color: var(--link-hover-color); }
    .post-menu-content { position: absolute; z-index: 10; top: 1.35em; left: 0; min-width: 115px; padding: 0; background: var(--menu-background); border: 1px solid var(--menu-border); text-align: left }
    .post-menu-content a, .post-menu-content button, .post-menu-label { display: block; width: 100%; padding: 3px 6px; border: 0; background: transparent; color: var(--text-color); font: inherit; text-align: left; white-space: nowrap; cursor: pointer }
    .post-menu-content a:hover, .post-menu-content button:hover, .post-menu-label:hover { background: var(--menu-hover-background); color: var(--link-hover-color); }
    .post-menu-submenu { display: none; position: absolute; top: 44px; left: 100%; min-width: 87px; padding: 0; background: var(--menu-background); border: 1px solid var(--menu-border); }
    .post-menu-submenu.flip { left: auto; right: 100%; }
    .post-menu-label:hover .post-menu-submenu, .post-menu-label:focus-within .post-menu-submenu { display: block; }
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
    .admin-post-controls { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 5px 0; font-size: .9rem }
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
 * Genera el script cliente compartido por todas las páginas del tablón.
 * La base de enlaces de hilos ($threadBaseUrl) se inyecta aparte como
 * window.TENMA_THREAD_BASE_URL, ya que depende de la URL del sitio.
 */
function generateScript(): string {
    return <<<'JS'
    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('.post-form-toggle');
        if (!toggle) return;

        var formContainer = document.getElementById(toggle.getAttribute('aria-controls'));
        if (!formContainer) return;

        var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!isExpanded));
        formContainer.hidden = isExpanded;
        toggle.closest('h3').hidden = !isExpanded;
    });

    function quotePost(num) {
        var allTextareas = document.querySelectorAll('textarea[name="com"]');

        // Si no hay ningún textarea, ir al hilo
        if (allTextareas.length === 0) {
            window.location.href = window.TENMA_THREAD_BASE_URL + num + '/?quote=' + num;
            return;
        }

        // Si solo hay un textarea (estamos en el tablón), ir al hilo
        if (allTextareas.length === 1) {
            var ta = allTextareas[0];
            var form = ta.closest('form');
            var isCreateForm = form && form.querySelector('input[name="subject"]');
            if (isCreateForm) {
                window.location.href = window.TENMA_THREAD_BASE_URL + num + '/?quote=' + num;
                return;
            }
        }

        // Estamos en el hilo, agregar la cita al último textarea
        var ta = allTextareas[allTextareas.length - 1];
        ta.focus();
        var quote = '>>' + num + '\n';
        ta.value += quote;
        ta.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }

    function tenmaHighlightPostFromHash() {
        var match = window.location.hash.match(/^#p(\d+)$/);
        if (!match) return;

        var post = document.getElementById('p' + match[1]);
        if (!post) return;

        post.classList.remove('post-highlight');
        void post.offsetWidth;
        post.classList.add('post-highlight');
    }

    window.addEventListener('load', tenmaHighlightPostFromHash);
    window.addEventListener('hashchange', tenmaHighlightPostFromHash);
    document.addEventListener('click', function(event) {
        var link = event.target.closest('a.quotelink');
        if (link && link.pathname === window.location.pathname && link.hash === window.location.hash) {
            tenmaHighlightPostFromHash();
        }
    });

    // Al cargar la página, agregar la cita si viene en la URL
    window.addEventListener('load', function() {
        var url = window.location.href;
        var quoteMatch = url.match(/[?&]quote=(\d+)/);
        if (quoteMatch) {
            var quoteNum = quoteMatch[1];
            var ta = document.querySelector('textarea[name="com"]');
            if (ta && !ta.value.includes('>>' + quoteNum)) {
                ta.value = '>>' + quoteNum + '\n' + ta.value;
                ta.focus();
            }
        }
    });

    function tenmaUpdateTimes() {
        var nodes = document.querySelectorAll('[data-ts]');
        var now = Math.floor(Date.now() / 1000);
        for (var i = 0; i < nodes.length; i++) {
            var ts = parseInt(nodes[i].getAttribute('data-ts'), 10);
            if (!ts) continue;
            var diff = Math.max(0, now - ts);
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
            nodes[i].textContent = text;
        }
    }

    function tenmaHiddenPosts() {
        try {
            var hidden = JSON.parse(localStorage.getItem('tenma-hidden-posts') || '[]');
            return Array.isArray(hidden) ? hidden : [];
        } catch (error) {
            return [];
        }
    }

    function tenmaApplyHiddenPosts() {
        var hidden = tenmaHiddenPosts();
        var nodes = document.querySelectorAll('[data-post-id]');
        for (var i = 0; i < nodes.length; i++) {
            var postId = nodes[i].getAttribute('data-post-id');
            var isHidden = hidden.indexOf(postId) !== -1;
            var placeholder = nodes[i].previousElementSibling;
            if (isHidden) {
                nodes[i].style.display = 'none';
                if (!placeholder || placeholder.getAttribute('data-hidden-post-id') !== postId) {
                    placeholder = document.createElement('div');
                    placeholder.className = 'hidden-post-placeholder';
                    placeholder.setAttribute('data-hidden-post-id', postId);
                    placeholder.innerHTML = 'Publicación oculta - <a href="#" onclick="tenmaShowHiddenPost(' + postId + '); return false;">Mostrar</a>';
                    nodes[i].parentNode.insertBefore(placeholder, nodes[i]);
                }
            } else {
                nodes[i].style.display = '';
                if (placeholder && placeholder.getAttribute('data-hidden-post-id') === postId) {
                    placeholder.remove();
                }
            }
        }
    }

    function tenmaHidePost(postId) {
        var hidden = tenmaHiddenPosts();
        postId = String(postId);
        if (hidden.indexOf(postId) === -1) hidden.push(postId);
        try {
            localStorage.setItem('tenma-hidden-posts', JSON.stringify(hidden));
        } catch (error) {}
        tenmaApplyHiddenPosts();
    }

    function tenmaShowHiddenPost(postId) {
        var hidden = tenmaHiddenPosts().filter(function(id) {
            return id !== String(postId);
        });
        try {
            localStorage.setItem('tenma-hidden-posts', JSON.stringify(hidden));
        } catch (error) {}
        tenmaApplyHiddenPosts();
    }

    document.querySelectorAll('.post-menu-label').forEach(function(label) {
        label.addEventListener('mouseenter', function() {
            var submenu = label.querySelector('.post-menu-submenu');
            if (!submenu) return;
            submenu.classList.remove('flip');
            window.setTimeout(function() {
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
    setInterval(tenmaUpdateTimes, 30000);
    JS;
}

/**
 * Genera la página de error 404 para hilos o recursos ya eliminados.
 */
function generateErrorPage(array $config): string {
    $title   = ($config['title'] ?: 'Tenma') . ' - No encontrado';
    $content = '<div style="background:#fff;border:1px solid #800">
        <h3 style="margin:0 0 10px;padding:3px 10px;background:#FCA">Error 404</h3>
        <div style="text-align:center;margin:0 0 10px">
            <p>El hilo o la imagen que buscas ya no existe.<br>Puede que haya sido eliminado por el administrador o eliminado automáticamente por antigüedad.</p>
            <br>
            [<a href="' . siteUrl('index.html') . '">Volver</a>]
        </div>
    </div>';
    return renderPage($title, $content, $config);
}

// ── Bloque de imagen para posts ─────────────────────────────────────────────
/**
 * Renderiza el bloque de imagen de una publicación, con vista previa y enlace al archivo.
 */
function renderImageBlock(array $post, bool $large, array $config): string {
    if (empty($post['image'])) return '';
    $path = siteUrl($config['uploadfolder'] . $post['image']);
    $diskPath = $config['uploadfolder'] . $post['image'];
    if (!file_exists($diskPath)) return '';

    $size     = formatBytes((int)$post['filesize']);
    $dims     = @getimagesize($diskPath);
    $dimstext = $dims ? $dims[0] . 'x' . $dims[1] : '';
    $maxcss   = $large ? 'max-width:250px;max-height:250px;' : 'max-width:150px;max-height:150px;';
    $extensionLabel = strtoupper(pathinfo($post['image'], PATHINFO_EXTENSION));
    $fileinfo = '<div class="fileinfo desktop-fileinfo">Archivo: <a href="' . $path . '" target="_blank">'
              . htmlspecialchars($post['image']) . '</a>'
              . ' (' . $size . ($dimstext !== '' ? ', ' . $dimstext : '') . ')</div>';
    $img      = '<div class="post-media">'
              . '<a href="' . $path . '" target="_blank">'
              . '<img src="' . $path . '" style="' . $maxcss . '" alt="imagen">'
              . '<span class="mobile-file-info mobile">' . $size . ' ' . $extensionLabel . '</span>'
              . '</a></div>';
    return $fileinfo . $img;
}

function renderThreadStatusIcons(array $post): string {
    $icons = '';
    if (!empty($post['pinned'])) {
        $icons .= '<img src="' . siteUrl('thread-pin.gif') . '" alt="Hilo fijado" title="Hilo fijado">';
    }
    if (!empty($post['locked'])) {
        $icons .= '<img src="' . siteUrl('thread-lock.gif') . '" alt="Hilo bloqueado" title="Hilo bloqueado">';
    }
    return $icons !== '' ? '<span class="thread-status-icons">' . $icons . '</span>' : '';
}

function renderPostAuthor(array $post, array $config): string {
    $name = htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if (empty($post['author_admin'])) {
        return '<span style="color:' . $config['posternamecolor'] . ';font-weight:bold;">' . $name . '</span>';
    }
    return '<span style="color:#f00;font-weight:bold;">' . $name . '</span> '
        . '<img src="' . siteUrl('admin.gif') . '" alt="Administrador" title="Administrador" width="16" height="16" style="vertical-align:top;">';
}

function renderPostBacklinks(array $post, array $allposts): string {
    $num = (int)$post['num'];
    $backlinks = [];
    foreach ($allposts as $referencingPost) {
        $referencingNum = (int)$referencingPost['num'];
        if ($referencingNum === $num || (int)$referencingPost['deleted'] > 0) {
            continue;
        }
        if (!preg_match_all('/>>(\d+)/', $referencingPost['comment'], $matches)) {
            continue;
        }
        if (!in_array((string)$num, $matches[1], true)) {
            continue;
        }
        $referencingThread = (int)$referencingPost['parent'] > 0
            ? (int)$referencingPost['parent']
            : $referencingNum;
        $backlinks[$referencingNum] = '<a class="quotelink post-backlink" href="' . threadUrl($referencingThread)
            . '#p' . $referencingNum . '" title="Respuesta que cita esta publicación">&gt;&gt;' . $referencingNum . '</a>';
    }
    return $backlinks !== []
        ? '<span class="post-backlinks"> ' . implode(' ', $backlinks) . '</span>'
        : '';
}

function renderMobilePostInfo(array $post, array $allposts, int $threadnum, bool $isop, array $config, bool $showCheckbox = true, bool $adminView = false): string {
    $num       = (int)$post['num'];
    $name      = renderPostAuthor($post, $config);
    $subject   = htmlspecialchars($post['subject'] ?? '');
    $subject   = $isop && $subject !== '' ? '<span class="mobile-subject">' . $subject . '</span>' : '';
    $statusIcons = $isop ? renderThreadStatusIcons($post) : '';
    $threadUrl = threadUrl($threadnum);
    $postMenu  = $adminView ? '' : renderPostMenu($post, $threadnum, $config);
    $lockedPost = $isop && !empty($post['locked']);
    $postNumber = $adminView || $lockedPost
        ? 'No.' . $num
        : '<a href="' . $threadUrl . '#p' . $num . '">No.</a><a href="' . $threadUrl . '#q' . $num . '" onclick="quotePost(' . $num . '); return false;">' . $num . '</a>';
    $postBacklinks = renderPostBacklinks($post, $allposts);
    $checkbox = $showCheckbox
        ? '<input class="post-checkbox" type="checkbox" name="delete_posts[]" value="' . $num . '" aria-label="Seleccionar publicación">'
        : '';

    return '<div class="mobile mobile-post-info">'
         . $checkbox
         . $postMenu
         . '<span class="name-block">' . $name . ' ' . $subject . '</span>'
         . '<span class="mobile-date">' . htmlspecialchars($post['time']) . '<span class="mobile-number">'
         . $postNumber
         . $postBacklinks
         . $statusIcons
         . '</span></span></div>';
}

/**
 * Crea el menú de acciones para una publicación concreta.
 */
function renderPostMenu(array $post, int $threadnum, array $config): string {
    $num       = (int)$post['num'];
    $reportUrl = siteUrl($config['tenmafile']) . '?mode=report&num=' . $num;
    $reportLink = !empty($post['deleted']) || !empty($post['pinned']) || !empty($post['locked']) || !empty($post['author_admin'])
        ? ''
        : '<a href="' . htmlspecialchars($reportUrl, ENT_QUOTES, 'UTF-8') . '">Reportar</a>';
    $menu      = '<details class="post-menu"><summary title="Más acciones"></summary><div class="post-menu-content">'
               . $reportLink
               . '<button type="button" onclick="tenmaHidePost(' . $num . ')">Ocultar</button>';

    if (!empty($post['image']) && file_exists($config['uploadfolder'] . $post['image'])) {
        $imagePath = siteUrl($config['uploadfolder'] . $post['image']);
         $menu .= '<div class="post-menu-label">Buscar imágen »<div class="post-menu-submenu">'
             . '<a href="https://www.google.com/searchbyimage?image_url=' . rawurlencode($imagePath) . '" target="_blank" rel="noopener">Google</a>'
             . '<a href="https://yandex.com/images/search?rpt=imageview&url=' . rawurlencode($imagePath) . '" target="_blank" rel="noopener">Yandex</a>'
             . '<a href="https://saucenao.com/search.php?url=' . rawurlencode($imagePath) . '" target="_blank" rel="noopener">SauceNAO</a>'
             . '</div></div>';
    }

    return ' ' . $menu . '</div></details>';
}

/**
 * Renderiza una publicación de tipo OP (post inicial de un hilo).
 */
function renderOP(array $post, array $allposts, bool $isthread, bool $summarize, array $config, bool $showCheckbox = true, bool $adminView = false): string {
    $postMenu = $adminView ? '' : renderPostMenu($post, (int)$post['num'], $config);

    if ((int)$post['deleted'] > 0) {
        return '<div id="p' . $post['num'] . '" class="op-post" data-post-id="' . $post['num'] . '" style="background-color:' . $config['postbackground'] . ';">'
             . '<i>' . $config['deletionphrase'] . '</i>'
             . '<span class="postactions">' . $postMenu . '</span>'
             . '</div>';
    }

    $name     = renderPostAuthor($post, $config);
    $subject  = htmlspecialchars($post['subject'] ?? '');
    $statusIcons = renderThreadStatusIcons($post);
    $subjHtml = $subject !== '' ? '<span class="subject">' . $subject . '</span> ' : '';

    $comment = $post['comment'];
    if ($summarize && mb_strlen($comment) > 150) {
        $comment = mb_substr($comment, 0, 150) . '...';
    }
    $comment   = formatComment($comment, $allposts);
    $imageHtml = renderImageBlock($post, true, $config);
    $mobileInfo = renderMobilePostInfo($post, $allposts, (int)$post['num'], true, $config, $showCheckbox, $adminView);
    $replyLink = !$isthread && empty($post['locked'])
        ? ' [<a href="' . threadUrl((int)$post['num']) . '">Responder</a>]' : '';
    $replyCount = count(array_filter($allposts, fn($p) => (int)$p['parent'] === (int)$post['num']));
    $imageCount = count(array_filter($allposts, fn($p) => (int)$p['parent'] === (int)$post['num'] && !empty($p['image'])));
    $mobileLink = !$isthread
        ? '<div class="mobile mobile-post-link"><span class="info">' . $replyCount . ' respuestas / ' . $imageCount . ' imágenes</span><a class="button" href="' . threadUrl((int)$post['num']) . '">Ver hilo</a></div>'
        : '';
    $checkbox = $showCheckbox
        ? ' <input class="post-checkbox" type="checkbox" name="delete_posts[]" value="' . $post['num'] . '" aria-label="Seleccionar publicación">'
        : '';
    $lockedPost = !empty($post['locked']);
    $postNumber = $adminView || $lockedPost
        ? 'No.' . $post['num']
        : '<span class="postnum" onclick="quotePost(' . $post['num'] . ')" title="Responder citando esta publicación">No.' . $post['num'] . '</span>';
    $postBacklinks = renderPostBacklinks($post, $allposts);
    return '<div id="p' . $post['num'] . '" class="op-post" data-post-id="' . $post['num'] . '">'
         . $mobileInfo
         . $imageHtml
         . '<div class="postheader desktop">'
         . $checkbox
         . $subjHtml
         . $name . ' '
         . '<span>' . $post['time'] . '</span> '
         . $postNumber
         . $statusIcons
         . '<span class="postactions">' . $replyLink . $postMenu . '</span>'
         . $postBacklinks
         . '</div>'
         . '<div class="post-content mobile-post-content">' . $comment . '<br clear="all"></div>'
         . $mobileLink
         . '</div>';
}

/**
 * Renderiza una respuesta de hilo con su contenido, imagen y acciones.
 */
function renderReply(array $post, array $allposts, bool $summarize, array $config, bool $showCheckbox = true, bool $adminView = false): string {
    $postMenu = $adminView ? '' : renderPostMenu($post, (int)$post['parent'], $config);
    $isDeleted = (int)$post['deleted'] > 0;
    $name    = renderPostAuthor($post, $config);
    if ($isDeleted) {
        $comment = '<i>' . htmlspecialchars($config['deletionphrase'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</i>';
    } else {
        $comment = $post['comment'];
        if ($summarize && mb_strlen($comment) > 150) {
            $comment = mb_substr($comment, 0, 150) . '...';
        }
        $comment = formatComment($comment, $allposts);
    }
    $imageHtml = renderImageBlock($post, false, $config);
    $mobileInfo = renderMobilePostInfo($post, $allposts, (int)$post['parent'], false, $config, $showCheckbox, $adminView);
    $checkbox = $showCheckbox
        ? ' <input class="post-checkbox" type="checkbox" name="delete_posts[]" value="' . $post['num'] . '" aria-label="Seleccionar publicación">'
        : '';
    $postNumber = $adminView
        ? 'No.' . $post['num']
        : '<span class="postnum" onclick="quotePost(' . $post['num'] . ')" title="Responder citando esta publicación">No.' . $post['num'] . '</span>';
    $postBacklinks = renderPostBacklinks($post, $allposts);

    return '<div id="p' . $post['num'] . '" class="reply-post" data-post-id="' . $post['num'] . '">'
         . $mobileInfo
         . $imageHtml
         . '<div class="postheader desktop">'
         . $checkbox
         . $name . ' '
         . '<span>' . $post['time'] . '</span> '
         . $postNumber
         . '<span class="postactions">' . $postMenu . '</span>'
         . $postBacklinks
         . '</div>'
         . '<div class="post-content mobile-post-content">' . $comment . '<br clear="all"></div>'
         . '</div>';
}

// ───────────────────────────────────────────────────────────────────────────
/**
 * Regenera todas las páginas estáticas del tablón a partir de las publicaciones actuales.
 */
function buildPages(array $posts, array $config): void {
    $threadlist = getThreads($posts);
    $threadlist = array_slice($threadlist, 0, $config['postsperpage'] * $config['maxpages']);
    $totalpages = max(1, (int)ceil(count($threadlist) / $config['postsperpage']));
    $pages      = array_chunk($threadlist, $config['postsperpage']) ?: [[]];

    foreach ($posts as $post) {
        if ((int)$post['parent'] !== 0 || (int)$post['deleted'] === 0) {
            continue;
        }
        $deletedThreadPath = threadFilePath((int)$post['num'], $config);
        if (is_file($deletedThreadPath) && !unlink($deletedThreadPath)) {
            throw new RuntimeException('No se pudo eliminar el archivo del hilo eliminado.');
        }
    }

    if (!is_dir($config['threaddir'])) {
        mkdir($config['threaddir'], 0755, true);
    }

    ensureThreadActionIcons();
    file_put_contents('index.html',              generateIndex($posts, $config),                     LOCK_EX);
    file_put_contents($config['rulesfile'],      generateRules($config),                             LOCK_EX);
    file_put_contents($config['helpfile'],       generateHelp($config),                              LOCK_EX);
    file_put_contents($config['errorfile'],      generateErrorPage($config),                         LOCK_EX);
    file_put_contents('favicon.ico',             generateFavicon(),                                  LOCK_EX);
    file_put_contents('robots.txt',              generateRobots(),                                   LOCK_EX);
    file_put_contents('sitemap.xml',             generateSitemap($threadlist, $config),              LOCK_EX);
    file_put_contents('style.css',               generateStyles($config),                            LOCK_EX);
    file_put_contents('tenma.js',                generateScript(),                                   LOCK_EX);

    foreach ($pages as $i => $pagethreads) {
        $filename = $i === 0 ? 'board.html' : 'board-' . $i . '.html';
        file_put_contents($filename, generateBoard($pagethreads, $i, $totalpages, $posts, $config), LOCK_EX);
    }
    foreach ($threadlist as $t) {
        file_put_contents(threadFilePath((int)$t['op']['num'], $config), generateThread($t, $posts, $config), LOCK_EX);
    }

    foreach (glob('board-*.html') as $f) {
        $idx = (int)str_replace(['board-', '.html'], '', $f);
        if ($idx >= $totalpages) @unlink($f);
    }
}

// ── Generadores de páginas estáticas ────────────────────────────────────────

/**
 * Genera la página de reglas del sitio.
 */
function generateRules(array $config): string {
    $title = ($config['title'] ?: 'Tenma') . ' - Reglas';
    $items = '';
    foreach ($config['rules'] as $rule) {
        $items .= '<li style="padding:3px 0;">' . htmlspecialchars((string)$rule) . '</li>';
    }
    $content = '<center><h1 style="margin-bottom:5px;">' . $title . '</h1></center><hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>]</div><hr>
<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . $config['section'] . ';">Reglas</h3>
<ul style="padding:0 0 10px 20px;list-style:disc;">' . $items . '</ul>
</div>';
    return renderPage($title, $content, $config);
}

/**
 * Genera la página de ayuda con ejemplos de formato para el usuario.
 */
function generateHelp(array $config): string {
    $title = ($config['title'] ?: 'Tenma') . ' - Ayuda';
    $rows  = '';
    foreach ($config['helps'] as $h) {
        $syntax  = htmlspecialchars($h['syntax']);
        $preview = $h['html'];   // HTML con estilo, ya es seguro (definido en config)
        $desc    = $h['desc'];   // ya contiene entidades HTML donde hace falta
        $rows .= '<div class="help-row">
<span class="help-syntax">' . $syntax . '</span>
<span class="help-preview">' . $preview . '</span>
<span class="help-desc">' . $desc . '</span>
</div>';
    }

    $content = '<center><h1 style="margin-bottom:5px;">' . $title . '</h1></center><hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>]</div><hr>
<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="padding:3px 10px;background:' . $config['section'] . ';">Formatos disponibles</h3>
<div class="help-list">' . $rows . '</div>
</div>';

    return renderPage($title, $content, $config);
}

/**
 * Genera la página principal con actividad reciente, galería de imágenes y estadísticas.
 */
function generateIndex(array $allposts, array $config): string {
    $title = $config['title'] ?: 'Tenma';

    $visiblePosts = array_values(array_filter($allposts, fn($post) => (int)$post['deleted'] === 0));
    $recent   = array_reverse(array_slice($visiblePosts, -10));
    $actItems = '';
    foreach ($recent as $p) {
        $thread  = $p['parent'] > 0 ? $p['parent'] : $p['num'];
        $preview = empty($p['comment']) && !empty($p['image'])
            ? '[imagen]'
            : htmlspecialchars(mb_substr($p['comment'], 0, 60)) . (mb_strlen($p['comment']) > 60 ? '...' : '');
        $name    = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $nameColor = !empty($p['author_admin']) ? '#f00' : $config['posternamecolor'];
        $ts      = (int)$p['now'];
        $reltime = relativeTime($ts);
        $actItems .= '<li><small style="color:#888;float:inline-end;margin-right:1em;" data-ts="' . $ts . '">(' . $reltime . ')</small><b style="color:' . $nameColor . '">' . $name . '</b>: ' . $preview . ' <a href="' . threadUrl($thread) . '#p' . $p['num'] . '">>></a></li>';
    }
    $activity = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . $config['section'] . ';">Última actividad</h3>
<ul style="margin:0;padding:0 0 10px 10px;">' . $actItems . '</ul></div>';

    $images = array_slice(array_reverse(array_values(array_filter(
        $allposts,
        fn($p) => !empty($p['image']) && (int)$p['deleted'] === 0 && file_exists($config['uploadfolder'] . $p['image'])
    ))), 0, 8);

    $gallery = '';
    if (!empty($images)) {
        $thumbs = '';
        foreach ($images as $img) {
            $thread  = $img['parent'] > 0 ? $img['parent'] : $img['num'];
            $postUrl = threadUrl($thread) . '#p' . $img['num'];
            $label = trim((string)($img['subject'] ?? ''));
            if ($label === '') $label = (string)$img['name'];
            $comment = trim((string)$img['comment']);
            $preview = $comment !== '' ? mb_substr($comment, 0, 120) . (mb_strlen($comment) > 120 ? '...' : '') : '';
            $thumbs .= '<a class="index-image-card" href="' . $postUrl . '">
<img src="' . siteUrl($config['uploadfolder'] . $img['image']) . '" alt="Imagen de la publicación No.' . (int)$img['num'] . '">
<span class="index-image-caption"><b>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b>'
                . ($preview !== '' ? ' ' . htmlspecialchars($preview, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '')
                . '</span></a>';
        }
        $gallery = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . $config['section'] . ';">Imágenes recientes</h3>
<div class="index-image-gallery">' . $thumbs . '</div></div>';
    }

    $totalPosts  = count($allposts);
    $totalImages = count(array_filter($allposts, fn($p) => !empty($p['image'])));
    $totalSize   = array_sum(array_map(fn($p) => (int)$p['filesize'], $allposts));
    $stats = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . $config['section'] . ';">Estadísticas</h3>
<div style="font-size:.9rem;padding:0 0 10px 10px;display:flex;flex-wrap:wrap;justify-content:center;gap:15px;text-align:center;">
<span><b>Publicaciones:</b> ' . $totalPosts . '</span>
<span><b>Imágenes:</b> ' . $totalImages . '</span>
<span><b>Contenido:</b> ' . formatBytes($totalSize) . '</span>
</div></div>';

    $content = '<center style="margin-bottom:5px;"><h1>' . $title . '</h1></center><hr>
<div class="nav">[<a href="' . siteUrl('board.html') . '">Tablón</a>] [<a href="' . siteUrl($config['rulesfile']) . '">Reglas</a>] [<a href="' . siteUrl($config['helpfile']) . '">Ayuda</a>]</div><hr>
' . $activity . $gallery . $stats . '
<hr><div class="nav" style="float:right;">[<a href="' . siteUrl($config['tenmafile']) . '?mode=manage">Admin</a>]</div>
<br clear="all">';

    return renderPage($title, $content, $config);
}

/**
 * Genera una página del tablón mostrando los hilos más recientes y el formulario de creación.
 */
function generateBoard(array $threads, int $pagenumber, int $totalpages, array $allposts, array $config): string {
    $title    = ($config['title'] ?: 'Tenma') . ' - Tablón';
    $subtitle = $config['subtitle'];
    $notice = '<hr>';
    $noticeMessage = getSiteSetting($config, 'notice_message');
    if (getSiteSetting($config, 'notice_enabled') === '1' && trim($noticeMessage) !== '') {
        $noticeMessage = str_replace(["\r\n", "\r"], "\n", trim($noticeMessage));
        $notice = '<hr><h2 style="color:#f00;text-align:center;margin:.3em 0;">'
            . htmlspecialchars($noticeMessage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            . '</h2><hr>';
    }

    $namefield = $config['forcedanonymity']
        ? '<input type="text" name="name" size="28" value="' . $config['defaultname'] . '" disabled autocomplete="off" placeholder="Nombre">'
        : '<input type="text" name="name" size="28" autocomplete="off" placeholder="Nombre">';

    // Formulario sin bloque de ayuda de formato bajo el textarea
    $formhtml = '<center>
<h3 style="margin:.5em 0; font-size: 1.5em;">[<button class="post-form-toggle" type="button" aria-controls="new-thread-form" aria-expanded="false">Crear nueva publicación</button>]</h3>
<div id="new-thread-form" hidden>
<br>
<form class="post-form" method="POST" action="' . siteUrl($config['tenmafile']) . '" enctype="multipart/form-data">
<table class="post-form-table" style="margin:0 auto;border-spacing:1px;">
    <tbody>
        <tr>
            <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                <b style="margin:.3em;">Nombre</b></td>
            <td>' . $namefield . ' <input type="submit" value="Publicar" style="padding:.15em .3em"></td>
        </tr>
        <tr>
            <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                <b style="margin:.3em;">Asunto</b>
            </td>
            <td>
                <input type="text" name="subject" size="28" maxlength="' . $config['subjectlimit'] . '" autocomplete="off" placeholder="Asunto" required>
            </td>
        </tr>
        <tr>
            <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                <b style="margin:.3em;">Comentario</b>
            </td>
            <td>
                <textarea name="com" cols="48" rows="4" style="vertical-align:bottom" autocomplete="off" placeholder="Comentario" required></textarea>
            </td>
        </tr>
        <tr>
            <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                <b style="margin:.3em;">Imagen</b>
            </td>
            <td>
                <input type="file" name="image" accept="image/*" autocomplete="off" required>
            </td>
        </tr>
    </tbody>
</table>
<p style="font-size:.9rem">Puedes leer las <a href="' . siteUrl($config['rulesfile']) . '">reglas</a> y la <a href="' . siteUrl($config['helpfile']) . '">ayuda de formato</a>.</p>
</form><br></div></center>';

    $posthtml = '';
    foreach ($threads as $thread) {
        $op      = $thread['op'];
        $replies = $thread['replies'];

        $posthtml .= renderOP($op, $allposts, false, true, $config);

        $omitted        = 0;
        $omittedImages  = 0;
        $displayReplies = $replies;
        if (count($replies) > $config['maxreplieshown']) {
            $omitted        = count($replies) - $config['maxreplieshown'];
            $hidden         = array_slice($replies, 0, $omitted);
            $omittedImages  = count(array_filter($hidden, fn($r) => !empty($r['image'])));
            $displayReplies = array_slice($replies, -$config['maxreplieshown']);
        }
        if ($omitted > 0) {
            $imgTxt    = $omittedImages > 0 ? ' y ' . $omittedImages . ' imágenes' : '';
            $posthtml .= '<div class="omitted"><i>' . $omitted . ' respuestas' . $imgTxt
                       . ' omitidas. <a href="' . threadUrl((int)$op['num']) . '">Haz clic para ver</a>.</i></div>';
        }
        foreach ($displayReplies as $reply) {
            $posthtml .= renderReply($reply, $allposts, true, $config);
        }
        $posthtml .= '<hr style="clear:both;">';
    }

    $deleteButton = '<div class="delete"><button type="submit">Eliminar</button></div>';
    $lastSeparator = '<hr style="clear:both;">';
    if (substr($posthtml, -strlen($lastSeparator)) === $lastSeparator) {
        $posthtml = substr($posthtml, 0, -strlen($lastSeparator)) . $deleteButton . $lastSeparator;
    }
    $pagehtml = paginationHtml($pagenumber, $totalpages, 'board', $config);

    $content = '<div class="board-column"><center style="margin-bottom:5px"><h1>' . $title . '</h1><h3>' . $subtitle . '</h3></center><hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>]</div><hr>
' . $formhtml . $notice . '<form method="POST" action="' . siteUrl($config['tenmafile']) . '" onsubmit="return confirm(\'¿Eliminar las publicaciones seleccionadas?\');">'
 . '<input type="hidden" name="return_to" value="' . siteUrl('board.html') . '">'
 . $posthtml . $pagehtml . '</form></div>';

    return renderPage($title, $content, $config);
}

/**
 * Genera la página completa de un hilo con su OP y sus respuestas.
 */
function generateThread(array $thread, array $allposts, array $config): string {
    $op        = $thread['op'];
    $threadnum = $op['num'];
    $title     = ($config['title'] ?: 'Tenma') . ' - Publicación #' . $threadnum;

    $posthtml = renderOP($op, $allposts, true, false, $config);
    foreach ($thread['replies'] as $reply) {
        $posthtml .= renderReply($reply, $allposts, false, $config);
    }

    $namefield = $config['forcedanonymity']
        ? '<input type="text" name="name" size="28" value="' . $config['defaultname'] . '" disabled autocomplete="off" placeholder="Nombre">'
        : '<input type="text" name="name" size="28" autocomplete="off" placeholder="Nombre">';

    // Formulario de respuesta sin bloque de ayuda bajo el textarea
    $replyForm = '<center>
<h3 style="margin:.5em 0">Responder al hilo</h3>
<form class="post-form" method="POST" action="' . siteUrl($config['tenmafile']) . '" enctype="multipart/form-data" style="margin:10px 0;">
<input type="hidden" name="parent" value="' . $threadnum . '">
<table class="post-form-table" style="margin:0 auto;border-spacing:1px;">
    <tbody>
        <tr>
            <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                <b style="margin:.3em;">Nombre</b>
            </td>
            <td>' . $namefield . ' <input type="submit" value="Responder" style="padding:.15em .3em"></td>
        </tr>
            <tr>
                <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                    <b style="margin:.3em;">Comentario</b>
                </td>
            <td>
                <textarea name="com" cols="48" rows="4" style="vertical-align:bottom" autocomplete="off" placeholder="Comentario"></textarea>
            </td>
        </tr>
        <tr>
            <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;">
                <b style="margin:.3em;">Imagen</b>
            </td>
            <td><input type="file" name="image" accept="image/*" autocomplete="off"> <small>(Máx 3MB)</small></td>
        </tr>
    </tbody>
</table>
<p style="font-size:.9rem">Puedes leer las <a href="' . siteUrl($config['rulesfile']) . '">reglas</a> y la <a href="' . siteUrl($config['helpfile']) . '">ayuda de formato</a>.</p>
</form>
</center>';
    $replySection = !empty($op['locked'])
        ? '<p style="text-align:center;color:#f00;font-size:1.4rem;font-weight:bold;margin:2em 0;">Hilo cerrado.<br>No se permite responder en este momento.</p>'
        : $replyForm;
    $deleteForm = '<div class="delete"><button type="submit">Eliminar</button></div>';
    $content = '<div class="board-column"><center><h1 style="margin-bottom:5px;">' . $title . '</h1></center>
<hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>] [<a href="' . siteUrl('board.html') . '">Tablón</a>]</div>
<hr>
' . $replySection . '<hr>
<form method="POST" action="' . siteUrl($config['tenmafile']) . '" onsubmit="return confirm(\'¿Eliminar las publicaciones seleccionadas?\');">'
 . '<input type="hidden" name="return_to" value="' . threadUrl((int)$threadnum) . '">'
 . $posthtml . $deleteForm . '</form></div>';

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
 * Genera el contenido del archivo robots.txt para indexación básica.
 */
function generateRobots(): string {
    return "User-agent: *\nAllow: /\n\nSitemap: sitemap.xml\n";
}

/**
 * Genera un sitemap.xml con las URLs principales del sitio y los hilos existentes.
 */
function generateSitemap(array $threads, array $config): string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!is_string($host) || $host === '' || preg_match('/[\x00-\x20\/\\\\<>"]/', $host)) {
        $host = $_SERVER['SERVER_NAME'] ?? 'localhost';
    }
    if (!is_string($host) || $host === '' || preg_match('/[\x00-\x20\/\\\\<>"]/', $host)) {
        $host = 'localhost';
    }
    $scheme = isHttps($config) ? 'https://' : 'http://';
    $base = htmlspecialchars(
        rtrim($scheme . $host . siteBasePath(), '/'),
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );

    $urls = ['index.html', 'board.html', $config['rulesfile'], $config['helpfile']];
    $xml  = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
    foreach ($urls as $page) {
        $xml[] = '<url><loc>' . $base . '/' . htmlspecialchars($page, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc></url>';
    }
    foreach ($threads as $t) {
        $xml[] = '<url><loc>' . $base . '/threads/' . (int)$t['op']['num'] . '/</loc></url>';
    }
    $xml[] = '</urlset>';
    return implode("\n", $xml);
}

/**
 * Construye el bloque de paginación para el tablón o el panel de administración.
 */
function paginationHtml(int $current, int $total, string $base, array $config, string $extraqs = ''): string {
    $qs = $extraqs !== '' ? '&' . $extraqs : '';

    if ($base === 'board') {
        $href = fn(int $i) => $i === 0 ? siteUrl('board.html') : siteUrl('board-' . $i . '.html');
    } else {
        $href = fn(int $i) => siteUrl($config['tenmafile']) . '?mode=manage&page=' . $i . $qs;
    }

    // Números de todas las páginas
    $nums = '';
    for ($i = 0; $i < $total; $i++) {
        if ($i === $current) {
            $nums .= '<td style="padding:2px 3px;text-align:center;">[' . $i . ']</td>';
        } else {
            $nums .= '<td style="padding:2px 3px;text-align:center;">[<a href="' . $href($i) . '" style="text-decoration:none;color:#0000ee;">' . $i . '</a>]</td>';
        }
    }

    return '<table align="center" style="border-collapse:collapse;margin:10px 0;background-color:#F0E0D6"><tbody><tr>' . $nums . '</tr></tbody></table>';
}

// ===== MODO REPORTE =====
$mode = $_GET['mode'] ?? '';

if ($mode === 'report') {
    $numValue = $_GET['num'] ?? $_POST['num'] ?? '0';
    if (!is_string($numValue) || !ctype_digit($numValue) || (int)$numValue <= 0) {
        showError($config, 'Publicación inválida.');
    }
    $num = (int)$numValue;
    $post = getPostByNum($config, $num);
    if ($post === null) showError($config, 'La publicación no existe.');
    if ((int)$post['deleted'] !== 0) showError($config, 'La publicación ya fue eliminada.');
    if (!empty($post['pinned']) || !empty($post['locked']) || !empty($post['author_admin'])) {
        showError($config, 'No se pueden reportar publicaciones del administrador, fijadas o bloqueadas.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reason = $_POST['reason'] ?? '';
        if (!is_string($reason)) {
            showError($config, 'El motivo del reporte no es válido.');
        }
        $reason = trim($reason);
        if ($reason === '') showError($config, 'Debes indicar un motivo de reporte.');
        insertReport($config, $num, mb_substr($reason, 0, 500));
        $content = '<center><h1 style="margin-bottom:5px;">Reportar publicación No.' . $num . '</h1><hr><br><h2>Gracias</h2><br><p>Tu reporte para la publicación No.' . $num . ' fue enviado al equipo de moderación.</p></center><br>';
        echo renderPage($config['title'] ?: 'Tenma', $content, $config, ['index.html' => 'Inicio']);
        exit;
    }

    $title   = $config['title'] ?: 'Tenma';
    $content = '<center><h1 style="margin-bottom:5px;">Reportar publicación No.' . $num . '</h1><hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>]</div><hr>
<br>
<p>Antes de realizar un reporte asegurate de que la publicación si infrinja las <a href="' . siteUrl('rules.html') . '">reglas</a>, caso contrario no lo hagas.</p><br>
<form method="POST" action="' . siteUrl($config['tenmafile']) . '?mode=report&num=' . $num . '">
<textarea name="reason" cols="48" rows="4" placeholder="Motivo del reporte" style="display:block;margin:0 auto 8px;" autocomplete="off"></textarea>
<input type="submit" value="Enviar reporte" style="padding: 0 .3em">
</form>
<br></center>';
    echo renderPage($title, $content, $config);
    exit;
}

// ===== ELIMINACIÓN DEL PROPIO USUARIO =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_posts'])) {
    $postnums = is_array($_POST['delete_posts']) ? $_POST['delete_posts'] : [$_POST['delete_posts']];
    foreach ($postnums as $postnum) {
        if (!is_string($postnum) || !ctype_digit($postnum) || (int)$postnum <= 0) {
            continue;
        }
        $num  = (int)$postnum;
        $post = getPostByNum($config, $num);
        if ($post !== null && (int)$post['deleted'] === 0
            && matchesClientIpHash((string)$post['postiphash'], $hashedip, $legacyhashedip)) {
            deletePostCompletely($config, $num);
        }
    }
    buildPages(readPosts($config), $config);
    $returnTo = $_POST['return_to'] ?? siteUrl('board.html');
    if (!is_string($returnTo) || $returnTo === '' || $returnTo[0] !== '/'
        || substr($returnTo, 0, 2) === '//' || strpos($returnTo, '\\') !== false
        || preg_match('/[\x00-\x1F\x7F]/', $returnTo)) {
        $returnTo = siteUrl('board.html');
    }
    header('Location: ' . $returnTo);
    exit;
}

// ===== MODO ADMINISTRACIÓN =====
if ($mode === 'manage') {
    $sessiontoken = adminSessionToken($config);
    $csrfToken    = generateAdminCsrfToken($config);
    $canmanage    = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['managepassword'])) {
        if (!verifyAdminCsrfToken($config, $_POST['csrf_token'] ?? '')) {
            showError($config, 'La sesión de administración ha caducado. Recarga la página e inténtalo de nuevo.');
        }

        checkLoginRateLimit($config, $hashedip);

        $username = $_POST['manageusername'] ?? '';
        $password = $_POST['managepassword'] ?? '';
        $validuser = is_string($username) && hash_equals($config['adminusername'], trim($username));
        $validpass = is_string($password) && password_verify($password, $config['adminpasswordhash']);
        if ($validuser && $validpass) {
            clearLoginAttempts($config, $hashedip);
            session_regenerate_id(true);
            setcookie($config['managecookie'], $sessiontoken, [
                'expires'  => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isHttps($config),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $canmanage = true;
        } else {
            registerFailedLogin($config, $hashedip);
            showError($config, 'Usuario o contraseña incorrectos.');
        }
    } elseif (isAdminAuthenticated($config)) {
        $canmanage = true;
    }

    if (!$canmanage) {
        $title = ($config['title'] ?: 'Tenma') . ' - Admin';

        $content = '<center><h1 style="margin-bottom:5px;">' . htmlspecialchars($config['title'] ?: 'Tenma') . '</h1></center>
<hr>
<center>
<h3 style="margin:.5em 0;">Acceso de administrador</h3>
<form method="POST" action="?mode=manage" style="margin:10px 0;">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<table style="margin:0 auto;border-spacing:1px;"><tbody>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Usuario</b></td>
  <td><input type="text" name="manageusername" size="28" autocomplete="username"></td>
</tr>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Contraseña</b></td>
  <td><input type="password" name="managepassword" size="28" autocomplete="current-password"> <input type="submit" value="Entrar" style="padding:.15em .3em"></td>
</tr>
</tbody></table>
</form>
</center>';
        echo renderPage($title, $content, $config, ['index.html' => 'Inicio']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['managepassword'])) {
        if (!verifyAdminCsrfToken($config, $_POST['csrf_token'] ?? '')) {
            showError($config, 'La sesión de administración ha caducado. Recarga la página e inténtalo de nuevo.');
        }

        $pageValue = $_POST['page'] ?? '0';
        $page = is_string($pageValue) && ctype_digit($pageValue) ? (int)$pageValue : 0;
        $tab = $_POST['tab'] ?? 'posts';
        $tab = is_string($tab) && in_array($tab, ['posts', 'reports', 'bans', 'settings'], true) ? $tab : 'posts';

        if (isset($_POST['logout'])) {
            // Revoca de inmediato TODAS las cookies de administrador emitidas
            // (incluida esta), útil si una cookie se filtró o para forzar el
            // cierre de sesión sin depender solo de borrar la cookie local.
            rotateAdminSessionSecret($config);
            setcookie($config['managecookie'], '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'domain'   => '',
                'secure'   => isHttps($config),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage');
            exit;
        }
        if (isset($_POST['togglelock'])) {
            file_exists($config['lockfile']) ? unlink($config['lockfile']) : file_put_contents($config['lockfile'], 'locked');
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=settings');
            exit;
        }
        if (isset($_POST['rebuild'])) {
            buildPages(readPosts($config), $config);
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=settings');
            exit;
        }
        if (isset($_POST['save_notice'])) {
            $noticeMessage = $_POST['notice_message'] ?? '';
            $noticeEnabled = $_POST['notice_enabled'] ?? '';
            if (!is_string($noticeMessage) || !is_string($noticeEnabled) || !in_array($noticeEnabled, ['', '1'], true)) {
                showError($config, 'El aviso no es válido.');
            }
            $noticeMessage = trim($noticeMessage);
            if (mb_strlen($noticeMessage) > $config['commentlimit']) {
                showError($config, 'El aviso no puede tener más de ' . $config['commentlimit'] . ' caracteres.');
            }
            setSiteSetting($config, 'notice_message', $noticeMessage);
            setSiteSetting($config, 'notice_enabled', $noticeEnabled);
            buildPages(readPosts($config), $config);
            header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=settings');
            exit;
        }
        if (isset($_POST['toggle_thread_lock']) || isset($_POST['toggle_thread_pin'])) {
            $action = isset($_POST['toggle_thread_lock']) ? 'toggle_thread_lock' : 'toggle_thread_pin';
            $threadValue = $_POST[$action];
            if (!is_string($threadValue) || !ctype_digit($threadValue) || (int)$threadValue <= 0) {
                showError($config, 'El hilo seleccionado no es válido.');
            }
            $threadNum = (int)$threadValue;
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
                || ($parts[1] !== '' && !preg_match('/\A[a-f0-9]{16}\z/i', $parts[1]))) {
                showError($config, 'La publicación o el hash del baneo no son válidos.');
            }
            deletePostCompletely($config, (int)$parts[0]);
            if ($parts[1] !== '') addBan($config, $parts[1]);
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
                $durationValues = $duration;
                $duration = is_array($durationValues) ? ($durationValues[$postValue] ?? '') : '';
            }
            if (!is_string($duration) || !isset($durationOptions[$duration])
                || !is_string($hash) || !preg_match('/\A[a-f0-9]{16}\z/i', $hash)) {
                showError($config, 'La duración o el hash del baneo no son válidos.');
            }
            $expiresAt = $durationOptions[$duration] === 0 ? 0 : time() + $durationOptions[$duration];
            addBan($config, $hash, $expiresAt);
        }
        if (isset($_POST['unban']) && $_POST['unban'] !== '') {
            $hash = $_POST['unban'];
            if (!is_string($hash) || !preg_match('/\A[a-f0-9]{16}\z/i', $hash)) {
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
        header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&tab=' . rawurlencode($tab) . '&page=' . $page);
        exit;
    }

    $tab = $_GET['tab'] ?? 'posts';
    $tab = is_string($tab) && in_array($tab, ['posts', 'reports', 'bans', 'settings'], true) ? $tab : 'posts';
    ensureThreadActionIcons();
    $allpostsraw  = array_reverse(readPosts($config));
    $postsByNum = [];
    foreach ($allpostsraw as $post) {
        $postsByNum[(int)$post['num']] = $post;
    }
    $totalcount   = count($allpostsraw);
    $totalpages   = max(1, (int)ceil($totalcount / $config['postsperpage']));
    $pageValue    = $_GET['page'] ?? '0';
    $pagenumber   = is_string($pageValue) && ctype_digit($pageValue)
        ? min((int)$pageValue, $totalpages - 1)
        : 0;
    $pageposts    = array_slice($allpostsraw, $pagenumber * $config['postsperpage'], $config['postsperpage']);
    $title = ($config['title'] ?: 'Tenma') . ' - Admin';

    $content = '<div class="admin-page">
<header class="admin-heading"><h1>' . htmlspecialchars($config['title'] ?: 'Tenma') . ' - Panel de administración</h1></header>
<hr>';

    if (password_verify('admin123', $config['adminpasswordhash'])) {
        $content .= '<p class="admin-warning">Estás usando la contraseña de administrador por defecto.</p>';
    }

    $reports = getReports($config);
    $bans = getBans($config);
    $content .= '<nav class="admin-tab-nav admin-button-group" aria-label="Secciones de administración">';
    foreach ([
        'posts' => 'Publicaciones',
        'reports' => 'Reportes',
        'bans' => 'Baneos',
        'settings' => 'Ajustes',
    ] as $tabValue => $tabLabel) {
        $current = $tab === $tabValue ? ' aria-current="page"' : '';
        $content .= '<form method="GET" action="' . siteUrl($config['tenmafile']) . '">
<input type="hidden" name="mode" value="manage">
<button type="submit" name="tab" value="' . $tabValue . '"' . $current . '>' . $tabLabel . '</button>
</form>';
    }
    $content .= '<form method="POST" action="?mode=manage" onsubmit="return confirm(\'Esto cerrará todas las sesiones de administrador activas. ¿Continuar?\');">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<button type="submit" name="logout" value="1">Cerrar sesión</button>
</form>
</nav>
<hr>
<main class="admin-content">';

    if ($tab === 'settings') {
        $noticeMessage = htmlspecialchars(getSiteSetting($config, 'notice_message'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $noticeEnabled = getSiteSetting($config, 'notice_enabled') === '1' ? ' checked' : '';
        $content .= '<section class="admin-section"><h2 class="admin-section-title">Ajustes</h2>
<section class="admin-settings-block">
<h3>Avisos importantes</h3>
<form method="POST" action="?mode=manage">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<label class="admin-notice-toggle"><input type="checkbox" name="notice_enabled" value="1"' . $noticeEnabled . '> Mostrar aviso en el tablón</label>
<textarea class="admin-notice-input" name="notice_message" maxlength="' . $config['commentlimit'] . '" rows="3" placeholder="Escribe el aviso que se mostrará en rojo y centrado">' . $noticeMessage . '</textarea>
<div class="admin-button-group admin-settings-submit"><button type="submit" name="save_notice" value="1">Guardar aviso</button></div>
</form>
</section>
<hr>
<section class="admin-settings-block">
<h3>Estado de publicaciones</h3>
<p class="admin-status">Estado actual: <b>' . (file_exists($config['lockfile']) ? 'Bloqueadas' : 'Abiertas') . '</b></p>
<div class="admin-toolbar admin-button-group">
<form method="POST" action="?mode=manage">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<button type="submit" name="togglelock" value="1">' . (file_exists($config['lockfile']) ? 'Desbloquear publicaciones' : 'Bloquear publicaciones') . '</button>
</form>
</div>
</section>
<hr>
<section class="admin-settings-block">
<h3>Mantenimiento</h3>
<p class="admin-status">Regenera todos los archivos .html realizando una actualización completa del sitio</p>
<div class="admin-toolbar admin-button-group">
<form method="POST" action="?mode=manage">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<button type="submit" name="rebuild" value="1">Regenerar HTML</button>
</form>
</div>
</section></section>';
    }

    if ($tab === 'reports') {
        $content .= '<section class="admin-section"><h2 class="admin-section-title">Reportes (' . count($reports) . ')</h2>';
        if (!empty($reports)) {
            $content .= '<div class="report-grid">';
            foreach ($reports as $r) {
                $rnum   = (int)$r['num'];
                $reason = htmlspecialchars(mb_substr($r['reason'], 0, 120));
                $rtime  = htmlspecialchars($r['time']);
                $reportedPost = $postsByNum[$rnum] ?? null;
                $reportedHash = is_array($reportedPost) ? (string)$reportedPost['postiphash'] : '';
                $content .= '<form method="POST" action="?mode=manage">
<input type="hidden" name="tab" value="reports">
<input type="hidden" name="page" value="' . $pagenumber . '">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<div class="report-card reported-post">
<div class="rc-num">Reporte de No.' . $rnum . '</div>
<div class="rc-reason">' . $reason . '</div>
<div class="rc-time">' . $rtime . '</div>
<div class="rc-actions admin-button-group">
  <button type="submit" name="dismiss_report" value="' . $r['id'] . '">Descartar</button>
  <button type="submit" name="delete" value="' . $rnum . '">Eliminar</button>';
                if (preg_match('/\A[a-f0-9]{16}\z/i', $reportedHash)) {
                    $content .= '<label>Duración del baneo: <select name="ban_duration">
<option value="3600">1 hora</option>
<option value="86400">1 día</option>
<option value="604800">7 días</option>
<option value="2592000">30 días</option>
<option value="0">Permanente</option>
</select></label>
  <button type="submit" name="ban" value="' . htmlspecialchars($reportedHash, ENT_QUOTES, 'UTF-8') . '">Banear IP</button>';
                }
                $content .= '
</div>
<div class="reported-publication">';
                if ($reportedPost !== null) {
                    $content .= (int)$reportedPost['parent'] > 0
                        ? renderReply($reportedPost, $allpostsraw, false, $config, false, true)
                        : renderOP($reportedPost, $allpostsraw, false, false, $config, false, true);
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
        if (!empty($bans)) {
            $content .= '<form method="POST" action="?mode=manage">
<input type="hidden" name="tab" value="bans">
<input type="hidden" name="page" value="' . $pagenumber . '">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<div class="report-grid">';
        foreach ($bans as $ban) {
            $banHash = (string)$ban['hash'];
            $expiresAt = (int)$ban['expires_at'];
            if ($expiresAt === 0) {
                $banStatus = 'Permanente';
            } elseif ($expiresAt <= time()) {
                $banStatus = 'Vencido el ' . date('Y-m-d H:i:s', $expiresAt);
            } else {
                $banStatus = 'Hasta el ' . date('Y-m-d H:i:s', $expiresAt);
            }
            $content .= '<div class="report-card">
<div class="rc-num">Hash: ' . htmlspecialchars($banHash, ENT_QUOTES, 'UTF-8') . '</div>
<div class="rc-time">' . htmlspecialchars($banStatus, ENT_QUOTES, 'UTF-8') . '</div>
<div class="rc-actions admin-button-group"><button type="submit" name="unban" value="' . htmlspecialchars($banHash, ENT_QUOTES, 'UTF-8') . '">Quitar baneo</button></div>
</div>';
        }
        $content .= '</div></form>';
        } else {
            $content .= '<p style="text-align:center;margin:10px 0;">No hay baneos registrados.</p>';
        }
        $content .= '</section>';
    }

    if ($tab === 'posts') {
        $content .= '<section class="admin-section"><h2 class="admin-section-title">Publicaciones (' . $totalcount . ')</h2>';
        $content .= '<form method="POST" action="?mode=manage">
<input type="hidden" name="tab" value="posts">
<input type="hidden" name="page" value="' . $pagenumber . '">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<div class="admin-post-list">';

        foreach ($pageposts as $p) {
            $num = (int)$p['num'];
            $hash = (string)$p['postiphash'];
            $postHtml = (int)$p['parent'] > 0
                ? renderReply($p, $allpostsraw, true, $config, false, true)
                : renderOP($p, $allpostsraw, false, true, $config, false, true);

            $content .= '<div class="admin-post-entry">'
                . $postHtml
                . '<div class="admin-post-controls"><span class="admin-post-hash">ID: '
                . htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') . '</span><div class="admin-post-actions admin-button-group">';
            if ((int)$p['deleted'] > 0) {
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
<button type="submit" name="ban" value="' . htmlspecialchars($hash . '|' . $num, ENT_QUOTES, 'UTF-8') . '">Banear IP</button>';
            if ((int)$p['parent'] === 0 && (int)$p['deleted'] === 0) {
                $locked = !empty($p['locked']);
                $pinned = !empty($p['pinned']);
                $content .= '<button class="thread-admin-action" type="submit" name="toggle_thread_lock" value="' . $num . '" aria-label="' . ($locked ? 'Desbloquear hilo' : 'Bloquear hilo') . '" title="' . ($locked ? 'Desbloquear hilo' : 'Bloquear hilo') . '">' . ($locked ? 'Desbloquear' : 'Bloquear') . '</button>'
                    . '<button class="thread-admin-action" type="submit" name="toggle_thread_pin" value="' . $num . '" aria-label="' . ($pinned ? 'Quitar fijado' : 'Fijar hilo') . '" title="' . ($pinned ? 'Quitar fijado' : 'Fijar hilo') . '">' . ($pinned ? 'Desfijar' : 'Fijar') . '</button>';
            }
            $content .= '</div></div><hr style="clear:both;">'
                . '</div>';
        }

        $content .= '</div></form>';
        $content .= paginationHtml($pagenumber, $totalpages, 'manage', $config, 'tab=posts');
        $content .= '</section>';
    }

    $content .= '</main></div>';
    echo renderPage($title, $content, $config, ['index.html' => 'Inicio']);
    exit;
}

// ===== BLOQUEO =====
if (file_exists($config['lockfile'])) {
    if (!isAdminAuthenticated($config)) {
        showError($config, 'Las publicaciones están bloqueadas temporalmente.');
    }
}

/**
 * Traduce los códigos de error de subida de PHP a mensajes comprensibles para el usuario.
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
 * Recibe un archivo subido por el usuario y lo guarda de forma segura en la carpeta de uploads.
 * Devuelve el nombre final, el tamaño y el MIME para almacenarlo en la base de datos.
 */
function storeUploadedImage(array $config, array $file): array {
    // Validación básica del formulario de subida.
    if (!isset($file['error']) || !is_int($file['error'])) {
        throw new RuntimeException('La información del archivo no es válida.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException(getUploadErrorMessage((int)$file['error']));
    }

    // Aseguramos que el archivo realmente haya sido subido por PHP y exista en el temporal.
    $tmpName = $file['tmp_name'] ?? '';
    if (!is_string($tmpName) || $tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('El archivo no pasó la validación de subida.');
    }

    if (!is_readable($tmpName)) {
        throw new RuntimeException('El archivo temporal no es legible.');
    }

    // Comprobamos que sea una imagen real y no un archivo malicioso con contenido no válido.
    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false) {
        throw new RuntimeException('El archivo no es una imagen válida.');
    }

    $mimeType = $imageInfo['mime'] ?? '';
    if (!in_array($mimeType, $config['allowedtypes'], true)) {
        throw new RuntimeException('Tipo de imagen no permitido. Usa JPG, PNG, GIF o WEBP.');
    }

    // Limitamos el tamaño para evitar archivos demasiado pesados.
    if (!isset($file['size']) || !is_int($file['size']) || $file['size'] < 0) {
        throw new RuntimeException('El tamaño del archivo no es válido.');
    }
    $fileSize = $file['size'];
    if ($fileSize > $config['maximagesize']) {
        throw new RuntimeException('La imagen supera el tamaño máximo de 3MB.');
    }

    // Convertimos el tipo MIME a una extensión segura para el nombre de archivo.
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $extension = $extensions[$mimeType] ?? 'bin';

    if ($extension === 'bin') {
        throw new RuntimeException('Tipo de imagen no permitido.');
    }

    // Creamos una ruta destino única, sin sobrescribir archivos existentes.
    $targetDir = rtrim($config['uploadfolder'], '/');
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $targetDir . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Error al guardar la imagen en el servidor.');
    }

    return [
        'filename' => $filename,
        'filesize' => $fileSize,
        'mimetype' => $mimeType,
    ];
}

// ===== PUBLICAR =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['name', 'subject', 'com'] as $field) {
        if (isset($_POST[$field]) && !is_string($_POST[$field])) {
            showError($config, 'Los datos de la publicación no son válidos.');
        }
    }
    $name    = trim($_POST['name']    ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $comment = trim($_POST['com']     ?? '');
    $parentValue = $_POST['parent'] ?? '0';
    if (!is_string($parentValue) || !ctype_digit($parentValue)) {
        showError($config, 'El hilo seleccionado no es válido.');
    }
    $parent = (int)$parentValue;
    if ($parent > 0) {
        $parentThread = getPostByNum($config, $parent);
        if ($parentThread === null || (int)$parentThread['parent'] !== 0 || (int)$parentThread['deleted'] !== 0) {
            showError($config, 'El hilo al que intentas responder no existe.');
        }
        if (!empty($parentThread['locked'])) {
            showError($config, 'Este hilo está bloqueado y no admite nuevas respuestas.');
        }
    }

    if (!empty($config['badstrings'])) {
        $pattern = '/' . implode('|', array_map(fn($s) => preg_quote($s, '/'), $config['badstrings'])) . '/i';
        if (preg_match($pattern, $comment) || preg_match($pattern, $name) || preg_match($pattern, $subject)) {
            showError($config, 'Tu publicación contiene frases prohibidas.');
        }
    }

    // Manejo de la imagen adjunta. Se delega a una función específica para
    // mantener este bloque de publicación más legible y fácil de mantener.
    $imagefile = '';
    $filesize  = 0;
    if (isset($_FILES['image']) && (!is_array($_FILES['image'])
        || !isset($_FILES['image']['error']) || !is_int($_FILES['image']['error']))) {
        showError($config, 'La información del archivo no es válida.');
    }
    if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        try {
            $uploaded = storeUploadedImage($config, $_FILES['image']);
            $imagefile = $uploaded['filename'];
            $filesize  = $uploaded['filesize'];
        } catch (RuntimeException $e) {
            showError($config, $e->getMessage());
        }
    }

    if ($parent === 0) {
        if ($imagefile === '') showError($config, 'Para crear un hilo debes adjuntar una imagen.');
        if ($subject === '')   showError($config, 'Para crear un hilo debes indicar un asunto.');
        if ($comment === '')   showError($config, 'Para crear un hilo debes escribir un comentario.');
    } else {
        if ($comment === '' && $imagefile === '') showError($config, 'Debes escribir un comentario o subir una imagen.');
    }

    if (mb_strlen($name)    > $config['namelimit'])    showError($config, 'Tu nombre no puede tener más de '      . $config['namelimit']    . ' caracteres.');
    if (mb_strlen($subject) > $config['subjectlimit']) showError($config, 'Tu asunto no puede tener más de '     . $config['subjectlimit'] . ' caracteres.');
    if (mb_strlen($comment) > $config['commentlimit']) showError($config, 'Tu comentario no puede tener más de ' . $config['commentlimit'] . ' caracteres.');
    $isAdminPost = isAdminAuthenticated($config);
    $name = ($name ?: $config['defaultname']);
    if ($config['forcedanonymity']) $name = $config['defaultname'];
    if ($isAdminPost) $name = 'Administrador';

    $now = time();
    foreach (getRecentPosts($config, $config['postsperpage']) as $p) {
        if (($now - $p['now']) < $config['cooldown']) {
            showError($config, 'Por favor espera ' . $config['cooldown'] . ' segundos entre publicaciones.');
        }
        if ($p['name'] === $name && $p['comment'] === $comment && $p['postiphash'] === $hashedip) {
            showError($config, 'Ya dijiste eso recientemente.');
        }
    }

    $num = insertPost($config, [
        'name'       => $name,
        'subject'    => $subject,
        'comment'    => $comment,
        'time'       => spanishDate(),
        'now'        => $now,
        'postiphash' => $hashedip,
        'image'      => $imagefile,
        'parent'     => $parent,
        'filesize'   => $filesize,
        'author_admin' => $isAdminPost ? 1 : 0,
    ]);

    if ($parent === 0) {
        pruneOldThreads($config);
    }

    buildPages(readPosts($config), $config);
    header('Location: ' . ($parent > 0 ? threadUrl($parent) . '#p' . $num : siteUrl('board.html')));
    exit;
}

// ===== REDIRECCIÓN INICIAL =====
if (!file_exists('index.html')) {
    buildPages(readPosts($config), $config);
}
header('Location: ' . siteUrl('index.html'));
exit;