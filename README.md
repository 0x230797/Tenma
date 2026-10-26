# Tenma

Tenma es un tablón anónimo de discusión inspirado en los *imageboards*. La aplicación está implementada principalmente en `index.php`, utiliza SQLite para guardar publicaciones, reportes, baneos y ajustes, y genera HTML estático para las páginas públicas. PHP sirve las páginas generadas, procesa publicaciones y reportes, y ejecuta las acciones administrativas.

## Funciones principales

- Crear hilos con imagen y responder con texto, imagen o ambos.
- Citar publicaciones con `>>123`; mostrar enlaces de vuelta a las publicaciones que las citan.
- Dar formato a comentarios: greentext (`>texto`), pinktext (`<texto`), negrita (`**texto**`), cursiva (`~texto~`) y subrayado (`_texto_`).
- Mostrar actividad reciente, imágenes y estadísticas en la página principal.
- Usar el tablón desde escritorio o móvil, ocultar publicaciones localmente en el navegador y eliminar las propias.
- Reportar contenido desde el menú de una publicación.
- Administrar reportes, publicaciones, baneos, avisos, hilos fijados o bloqueados y el estado general de las publicaciones.
- Banear por el hash asociado a una publicación durante 1 hora, 1 día, 7 días, 30 días o permanentemente.
- Generar páginas HTML, estilos, JavaScript, iconos, reglas, ayuda, sitemap y archivos de control para Apache.

El tablón no requiere cuentas: el nombre es opcional y, si se deja vacío, se usa `defaultname`. `forcedanonymity` permite ignorar los nombres ingresados. Si se inicia sesión en el panel, las publicaciones realizadas durante esa sesión aparecen identificadas como «Administrador».

## Requisitos

- PHP 7.4 o superior.
- Extensiones `pdo_sqlite` y `mbstring`.
- Apache con `mod_rewrite` y `AllowOverride` habilitado para que funcionen las rutas amigables y las reglas `.htaccess`.
- Permisos de lectura y escritura para PHP en el proyecto y sus carpetas de datos.
- Una sesión de PHP funcional para el acceso administrativo.

No se necesita Composer ni un proceso de compilación. Para recibir imágenes de hasta 3 MB, revisa también `upload_max_filesize` y `post_max_size` en la configuración de PHP: ambos deben permitir el tamaño configurado en Tenma.

## Instalación

1. Copia el proyecto en la carpeta pública del servidor; por ejemplo, en XAMPP:

   ```text
   C:\xampp\htdocs\Tenma
   ```

2. Habilita `mod_rewrite` y permite que Apache lea los archivos `.htaccess` del proyecto.
3. Asegúrate de que PHP pueda escribir en el proyecto. Tenma crea las carpetas de datos y los archivos generados al inicializarse.
4. Edita `$config` al principio de `index.php`. Como mínimo, cambia la contraseña inicial de administración antes de exponer el sitio.
5. Abre el sitio, por ejemplo, en `http://localhost/Tenma/`.

En una instalación nueva, el primer acceso prepara SQLite, crea carpetas y reglas `.htaccess` que falten, genera iconos y páginas, y redirige a `index.html`. Mantén `index.php` en la ubicación configurada: las rutas relativas de datos y recursos se resuelven desde el directorio de trabajo del sitio.

## Páginas y rutas

| URL o archivo | Uso |
| --- | --- |
| `index.html` | Página principal: actividad, galería de imágenes y estadísticas. |
| `board.html` | Primera página del tablón. |
| `board-1.html`, `board-2.html`, etc. | Páginas siguientes del tablón. |
| `/threads/<id>/` | Vista de un hilo; la regla de Apache la sirve desde el HTML generado del hilo. |
| `/thread-<id>.html` | Ruta antigua que Apache redirige a `/threads/<id>/`. |
| `index.php?mode=report&num=<id>` | Formulario para reportar una publicación. |
| `index.php?mode=manage` | Inicio de sesión y panel de administración. |

Las páginas públicas son archivos estáticos regenerados por PHP; esto reduce el trabajo necesario para servirlas, pero requiere que el servidor pueda escribir los archivos. La comprobación de baneos se ejecuta antes de servir las páginas, por lo que también impide a una IP baneada acceder al sitio.

