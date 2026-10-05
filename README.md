# Tenma

Tenma es un tablón anónimo de discusión inspirado en los *imageboards*. Está implementado principalmente en `index.php`, usa SQLite para guardar publicaciones y genera páginas estáticas para el índice, el tablón y cada hilo.

## Características

- Crear hilos y responder con texto y una imagen opcional.
- Citar publicaciones con `>>123`; los enlaces llevan al post y lo resaltan brevemente para encontrarlo con facilidad.
- Formato de texto: greentext (`>texto`), pinktext (`<texto`), negrita (`**texto**`), cursiva (`~texto~`) y subrayado (`_texto_`).
- Interfaz adaptable a escritorio y móvil, con actividad reciente, galería de imágenes y estadísticas.
- Ocultar publicaciones en el navegador, reportar publicaciones y eliminar las propias.
- Panel de administración para revisar reportes, eliminar publicaciones, banear, bloquear publicaciones, regenerar las páginas y cerrar la sesión.
- Imágenes JPG, PNG, GIF y WEBP de hasta 3 MB.
- Páginas estáticas y URLs amigables para los hilos: `/threads/<id>/`.
- Reglas de reescritura y protecciones de acceso generadas en archivos `.htaccess`.

## Requisitos

- PHP 8.1 o superior.
- Extensiones PHP `pdo_sqlite` y `mbstring`.
- Apache con `mod_rewrite` y `AllowOverride` habilitado para utilizar las URLs amigables y las reglas de `.htaccess`.
- Permisos de escritura para PHP en la carpeta del proyecto.

## Instalación

1. Copia el proyecto a la carpeta pública del servidor. En XAMPP, por ejemplo:

   ```text
   C:\xampp\htdocs\Tenma
   ```

2. Configura Apache para permitir `.htaccess` en esa carpeta y habilita `mod_rewrite`.

3. Da permisos de escritura a PHP sobre el directorio del proyecto. Tenma crea cuando haga falta `database/`, `uploads/` y `threads/`, además de la base de datos y los archivos generados.

4. Abre `index.php` y revisa la configuración inicial, especialmente el usuario y la contraseña de administración, las rutas y los valores de publicación.

5. Abre el sitio en el navegador, por ejemplo:

   ```text
   http://localhost/Tenma/
   ```

En el primer acceso, Tenma inicializa la base de datos, crea las reglas `.htaccess` que falten y genera las páginas estáticas.

## Configuración

La configuración se encuentra en el arreglo `$config`, al principio de `index.php`. Entre los valores principales están:

- `title`, `subtitle` y los colores: identidad y apariencia del sitio.
- `rules` y `helps`: contenido de las páginas de reglas y ayuda.
- `databasefolder`, `databasefile`, `uploadfolder` y `threaddir`: ubicaciones de datos, imágenes y páginas de hilo.
- `postsperpage` y `maxpages`: cantidad de hilos por página y número máximo de páginas del tablón.
- `forcedanonymity`: fuerza el nombre anónimo aunque se introduzca otro.
- `cooldown`, `namelimit`, `subjectlimit` y `commentlimit`: límites de publicación.
- `maxreplieshown`: respuestas mostradas en las vistas resumidas.
- `maximagesize` y `allowedtypes`: tamaño y tipos permitidos para las imágenes.
- `badstrings`: frases que se rechazan en publicaciones.
- `trustedproxies`: IPs o rangos CIDR de proxies inversos de confianza.

Solo se confía en cabeceras como `CF-Connecting-IP` y `X-Forwarded-For` cuando la conexión llega desde una IP incluida en `trustedproxies`. Déjalo vacío si el servidor no está detrás de un proxy de confianza.

## Uso

- `index.html` muestra la actividad reciente, las imágenes y las estadísticas.
- `board.html` muestra los hilos; las páginas siguientes usan nombres como `board-1.html`.
- Para abrir un hilo, usa `/threads/<id>/`. Las URLs antiguas `/thread-<id>.html` se redirigen a la ruta nueva.
- En la ayuda del sitio se muestran los formatos de texto admitidos. Al seguir un enlace de cita, el post de destino se resalta temporalmente.
- Las opciones de reportar, ocultar y buscar una imagen aparecen en el menú de acciones de cada publicación.
- Un usuario puede eliminar las publicaciones asociadas a su propia IP/hash desde el tablón.

## Administración

Abre `index.php?mode=manage` para iniciar sesión. El usuario inicial es `admin` y la contraseña inicial es `admin123`; **cámbiala antes de publicar el sitio**.

Para cambiarla, genera un hash en PHP:

```php
echo password_hash('tu-contraseña-nueva', PASSWORD_DEFAULT);
```

Sustituye el valor de `adminpasswordhash` en `$config` por el hash generado. El panel muestra un aviso mientras detecta la contraseña predeterminada. Incluye limitación de intentos de inicio de sesión y protección CSRF para las acciones administrativas.

Desde el panel puedes bloquear temporalmente las publicaciones, gestionar reportes, eliminar publicaciones, banear hashes de IP, regenerar los archivos estáticos y cerrar la sesión de administración. Al regenerar las páginas también se actualizan los recursos estáticos `style.css` y `tenma.js`.

## Seguridad y datos

- Las contraseñas se verifican con `password_verify`; configura una contraseña propia antes de exponer el sitio.
- Los hashes usados para baneos se derivan mediante HMAC y un secreto local en `database/iphash.secret`. El token de sesión administrativa depende de `database/admin.secret`. Ambos secretos se crean automáticamente; no los borres ni los compartas.
- No confíes en cabeceras de proxy que no provengan de proxies configurados explícitamente.
- Los `.htaccess` generados deshabilitan el listado de directorios, bloquean el acceso directo a archivos sensibles y limitan tipos de contenido. Estas protecciones dependen de que Apache aplique las reglas.
- Los datos de publicaciones se guardan en `database/tenma.sqlite`; las imágenes se guardan en `uploads/`. Haz copias de seguridad de ambos directorios.

## Archivos generados

- `index.html`: índice y actividad del sitio.
- `board.html`, `board-<n>.html`: páginas del tablón.
- `threads/thread-<id>.html`: archivo estático de cada hilo, servido normalmente mediante `/threads/<id>/`.
- `rules.html`, `help.html` y `error.html`: reglas, ayuda y página de error 404.
- `style.css`, `tenma.js`, `favicon.ico`, `robots.txt` y `sitemap.xml`: recursos del sitio.
- `.htaccess` y reglas `.htaccess` dentro de `database/` y `threads/`: reescritura y controles de acceso.

Las páginas y recursos se regeneran al publicar, moderar o usar la acción correspondiente en el panel. Si actualizas el código y necesitas actualizar las páginas ya generadas, inicia sesión en administración y pulsa **Regenerar HTML**.

## Estructura de carpetas

- `database/`: base de datos SQLite y secretos locales.
- `uploads/`: imágenes subidas.
- `threads/`: HTML estático de los hilos.

## Licencia

El proyecto no incluye una licencia explícita.
