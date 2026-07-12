<?php
// ===== CONFIGURACIÓN =====
$config = [
    'title'              => 'Tenma',
    'subtitle'           => 'Tablón anónimo para discusión, imágenes y debates breves',
    'gradient'           => '#FED6AF',
    'background'         => '#FFFFEE',
    'section'            => '#FCA',
    'textcolor'          => '#800',
    'linkcolor'          => '#0000EE',
    'linkhover'          => '#FF0000',
    'fonts'              => 'arial, helvetica, sans-serif',
    'formsidecolor'      => '#ea8',
    'border'             => '#800',
    'postbackground'     => '#F0E0D6',
    'posternamecolor'    => '#117743',
    'postsubjectcolor'   => '#CC1105',
    'errortextcolor'     => '#B00020',
    'defaultname'        => 'Anónimo',
    'deletionphrase'     => 'Eliminado',
    'rules'              => [
        'Respeto: mantén un comportamiento civil; no se permiten insultos, amenazas ni agresiones personales.',
        'Contenido ilegal: está prohibido publicar material que infrinja la ley del país donde se hospeda el sitio.',
        'Doxxing: no publiques datos personales de terceros (direcciones, teléfonos, documentos, identificadores privados).',
        'Formatos permitidos: JPG, PNG, GIF, WEBP. Tamaño máximo por archivo: 3 MB.',
        'Spam y autopromoción: evita repetir mensajes, enlaces comerciales o propaganda no solicitada.',
        'Enlaces y archivos maliciosos: no enlaces a malware, phishing, estafas ni a contenido que busque engañar a usuarios.',
        'Reportes: las publicaciones reportadas serán revisadas por el equipo; las medidas pueden incluir edición, eliminación o baneo.',
        'Moderación: el administrador puede eliminar publicaciones o banear IPs/hash sin previo aviso cuando existan infracciones claras.',
        'Privacidad de menores: no publiques imágenes ni contenidos que pongan en riesgo a menores de edad.',
        'Al publicar aceptas que el contenido pueda almacenarse y mostrarse públicamente hasta que sea eliminado por moderación.',
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

// ===== INICIALIZACIÓN =====
ini_set('default_charset', 'UTF-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('tenma_admin');
    session_start();
}

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
    RewriteRule ^thread-([0-9]+)\.html$ threads/thread-$1.html [L,R=301]
    RewriteRule ^threads/([0-9]+)/?$ threads/thread-$1.html [L,QSA]
    RewriteRule ^threads/([0-9]+)/(.*)$ threads/thread-$1.html [L,QSA]
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
        if (strpos($existing, $expectedErrorDocument) === false || strpos($existing, 'RewriteRule ^threads') === false) {
            $needsWrite = true;
        }
    }
    if ($needsWrite) {
        file_put_contents($path, $content);
    }
}

$ip       = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$hashedip = substr(sha1($ip), 0, 16);

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
        filesize    INTEGER NOT NULL DEFAULT 0
    )");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_posts_parent ON posts(parent)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS bans (
        hash TEXT PRIMARY KEY
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS reports (
        id     INTEGER PRIMARY KEY AUTOINCREMENT,
        num    INTEGER NOT NULL,
        reason TEXT    NOT NULL DEFAULT '',
        time   TEXT    NOT NULL
    )");
    return $pdo;
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
        (name, subject, comment, time, now, postiphash, deleted, image, parent, filesize)
        VALUES (:name, :subject, :comment, :time, :now, :postiphash, 0, :image, :parent, :filesize)');
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
 * Comprueba si un hilo existe realmente en la base de datos.
 */