## Configuración

Los valores configurables están en el arreglo `$config` al inicio de `index.php`.

### Identidad, apariencia y contenido

- `title`, `subtitle`, `timezone`: título, descripción y zona horaria.
- `gradient`, `background`, `section`, `textcolor`, `linkcolor`, `linkhover`, `fonts`, `formsidecolor`, `border`: estilo general.
- `postbackground`, `postborder`, `posternamecolor`, `postsubjectcolor`, `errortextcolor`: apariencia de publicaciones y mensajes.
- `menubackground`, `menuborder`, `menuhoverbackground`, `placeholderbackground`: colores de menús y marcadores.
- `reportcardbackground`, `reportcardborder`, `mutedtextcolor`, `helpbordercolor`, `helpdesccolor`: apariencia de reportes y ayuda.
- `mobilelinkbackground`, `mobilebuttonbordercolor`: detalles de la interfaz móvil.
- `defaultname`, `deletionphrase`: nombre predeterminado y texto mostrado en publicaciones eliminadas.
- `rules`, `helps`: contenido de las páginas generadas de reglas y ayuda. `helps` define el título, la sintaxis, la descripción y la vista previa de cada formato.

Los colores deben ser valores CSS válidos. Las vistas previas HTML de `helps` son contenido confiable definido por quien administra el sitio.

### Administración, archivos y límites

- `adminusername`, `adminpasswordhash`: credenciales de administración. `adminpasswordhash` debe ser un hash generado con `password_hash`, no una contraseña en texto claro.
- `managecookie`, `lockfile`, `tenmafile`, `rulesfile`, `helpfile`, `errorfile`: nombre de la cookie y ubicaciones/nombres de archivos usados por el sitio.
- `databasefolder`, `databasefile`, `uploadfolder`, `threaddir`: carpetas y archivo de datos de SQLite.
- `postsperpage`, `maxpages`: hilos por página y máximo de páginas públicas; los hilos que exceden ese límite se depuran junto con sus respuestas e imágenes.
- `forcedanonymity`: fuerza el nombre `defaultname`, aunque se introduzca otro.
- `cooldown`: intervalo mínimo en segundos entre publicaciones.
- `namelimit`, `subjectlimit`, `commentlimit`: límites de longitud.
- `maxreplieshown`: número de respuestas mostradas en el resumen de un hilo en el tablón.
- `maximagesize`, `allowedtypes`: tamaño máximo en bytes y tipos MIME permitidos para imágenes.
- `badstrings`: frases que impiden publicar cuando aparecen en el nombre, asunto o comentario.
- `trustedproxies`: lista de IP o rangos CIDR de proxies inversos de confianza.

Tenma utiliza `CF-Connecting-IP` o el primer valor de `X-Forwarded-For` únicamente cuando `REMOTE_ADDR` pertenece a `trustedproxies`. Deja la lista vacía si no usas un proxy de confianza. Configurarla incorrectamente puede hacer que el sitio identifique de forma equivocada a los visitantes y aplique baneos a la IP incorrecta.

## Uso del tablón

- Para crear un hilo se requiere un asunto, un comentario y una imagen.
- Para responder se requiere un comentario, una imagen o ambos. No se aceptan respuestas en hilos bloqueados.
- Se permiten imágenes JPG, PNG, GIF y WEBP de hasta el tamaño definido por `maximagesize`; el archivo también debe superar la validación de imagen del servidor.
- Las opciones de reportar, ocultar y buscar una imagen están en el menú de acciones de cada publicación. Las publicaciones del administrador, eliminadas, fijadas o bloqueadas no se pueden reportar desde ese menú.
- Ocultar una publicación solo la oculta en el navegador actual mediante `localStorage`; no la elimina del sitio.
- Para eliminar publicaciones propias, selecciónalas en el tablón y usa **Eliminar**. La comprobación usa los hashes de IP actuales y heredados.
- Al seguir una cita se abre o resalta la publicación correspondiente. Las referencias a publicaciones inexistentes se muestran como texto sin enlace.

## Administración

Abre `index.php?mode=manage` e inicia sesión. En la instalación inicial, el usuario es `admin` y la contraseña es `admin123`; cambia ambos valores de acuerdo con tu configuración antes de publicar el sitio. El panel avisa si detecta la contraseña inicial.

Para generar el hash de una contraseña, ejecuta PHP con un comando equivalente a:

```php
echo password_hash('tu-contraseña-nueva', PASSWORD_DEFAULT);
```

Copia el resultado en `adminpasswordhash` y cambia `adminusername` si corresponde. El panel aplica un límite de cinco intentos fallidos por IP durante una ventana de 15 minutos y protege sus operaciones con token CSRF.

El panel ofrece estas secciones:

- **Publicaciones:** revisar publicaciones, eliminarlas, banear el hash del autor con una duración elegida y fijar o bloquear hilos iniciales.
- **Reportes:** revisar el motivo y el contenido reportado, descartar el reporte, eliminar la publicación o banear el hash de su autor.
- **Baneos:** consultar hashes y vencimientos, y quitar baneos. Un baneo temporal vencido deja de bloquear aunque siga apareciendo en la lista.
- **Ajustes:** editar y activar un aviso importante, cerrar o reabrir publicaciones para visitantes y regenerar los archivos del sitio.
- **Cerrar sesión:** invalida las sesiones de administración existentes, no solo la cookie del navegador actual.

El bloqueo general afecta la creación de publicaciones de visitantes; una sesión administrativa autenticada puede publicar mientras está activo. Los baneos se aplican al hash de la IP de origen identificada por el servidor, no a una cuenta de usuario.

## Datos y archivos generados

| Ruta | Contenido |
| --- | --- |
| `database/tenma.sqlite` | Publicaciones, baneos, reportes, intentos de inicio de sesión y ajustes del sitio. |
| `database/iphash.secret` | Secreto local empleado para generar hashes HMAC de IP. |
| `database/admin.secret` | Secreto local para firmar las sesiones administrativas. |
| `uploads/` | Imágenes subidas y su `.htaccess` de protección. |
| `threads/` | HTML de cada hilo y su `.htaccess`. |
| `index.html`, `board*.html` | Página principal y páginas del tablón. |
| `rules.html`, `help.html`, `error.html` | Reglas, ayuda y página de error 404. |
| `style.css`, `tenma.js`, `favicon.ico` | Estilos, JavaScript e icono del sitio. |
| `admin.gif`, `thread-lock.gif`, `thread-pin.gif` | Iconos generados si no existen. |
| `robots.txt`, `sitemap.xml` | Directivas para rastreadores y sitemap. |
| `.htaccess` | Rutas amigables, página 404, cabeceras y reglas de acceso. |
| `uploads/.htaccess`, `database/.htaccess`, `threads/.htaccess` | Protección de archivos dentro de carpetas de datos. |
| `tenma.lock` | Presencia de este archivo indica que las publicaciones están bloqueadas. |

Las páginas se regeneran al crear o moderar publicaciones, cambiar ajustes y al pulsar **Regenerar HTML**. Si actualizas `index.php` o el CSS/JavaScript y el sitio ya existe, usa **Regenerar HTML** para actualizar los recursos generados.

Haz copias de seguridad de `database/`, `uploads/` y de cualquier configuración personalizada. Conserva `iphash.secret` junto con la base de datos: reemplazarlo cambia los hashes calculados para las IP y puede impedir que los hashes guardados en publicaciones antiguas coincidan con baneos. No publiques ni compartas los secretos. Las reglas `.htaccess` solo protegen los datos si Apache las procesa; configura permisos del sistema de archivos como defensa adicional.

## Organización de `index.php`

El archivo contiene la configuración, inicialización, operaciones SQLite, utilidades de seguridad, renderizadores HTML, generadores de recursos y controladores de solicitudes. A grandes rasgos, el flujo es:

1. Cargar `$config`, resolver rutas, iniciar la sesión y preparar directorios y reglas de Apache.
2. Identificar la IP de origen y derivar los hashes HMAC y heredado.
3. Inicializar SQLite y comprobar si el visitante tiene un baneo activo.
4. Servir páginas estáticas, manejar reportes o solicitudes administrativas.
5. Si la solicitud es una publicación, validar campos e imagen, guardar el registro y reconstruir las páginas.