function threadExists(array $config, int $num): bool {
    $stmt = getPDO($config)->prepare('SELECT 1 FROM posts WHERE num = :num AND parent = 0');
    $stmt->execute(['num' => $num]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Comprueba si una IP o hash de IP está baneado.
 */
function isBanned(array $config, string $hash): bool {
    $stmt = getPDO($config)->prepare('SELECT 1 FROM bans WHERE hash = :hash');
    $stmt->execute(['hash' => $hash]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Añade un hash de IP a la tabla de baneos si aún no estaba registrado.
 */
function addBan(array $config, string $hash): void {
    getPDO($config)->prepare('INSERT OR IGNORE INTO bans (hash) VALUES (:hash)')->execute(['hash' => $hash]);
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
 * Genera un token firmado para validar la sesión de administración.
 */
function adminSessionToken(array $config): string {
    return hash_hmac('sha256', $config['adminusername'], $config['adminpasswordhash']);
}

function generateAdminCsrfToken(array $config): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('tenma_admin');
        session_start();
    }

    if (empty($_SESSION['tenma_admin_csrf'])) {
        $_SESSION['tenma_admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['tenma_admin_csrf'];
}

function verifyAdminCsrfToken(array $config, string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('tenma_admin');
        session_start();
    }

    return hash_equals($_SESSION['tenma_admin_csrf'] ?? '', $token);
}

if (isBanned($config, $hashedip)) {
    showError($config, 'Estás baneado de este BBS.');
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
function showError(array $config, string $message): never {
    $title    = htmlspecialchars($config['title']);
    $subtitle = htmlspecialchars($config['subtitle']);
    $homeUrl  = siteUrl('index.html');

    $content = <<<HTML
<center> 
<h1>$title</h1>
<h3>$subtitle</h3>
<br>
<hr>
<div style="margin: 1em;">
<span class="error-message">$message</span>
<br>
[<a href="$homeUrl">Volver</a>]
</div>
<center> 
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
        if ((int)$p['parent'] === 0) {
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
    return '<div style="text-align:center;font-size:9pt;color:#aaa;margin-top:8px;padding:4px 0;">' . $title . ' &mdash; Tablón anónimo para discusión, imágenes y debates breves</div>';
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
    $faviconUrl = siteUrl('favicon.svg');

    return <<<HTML
    <!DOCTYPE html>
    <html lang="es">
    <head>
    <title>$title</title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="$faviconUrl">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; overflow-wrap: break-word; word-wrap: break-word }
    body { font-family: {$config['fonts']}; color: {$config['textcolor']}; padding: 10px; background: linear-gradient(to bottom, {$config['gradient']} 0, {$config['background']} 190px) no-repeat; background-color: {$config['background']} }
    a { text-decoration: none; color: {$config['linkcolor']} }
    a:hover { color: {$config['linkhover']} }
    .container { margin: 0 auto }
    .nav { text-align: center; margin: .3em 0 }
    hr { border: none; opacity: .3; border-top: 1px solid {$config['textcolor']} }
    ul { list-style-type: none; margin: 1em; padding: 0 }
    input[type=text], input[type=password], textarea { font-family:sans-serif; padding:.2em; border: 1px solid {$config['border']} }
    .fileinfo  { font-size: 10pt; margin-bottom: 3px }
    .error-message { color: {$config['errortextcolor']}; font-weight: bold }
    .postheader { font-size: 10.5pt }
    .postheader .subject { color: {$config['postsubjectcolor']}; font-weight: bold }
    .postactions { font-size: 10.5pt }
    .omitted { padding: 5px 0; font-size: 10pt }
    .postnum { cursor: pointer; color: {$config['linkcolor']} }
    .postnum:hover { text-decoration: underline; color: {$config['linkhover']} }
    .report-grid { display: flex; flex-wrap: wrap; gap: 10px; padding: 10px }
    .report-card { background: #fff8f2; border: 1px solid #e09060; border-radius: 6px; padding: 10px 14px; min-width: 260px; flex: 1 1 260px }
    .report-card .rc-num  { font-weight: bold; font-size: 11pt; color: {$config['postsubjectcolor']} }
    .report-card .rc-reason { margin: 4px 0 8px; font-size: 10px }
    .report-card .rc-time { font-size: 9pt; color: #888; margin-bottom: 8px }
    .report-card .rc-actions button { margin-right: 4px; cursor: pointer }
    .help-list { overflow: hidden }
    .help-row { display: flex; align-items: center; padding: 8px 12px; border-bottom: 1px solid #ddd; gap: 14px }
    .help-row:last-child { border-bottom: none }
    .help-syntax { font-family: monospace; flex: 0 0 100px }
    .help-preview { flex: 0 0 100px; font-size: 11pt }
    .help-desc { flex: 1 1 auto; font-size: 9.5pt; color: #666 }
    .admin-table { width: 100%; border-collapse: collapse; border: 1px solid #800}
    .admin-table th { background: #FCA; color: #800; padding: 5px }
    .admin-table td { padding: 5px }
    .admin-toolbar { text-align: center; margin: 10px 0 }
    </style>
    </head>
    <body>
    <div class="container" style="max-width:800px">
    $content
    <hr>
    $navhtml
    $footer
    </div>
    <script>
    function quotePost(num) {
        var allTextareas = document.querySelectorAll('textarea[name="com"]');
        
        // Si no hay ningún textarea, ir al hilo
        if (allTextareas.length === 0) {
            window.location.href = '$threadBaseUrl' + num + '/?quote=' + num;
            return;
        }
        
        // Si solo hay un textarea (estamos en el tablón), ir al hilo
        if (allTextareas.length === 1) {
            var ta = allTextareas[0];
            var form = ta.closest('form');
            var isCreateForm = form && form.querySelector('input[name="subject"]');
            if (isCreateForm) {
                window.location.href = '$threadBaseUrl' + num + '/?quote=' + num;
                return;
            }
        }
        
        // Estamos en el hilo, agregar la cita al último textarea
        var ta = allTextareas[allTextareas.length - 1];
        ta.focus();
        var quote = '>>' + num + '\\n';
        ta.value += quote;
        ta.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
    
    // Al cargar la página, agregar la cita si viene en la URL
    window.addEventListener('load', function() {
        var url = window.location.href;
        var quoteMatch = url.match(/[?&]quote=(\d+)/);
        if (quoteMatch) {
            var quoteNum = quoteMatch[1];
            var ta = document.querySelector('textarea[name="com"]');
            if (ta && !ta.value.includes('>>' + quoteNum)) {
                ta.value = '>>' + quoteNum + '\\n' + ta.value;
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
    tenmaUpdateTimes();
    setInterval(tenmaUpdateTimes, 30000);
    </script>
    </body>
    </html>
    HTML;
}

/**
 * Genera la página de error 404 para hilos o recursos ya eliminados.
 */
function generateErrorPage(array $config): string {
    $title   = ($config['title'] ?: 'Tenma') . ' - No encontrado';
    $content = '<center>
<h1 style="margin-bottom:5px;">404</h1>
<hr><br>
<p style="margin-bottom:15px;">El hilo o la imagen que buscas ya no existe.<br>Puede que haya sido eliminado por el administrador o eliminado automáticamente por antigüedad.</p>
<a href="' . siteUrl('index.html') . '"><button style="padding:.15em .3em;cursor:pointer;">Volver a Inicio</button></a><br><br>
</center>';
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
    $fileinfo = '<div class="fileinfo">Archivo: <a href="' . $path . '" target="_blank">'
              . htmlspecialchars($post['image']) . '</a>'
              . ' (' . $size . ($dimstext !== '' ? ', ' . $dimstext : '') . ')</div>';
    $img      = '<div style="float:left;margin-right:10px;">'
              . '<a href="' . $path . '" target="_blank">'
              . '<img src="' . $path . '" style="' . $maxcss . '" alt="imagen">'
              . '</a></div>';
    return $fileinfo . $img;
}

/**
 * Crea el enlace de reporte para una publicación concreta.
 */
function renderReportLink(int $num, array $config): string {
    return ' [<a href="' . $config['tenmafile'] . '?mode=report&num=' . $num . '">Reportar</a>]';
}

/**
 * Renderiza una publicación de tipo OP (post inicial de un hilo).
 */
function renderOP(array $post, array $allposts, bool $isthread, bool $summarize, array $config): string {
    $reportLink = renderReportLink((int)$post['num'], $config);

    if ((int)$post['deleted'] > 0) {
        return '<div id="p' . $post['num'] . '" style="padding:8px;margin-bottom:5px;background-color:' . $config['postbackground'] . ';">'
             . '<i>' . $config['deletionphrase'] . '</i>'
             . '<span class="postactions">' . $reportLink . '</span>'
             . '</div>';
    }

    $name     = htmlspecialchars($post['name']);
    $subject  = htmlspecialchars($post['subject'] ?? '');
    $subjHtml = $subject !== '' ? '<span class="subject">' . $subject . '</span> ' : '';

    $comment = $post['comment'];
    if ($summarize && mb_strlen($comment) > 150) {
        $comment = mb_substr($comment, 0, 150) . '...';
    }
    $comment   = formatComment($comment, $allposts);
    $imageHtml = renderImageBlock($post, true, $config);
    $replyLink = !$isthread
        ? ' [<a href="' . threadUrl((int)$post['num']) . '">Responder</a>]' : '';
    return '<div id="p' . $post['num'] . '" style="padding:.5em 0;overflow:hidden;">'
         . $imageHtml
         . '<div class="postheader">'
         . ' ' . $subjHtml
         . '<span style="color:' . $config['posternamecolor'] . ';"><b>' . $name . '</b></span> '
         . '<span>' . $post['time'] . '</span> '
         . '<span class="postnum" onclick="quotePost(' . $post['num'] . ')" title="Responder citando esta publicación">No.' . $post['num'] . '</span>'
         . '<span class="postactions">' . $replyLink . $reportLink . '</span>'
         . '</div>'
         . '<div style="margin-top:5px;">' . $comment . '<br clear="all"></div>'
         . '</div>';
}

/**
 * Renderiza una respuesta de hilo con su contenido, imagen y acciones.
 */
function renderReply(array $post, array $allposts, bool $summarize, array $config): string {
    $reportLink = renderReportLink((int)$post['num'], $config);

    if ((int)$post['deleted'] > 0) {
        return '<div id="p' . $post['num'] . '" style="padding:6px;margin:0 0 4px 0;background-color:' . $config['postbackground'] . ';">'
             . '<span>No.' . $post['num'] . ' </span>'
             . '<i>' . $config['deletionphrase'] . '</i>'
             . '</div>';
    }

    $name    = htmlspecialchars($post['name']);
    $comment = $post['comment'];
    if ($summarize && mb_strlen($comment) > 150) {
        $comment = mb_substr($comment, 0, 150) . '...';
    }
    $comment   = formatComment($comment, $allposts);
    $imageHtml = renderImageBlock($post, false, $config);

    return '<div id="p' . $post['num'] . '" style="display:inline-grid;padding:5px;margin:0 0 4px 0;border:1px solid #D9BFB7;background-color:' . $config['postbackground'] . ';overflow:hidden;max-width:100%;">'
         . $imageHtml
         . '<div class="postheader">'
         . ' <span style="color:' . $config['posternamecolor'] . ';"><b>' . $name . '</b></span> '
         . '<span>' . $post['time'] . '</span> '
         . '<span class="postnum" onclick="quotePost(' . $post['num'] . ')" title="Responder citando esta publicación">No.' . $post['num'] . '</span>'
         . '<span class="postactions">' . $reportLink . '</span>'
         . '</div>'
         . '<div style="margin-top:4px;">' . $comment . '<br clear="all"></div>'
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

    if (!is_dir($config['threaddir'])) {
        mkdir($config['threaddir'], 0755, true);
    }

    file_put_contents('index.html',              generateIndex($threadlist, $posts, $config),        LOCK_EX);
    file_put_contents($config['rulesfile'],      generateRules($config),                             LOCK_EX);
    file_put_contents($config['helpfile'],       generateHelp($config),                              LOCK_EX);
    file_put_contents($config['errorfile'],      generateErrorPage($config),                         LOCK_EX);
    file_put_contents('favicon.svg',             generateFavicon($config),                           LOCK_EX);
    file_put_contents('robots.txt',              generateRobots(),                                   LOCK_EX);
    file_put_contents('sitemap.xml',             generateSitemap($threadlist, $config),              LOCK_EX);

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
function generateIndex(array $threadlist, array $allposts, array $config): string {
    $title = $config['title'] ?: 'Tenma';

    $recent   = array_reverse(array_slice($allposts, -10));
    $actItems = '';
    foreach ($recent as $p) {
        $thread  = $p['parent'] > 0 ? $p['parent'] : $p['num'];
        $preview = (int)$p['deleted'] > 0
            ? '<i>Eliminado</i>'
            : (empty($p['comment']) && !empty($p['image'])
                ? '[imagen]'
                : htmlspecialchars(mb_substr($p['comment'], 0, 60)) . (mb_strlen($p['comment']) > 60 ? '...' : ''));
        $name    = htmlspecialchars($p['name']);
        $ts      = (int)$p['now'];
        $reltime = relativeTime($ts);
        $actItems .= '<li><a href="' . threadUrl($thread) . '#p' . $p['num'] . '">>>' . $p['num'] . '</a>'
                   . ' por <b style="color:' . $config['posternamecolor'] . ';">' . $name . '</b>: '
                   . $preview . ' <small style="color:#888;float:inline-end;margin-right:1em;" data-ts="' . $ts . '">(' . $reltime . ')</small></li>';
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
            $thumbs .= '<div style="text-align:center;">
<a href="' . threadUrl($thread) . '#p' . $img['num'] . '">
<img src="' . siteUrl($config['uploadfolder'] . $img['image']) . '" style="width:120px;height:120px;object-fit:cover;border:1px solid #800;" alt="imagen">
</a>
<div style="font-size:10pt;"><a href="' . threadUrl($thread) . '#p' . $img['num'] . '">>>' . $img['num'] . '</a></div>
</div>';
        }
        $gallery = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . $config['section'] . ';">Imágenes recientes</h3>
<div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;padding:0 0 10px 10px;">' . $thumbs . '</div></div>';
    }

    $totalPosts  = count($allposts);
    $totalImages = count(array_filter($allposts, fn($p) => !empty($p['image'])));
    $totalSize   = array_sum(array_map(fn($p) => (int)$p['filesize'], $allposts));
    $stats = '<div style="background:#fff;margin:1em 0;border:1px solid #800;">
<h3 style="margin:0 0 10px;padding:3px 10px;background:' . $config['section'] . ';">Estadísticas</h3>
<div style="font-size:11pt;padding:0 0 10px 10px;display:flex;flex-wrap:wrap;justify-content:center;gap:15px;text-align:center;">
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

    $namefield = $config['forcedanonymity']
        ? '<input type="text" name="name" size="28" value="' . $config['defaultname'] . '" disabled autocomplete="off">'
        : '<input type="text" name="name" size="28" autocomplete="off">';

    // Formulario sin bloque de ayuda de formato bajo el textarea
    $formhtml = '<center>
<h3 style="margin:.5em 0;">Crear nuevo hilo</h3>
<form method="POST" action="' . siteUrl($config['tenmafile']) . '" enctype="multipart/form-data" style="margin-bottom:10px;">
<table style="margin:0 auto;border-spacing:1px;"><tbody>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Nombre</b></td>
  <td>' . $namefield . ' <input type="submit" value="Publicar" style="padding:.15em .3em"></td>
</tr>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Asunto</b></td>
  <td><input type="text" name="subject" size="28" maxlength="' . $config['subjectlimit'] . '" autocomplete="off" required></td>
</tr>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Comentario</b></td>
  <td><textarea name="com" cols="48" rows="4" style="vertical-align:bottom" autocomplete="off" required></textarea></td>
</tr>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Imagen</b></td>
  <td><input type="file" name="image" accept="image/*" autocomplete="off" required> <small>(Máx 3MB)</small></td>
</tr>
</tbody></table>
<p style="font-size:9pt;">Puedes leer las <a href="' . $config['rulesfile'] . '">reglas</a> y la <a href="' . $config['helpfile'] . '">ayuda de formato</a>.</p>
</form></center><hr>';

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

    $pagehtml = paginationHtml($pagenumber, $totalpages, 'board', $config);

    $content = '<center style="margin-bottom:5px"><h1>' . $title . '</h1><h3>' . $subtitle . '</h3></center><hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>]</div><hr>
' . $formhtml . $posthtml . $pagehtml;

    return renderPage($title, $content, $config);
}

/**
 * Genera la página completa de un hilo con su OP y sus respuestas.
 */
function generateThread(array $thread, array $allposts, array $config): string {
    $op        = $thread['op'];
    $threadnum = $op['num'];
    $title     = ($config['title'] ?: 'Tenma') . ' - Hilo #' . $threadnum;

    $posthtml = renderOP($op, $allposts, true, false, $config);
    foreach ($thread['replies'] as $reply) {
        $posthtml .= renderReply($reply, $allposts, false, $config);
    }

    $namefield = $config['forcedanonymity']
        ? '<input type="text" name="name" size="28" value="' . $config['defaultname'] . '" disabled autocomplete="off">'
        : '<input type="text" name="name" size="28" autocomplete="off">';

    // Formulario de respuesta sin bloque de ayuda bajo el textarea
    $replyForm = '<hr>
<center>
<h3 style="margin:.5em 0">Responder al hilo</h3>
<form method="POST" action="' . siteUrl($config['tenmafile']) . '" enctype="multipart/form-data" style="margin:10px 0;">
<input type="hidden" name="parent" value="' . $threadnum . '">
<table style="margin:0 auto;border-spacing:1px;"><tbody>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Nombre</b></td>
  <td>' . $namefield . ' <input type="submit" value="Responder" style="padding:.15em .3em"></td>
</tr>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Comentario</b></td>
  <td><textarea name="com" cols="48" rows="4" style="vertical-align:bottom" autocomplete="off"></textarea></td>
</tr>
<tr>
  <td style="background:' . $config['formsidecolor'] . ';border:1px solid ' . $config['border'] . ';padding:.1em;"><b style="margin:.3em;">Imagen</b></td>
  <td><input type="file" name="image" accept="image/*" autocomplete="off"> <small>(Máx 3MB)</small></td>
</tr>
</tbody></table>
<p style="font-size:9pt;">Puedes leer las <a href="' . $config['rulesfile'] . '">reglas</a> y la <a href="' . $config['helpfile'] . '">ayuda de formato</a>.</p>
</form>
</center>';
    $content = '<center><h1 style="margin-bottom:5px;">' . $title . '</h1></center>
<hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>] [<a href="' . siteUrl('board.html') . '">Tablón</a>]</div>
<hr>
' . $posthtml . $replyForm;

    return renderPage($title, $content, $config);
}

/**
 * Genera un favicon simple con los colores del tablón.
 */
function generateFavicon(array $config): string {
    $bg = htmlspecialchars($config['section'],   ENT_QUOTES);
    $fg = htmlspecialchars($config['textcolor'], ENT_QUOTES);
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
         . '<rect width="64" height="64" rx="10" fill="' . $bg . '"/>'
         . '<text x="50%" y="54%" text-anchor="middle" dominant-baseline="middle" '
         . 'font-size="40" font-family="Arial,Helvetica,sans-serif" font-weight="bold" fill="' . $fg . '">T</text>'
         . '</svg>';
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
    $base = rtrim(
        ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://')
        . $_SERVER['HTTP_HOST']
        . dirname($_SERVER['SCRIPT_NAME']),
        '/'
    );

    $urls = ['index.html', 'board.html', $config['rulesfile'], $config['helpfile']];
    $xml  = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
    foreach ($urls as $page) {
        $xml[] = "<url><loc>$base/$page</loc></url>";
    }
    foreach ($threads as $t) {
        $xml[] = "<url><loc>$base/threads/{$t['op']['num']}/</loc></url>";
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
    $num = (int)($_GET['num'] ?? $_POST['num'] ?? 0);
    if ($num <= 0) showError($config, 'Publicación inválida.');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') showError($config, 'Debes indicar un motivo de reporte.');
        insertReport($config, $num, mb_substr($reason, 0, 500));
        $content = '<center><h2>Gracias</h2><p>Tu reporte para la publicación No.' . $num . ' fue enviado al equipo de moderación.</p></center>';
        echo renderPage($config['title'] ?: 'Tenma', $content, $config, ['index.html' => 'Inicio']);
        exit;
    }

    $title   = $config['title'] ?: 'Tenma';
    $content = '<center><h1 style="margin-bottom:5px;">Reportar publicación No.' . $num . '</h1><hr>
<div class="nav">[<a href="' . siteUrl('index.html') . '">Inicio</a>]</div><hr>
<br>
<form method="POST" action="' . siteUrl($config['tenmafile']) . '?mode=report&num=' . $num . '">
<textarea name="reason" cols="48" rows="4" placeholder="Motivo del reporte" style="display:block;margin:0 auto 8px;" autocomplete="off"></textarea>
<input type="submit" value="Enviar reporte" style="padding:.15em .3em">
</form>
<br></center>';
    echo renderPage($title, $content, $config);
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

        $validuser = hash_equals($config['adminusername'], trim($_POST['manageusername'] ?? ''));
        $validpass = password_verify((string)($_POST['managepassword'] ?? ''), $config['adminpasswordhash']);
        if ($validuser && $validpass) {
            session_regenerate_id(true);
            setcookie($config['managecookie'], $sessiontoken, [
                'expires'  => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $canmanage = true;
        } else {
            showError($config, 'Usuario o contraseña incorrectos.');
        }
    } elseif (isset($_COOKIE[$config['managecookie']]) && hash_equals($sessiontoken, $_COOKIE[$config['managecookie']])) {
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

        $page = (int)($_POST['page'] ?? 0);

        if (isset($_POST['togglelock'])) {
            file_exists($config['lockfile']) ? unlink($config['lockfile']) : file_put_contents($config['lockfile'], 'locked');
            header('Location: ' . $config['tenmafile'] . '?mode=manage');
            exit;
        }
        if (isset($_POST['rebuild'])) {
            buildPages(readPosts($config), $config);
            header('Location: ' . $config['tenmafile'] . '?mode=manage');
            exit;
        }
        if (isset($_POST['delete'])) {
            $n = (int)$_POST['delete'];
            deletePostCompletely($config, $n);
        }
        if (isset($_POST['delete_ban'])) {
            [$ns, $hash] = explode('|', $_POST['delete_ban'], 2);
            $n = (int)$ns;
            deletePostCompletely($config, $n);
            if ($hash !== '') addBan($config, $hash);
        }
        if (isset($_POST['ban']) && $_POST['ban'] !== '') {
            addBan($config, (string)$_POST['ban']);
        }
        if (isset($_POST['dismiss_report'])) {
            deleteReport($config, (int)$_POST['dismiss_report']);
        }

        buildPages(readPosts($config), $config);
        header('Location: ' . siteUrl($config['tenmafile']) . '?mode=manage&page=' . $page);
        exit;
    }

    $allpostsraw  = array_reverse(readPosts($config));
    $totalcount   = count($allpostsraw);
    $totalpages   = max(1, (int)ceil($totalcount / $config['postsperpage']));
    $pagenumber   = max(0, min((int)($_GET['page'] ?? 0), $totalpages - 1));
    $pageposts    = array_slice($allpostsraw, $pagenumber * $config['postsperpage'], $config['postsperpage']);
    $title = ($config['title'] ?: 'Tenma') . ' - Admin';

    $content = '<center><h1 style="margin-bottom:5px;">' . htmlspecialchars($config['title'] ?: 'Tenma') . ' - Panel de administración</h1></center>
<hr>
<div class="admin-toolbar">
<form method="POST" action="?mode=manage" style="display:inline;">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<button type="submit" name="togglelock" value="1" style="padding:.15em .3em">' . (file_exists($config['lockfile']) ? 'Desbloquear publicaciones' : 'Bloquear publicaciones') . '</button>
</form>
&nbsp;
<form method="POST" action="?mode=manage" style="display:inline;">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<button type="submit" name="rebuild" value="1" style="padding:.15em .3em">Regenerar HTML</button>
</form>
</div>';

    $reports = getReports($config);
    if (!empty($reports)) {
        $content .= '<hr><h3 style="text-align:center;margin-bottom:8px;">Reportes pendientes (' . count($reports) . ')</h3>
<form method="POST" action="?mode=manage">
<input type="hidden" name="page" value="' . $pagenumber . '">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<div class="report-grid">';
        foreach ($reports as $r) {
            $rnum   = (int)$r['num'];
            $reason = htmlspecialchars(mb_substr($r['reason'], 0, 120));
            $rtime  = htmlspecialchars($r['time']);
            $content .= '<div class="report-card">
<div class="rc-num"><a href="' . threadUrl($rnum) . '#p' . $rnum . '" target="_blank">No.' . $rnum . '</a></div>
<div class="rc-reason">' . $reason . '</div>
<div class="rc-time">' . $rtime . '</div>
<div class="rc-actions">
  <button type="submit" name="dismiss_report" value="' . $r['id'] . '">Descartar</button>
  <button type="submit" name="delete" value="' . $rnum . '">Eliminar post</button>
</div>
</div>';
        }
        $content .= '</div></form>';
    }

    $content .= '<hr><form method="POST" action="?mode=manage"><br>
<input type="hidden" name="page" value="' . $pagenumber . '">
<input type="hidden" name="csrf_token" value="' . $csrfToken . '">
<table class="admin-table">
<tr><th>#</th><th>Nombre</th><th>Asunto</th><th>Comentario</th><th>Hilo</th><th>Hora</th><th>ID</th><th>Acciones</th></tr>';

    foreach ($pageposts as $idx => $p) {
        $num     = $p['num'];
        $name    = htmlspecialchars($p['name']);
        $subject = htmlspecialchars($p['subject'] ?? '');
        $commentRaw = str_replace(["\r\n", "\r", "\n"], ' ', $p['comment']);
        $comment    = htmlspecialchars(mb_substr($commentRaw, 0, 50)) . (mb_strlen($commentRaw) > 50 ? '...' : '');
        $parent  = $p['parent'] > 0 ? '#' . $p['parent'] : '(OP)';
        $hash    = $p['postiphash'];
        $bg      = ($idx % 2) ? '#f0e0d6' : '#fff8f2';

        $content .= '<tr style="background:' . $bg . ';">
<td>' . $num . '</td><td>' . $name . '</td><td>' . $subject . '</td>
<td>' . $comment . '</td><td>' . $parent . '</td><td>' . $p['time'] . '</td><td>' . $hash . '</td>
<td>';
        if ((int)$p['deleted'] > 0) {
            $content .= '<i>Eliminado</i><br>';
        } else {
            $content .= '<button type="submit" name="delete" value="' . $num . '">Eliminar</button><br>';
        }
        $content .= '<button type="submit" name="ban" value="' . $hash . '">Ban IP</button><br>';
        $content .= '</td></tr>';
    }

    $content .= '</table></form>';
    $content .= paginationHtml($pagenumber, $totalpages, 'manage', $config);

    echo renderPage($title, $content, $config, ['index.html' => 'Inicio']);
    exit;
}

// ===== BLOQUEO =====
if (file_exists($config['lockfile'])) {
    $hashedcookie = $_COOKIE[$config['managecookie']] ?? '';
    if (!hash_equals(adminSessionToken($config), $hashedcookie)) {
        showError($config, 'Las publicaciones están bloqueadas temporalmente.');
    }
}

/**
 * Traduce los códigos de error de subida de PHP a mensajes comprensibles para el usuario.
 */
function getUploadErrorMessage(int $errorCode): string {
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE => 'El archivo supera el límite permitido por el servidor.',
        UPLOAD_ERR_FORM_SIZE => 'El archivo supera el límite permitido por el formulario.',
        UPLOAD_ERR_PARTIAL => 'La subida se interrumpió antes de completarse.',
        UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'No hay un directorio temporal disponible para la subida.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en disco.',
        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.',
        default => 'Ocurrió un error desconocido al subir el archivo.',
    };
}

/**
 * Recibe un archivo subido por el usuario y lo guarda de forma segura en la carpeta de uploads.
 * Devuelve el nombre final, el tamaño y el MIME para almacenarlo en la base de datos.
 */
function storeUploadedImage(array $config, array $file): array {
    // Validación básica del formulario de subida.
    if (!isset($file['error']) || !is_array($file)) {
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
    $fileSize = (int)($file['size'] ?? 0);
    if ($fileSize > $config['maximagesize']) {
        throw new RuntimeException('La imagen supera el tamaño máximo de 3MB.');
    }

    // Convertimos el tipo MIME a una extensión segura para el nombre de archivo.
    $extension = match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        default      => 'bin',
    };

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
    $name    = trim($_POST['name']    ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $comment = trim($_POST['com']     ?? '');
    $parent  = (int)($_POST['parent'] ?? 0);

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
    if (isset($_FILES['image']) && is_array($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
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
    if ($parent > 0 && !threadExists($config, $parent)) showError($config, 'El hilo al que intentas responder no existe.');

    $name = ($name ?: $config['defaultname']);
    if ($config['forcedanonymity']) $name = $config['defaultname'];

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