## Referencia de funciones PHP

Las funciones siguientes están definidas en `index.php`. Las funciones generadoras de HTML devuelven contenido; `buildPages` escribe los archivos. Los manejadores de solicitud se ejecutan directamente en el flujo principal del archivo y no son funciones independientes.

### Rutas, red y sesión

| Función | Descripción |
| --- | --- |
| `siteBasePath()` | Obtiene el directorio base de la aplicación a partir de `SCRIPT_NAME`. |
| `siteUrl(string $path = '')` | Construye una URL local bajo el directorio base del sitio. |
| `threadFilePath(int $num, array $config)` | Construye la ruta del HTML generado para un hilo. |
| `threadUrl(int $num)` | Construye la URL amigable de un hilo. |
| `isHttps(array $config)` | Determina si la solicitud usa HTTPS, incluyendo el protocolo comunicado por un proxy de confianza. |
| `ipMatchesCidr(string $ip, string $cidr)` | Comprueba si una IP pertenece a una IP o rango CIDR IPv4/IPv6. |
| `isTrustedProxy(string $remoteAddr, array $config)` | Comprueba si la dirección remota está en la lista de proxies configurados. |
| `clientIp(array $config)` | Obtiene la IP de origen; solo consulta cabeceras de proxy si el servidor remoto es de confianza. |
| `ipHashSecret(array $config)` | Crea o lee, con bloqueo de archivo, el secreto local para derivar hashes de IP. |
| `matchesClientIpHash(string $storedHash, string $currentHash, string $legacyHash)` | Compara un hash almacenado con los formatos actual y heredado. |
| `startAdminSession(array $config)` | Inicia la sesión administrativa con cookie `HttpOnly`, `SameSite=Lax` y atributo `Secure` cuando corresponde. |

### Base de datos, publicaciones, reportes y baneos

| Función | Descripción |
| --- | --- |
| `getPDO(array $config)` | Abre y reutiliza la conexión SQLite; crea tablas, índices y columnas faltantes del esquema. |
| `getSiteSetting(array $config, string $key)` | Lee un ajuste del sitio; devuelve una cadena vacía si no existe. |
| `setSiteSetting(array $config, string $key, string $value)` | Inserta o actualiza un ajuste del sitio. |
| `ensureThreadActionIcons()` | Crea los iconos del administrador, bloqueo y fijado si faltan. |
| `readPosts(array $config)` | Lee todas las publicaciones ordenadas por número. |
| `getPostByNum(array $config, int $num)` | Busca una publicación por número; devuelve `null` si no existe. |
| `setThreadFlag(array $config, int $num, string $flag, bool $enabled)` | Cambia el estado `locked` o `pinned` de un hilo inicial. |
| `getRepliesOf(array $config, int $parent)` | Lee las respuestas asociadas a un identificador padre. |
| `insertPost(array $config, array $data)` | Inserta una publicación y devuelve el número asignado. |
| `markPostDeleted(array $config, int $num)` | Marca una publicación como eliminada, conservando su registro. |
| `deletePostImageFile(array $config, array $post)` | Elimina el archivo de imagen asociado a una publicación, si existe. |
| `deletePostCompletely(array $config, int $num)` | Quita imagen y reportes, y marca la publicación como eliminada. No borra la fila de la base de datos. |
| `deleteThreadCompletely(array $config, int $threadnum)` | Elimina el hilo y sus respuestas de SQLite, además de sus imágenes, reportes y HTML. |
| `pruneOldThreads(array $config)` | Elimina hilos antiguos cuando se supera la capacidad configurada. |
| `getRecentPosts(array $config, int $limit)` | Recupera publicaciones recientes limitadas a una cantidad. |
| `checkLoginRateLimit(array $config, string $hash)` | Impide nuevos intentos de inicio de sesión si el hash alcanzó el límite. |
| `registerFailedLogin(array $config, string $hash)` | Registra o incrementa los intentos fallidos para un hash. |
| `clearLoginAttempts(array $config, string $hash)` | Limpia los intentos tras iniciar sesión correctamente. |
| `getActiveBanExpiration(array $config, string $hash, string $legacyHash = '')` | Devuelve la expiración del baneo activo encontrado o `null`; cero representa un baneo permanente. |
| `formatBanTimeRemaining(int $seconds)` | Convierte segundos restantes en texto legible en español. |
| `addBan(array $config, string $hash, int $expiresAt = 0)` | Crea o actualiza un baneo; la expiración cero es permanente. |
| `getBans(array $config)` | Recupera los baneos registrados para el panel. |
| `removeBan(array $config, string $hash)` | Elimina un baneo por hash. |
| `insertReport(array $config, int $num, string $reason)` | Registra un reporte ligado a una publicación. |
| `getReports(array $config)` | Recupera los reportes pendientes, del más reciente al más antiguo. |
| `deleteReport(array $config, int $id)` | Elimina un reporte por su identificador. |
| `deleteReportsForPost(array $config, int $num)` | Elimina los reportes ligados a una publicación. |

### Autenticación y protección del panel

| Función | Descripción |
| --- | --- |
| `adminSessionSecret(array $config)` | Crea o lee el secreto persistente usado para firmar sesiones de administración. |
| `rotateAdminSessionSecret(array $config)` | Cambia el secreto de sesión e invalida las cookies administrativas anteriores. |
| `adminSessionToken(array $config)` | Genera el token de administración firmado a partir del usuario, el secreto y el hash de contraseña configurados. |
| `isAdminAuthenticated(array $config)` | Comprueba la cookie administrativa contra el token actual. |
| `applyAdminNameField(string $html, array $config)` | Sustituye el campo de nombre de un HTML estático por el campo de administrador. |
| `generateAdminCsrfToken(array $config)` | Obtiene o crea el token CSRF de la sesión administrativa. |
| `verifyAdminCsrfToken(array $config, $token)` | Valida que el token recibido corresponda con el token de sesión. |

### Formato, plantillas y publicación

| Función | Descripción |
| --- | --- |
| `spanishDate()` | Formatea fecha y hora de publicación usando la zona horaria configurada. |
| `relativeTime(int $timestamp)` | Convierte un timestamp en una descripción relativa breve. |
| `showError(array $config, string $message)` | Renderiza un mensaje de error y termina la solicitud. |
| `formatBytes(int $bytes, int $precision = 2)` | Presenta un tamaño en bytes con unidades legibles. |
| `formatComment(string $text, array $allposts)` | Escapa y transforma citas y sintaxis de formato de comentarios. |
| `getThreads(array $posts)` | Agrupa respuestas bajo los hilos y los ordena por fijado y actividad reciente. |
| `footerHtml(array $config)` | Genera el pie de página compartido. |
| `renderPage(string $title, string $content, array $config, array $nav = [])` | Envuelve el contenido en la plantilla HTML común y enlaza CSS/JavaScript. |
| `generateStyles(array $config)` | Produce el CSS del sitio a partir de la configuración visual. |
| `generateScript()` | Devuelve el JavaScript de interacción del navegador. |
| `generateErrorPage(array $config)` | Genera la página de error 404. |
| `renderImageBlock(array $post, bool $large, array $config)` | Renderiza información, enlace y vista previa de una imagen existente. |
| `renderThreadStatusIcons(array $post)` | Renderiza los iconos de hilo fijado y bloqueado. |
| `renderPostAuthor(array $post, array $config)` | Escapa y renderiza el nombre y distintivo del administrador, si aplica. |
| `renderPostBacklinks(array $post, array $allposts)` | Busca y muestra enlaces desde otras publicaciones que citan esta. |
| `renderMobilePostInfo(array $post, array $allposts, int $threadnum, bool $isop, array $config, bool $showCheckbox = true, bool $adminView = false)` | Genera los metadatos y controles móviles de una publicación. |
| `renderPostMenu(array $post, int $threadnum, array $config)` | Genera el menú de acciones de visitante para reportar, ocultar o buscar una imagen. |
| `renderOP(array $post, array $allposts, bool $isthread, bool $summarize, array $config, bool $showCheckbox = true, bool $adminView = false)` | Renderiza una publicación inicial de hilo. |
| `renderReply(array $post, array $allposts, bool $summarize, array $config, bool $showCheckbox = true, bool $adminView = false)` | Renderiza una respuesta. |
| `buildPages(array $posts, array $config)` | Regenera páginas, estilos, scripts, sitemap, iconos y HTML de hilos. |
| `generateRules(array $config)` | Genera la página de reglas. |
| `generateHelp(array $config)` | Genera la página de ayuda y los ejemplos de sintaxis. |
| `generateIndex(array $allposts, array $config)` | Genera el índice con actividad reciente, galería y estadísticas. |
| `generateBoard(array $threads, int $pagenumber, int $totalpages, array $allposts, array $config)` | Genera una página del tablón con formulario, publicaciones y paginación. |
| `generateThread(array $thread, array $allposts, array $config)` | Genera el HTML de un hilo con sus respuestas y formulario para contestar. |
| `generateFavicon()` | Decodifica y devuelve el favicon integrado. |
| `generateRobots()` | Genera las directivas de `robots.txt`. |
| `generateSitemap(array $threads, array $config)` | Genera el sitemap XML usando el host y protocolo de la solicitud. |
| `paginationHtml(int $current, int $total, string $base, array $config, string $extraqs = '')` | Construye la navegación entre páginas del tablón o del panel. |

### Carga de archivos

| Función | Descripción |
| --- | --- |
| `getUploadErrorMessage(int $errorCode)` | Traduce un código de error de carga PHP a un mensaje en español. |
| `storeUploadedImage(array $config, array $file)` | Valida una imagen subida y la mueve a `uploadfolder`; devuelve nombre, tamaño y MIME. |

### JavaScript generado por `generateScript()`

El JavaScript incluido en `index.php` añade interacciones sin una dependencia externa:

- `quotePost(num)`: inserta una cita en el formulario o navega al hilo de destino.
- `tenmaHighlightPostFromHash()`: resalta la publicación indicada por el fragmento `#p<id>`.
- `tenmaUpdateTimes()`: actualiza los tiempos relativos de los elementos `data-ts`.
- `tenmaHiddenPosts()`: lee la lista local de publicaciones ocultas.
- `tenmaApplyHiddenPosts()`: aplica u oculta marcadores para esa lista.
- `tenmaHidePost(postId)` y `tenmaShowHiddenPost(postId)`: guardan o restauran publicaciones ocultas en `localStorage`.

## Seguridad y privacidad

- Las contraseñas se verifican con `password_verify`; no dejes las credenciales iniciales en un sitio público.
- La IP no se guarda en texto claro en las publicaciones: se guarda un hash HMAC de 16 caracteres basado en `database/iphash.secret`. El hash SHA-1 heredado se comprueba para mantener compatibilidad con publicaciones y baneos de versiones anteriores.
- `database/iphash.secret` y `database/admin.secret` son secretos privados del sitio. No los borres, regeneres manualmente, expongas por HTTP ni compartas. Inclúyelos en copias de seguridad protegidas junto con la base de datos.
- El contenido de usuarios se escapa antes de renderizarse y las imágenes se validan por contenido, tipo y tamaño.
- Los formularios administrativos validan CSRF. Las sesiones de administración utilizan cookies `HttpOnly` y `SameSite`; al cerrar sesión se rotan las sesiones existentes.
- Los `.htaccess` generados configuran rutas amigables y restringen archivos sensibles. Estas reglas dependen de Apache y no sustituyen los permisos del sistema de archivos.
- `trustedproxies` debe contener exclusivamente proxies controlados por el operador. No se debe confiar en una cabecera reenviada desde cualquier cliente.

## Funciones no incluidas y licencia

El código de la aplicación se concentra en `index.php`; `style.css`, `tenma.js`, páginas HTML, iconos y archivos `.htaccess` son recursos generados por el programa. No hay una suite de pruebas automatizada documentada en este repositorio.

El repositorio no incluye una licencia explícita. Consulta al titular del proyecto antes de redistribuirlo o reutilizarlo.
