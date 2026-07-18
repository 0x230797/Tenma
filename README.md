# Tenma

Tenma es un tablón de discusión anónimo inspirado en sistemas de tipo "imageboard". Genera páginas estáticas a partir de publicaciones almacenadas en SQLite y ofrece URLs amigables para hilos en la forma `/threads/<id>/`. Todo desde un solo archivo.

## Características

- Interfaz de tablón anónimo con creación de hilos y respuestas.
- Control simple de moderación y reporte de publicaciones.
- Generación automática de páginas estáticas: `index.html`, `board.html`, `thread-<id>.html`, `rules.html`, `help.html`, `error.html`, `robots.txt` y `sitemap.xml`.
- Soporte para imágenes (JPG, PNG, GIF, WEBP) con límite de 3 MB.
- Enlaces amigables para hilos en `threads/<id>/`.
- Archivos y carpetas protegidos con `.htaccess` para evitar acceso directo a scripts y datos sensibles.

## Requisitos

- PHP 7.4 o superior.
- Extensión `pdo_sqlite` habilitada.
- Servidor web con soporte para `.htaccess` (Apache con `mod_rewrite` recomendado).
- Carpeta de proyecto accesible por el servidor web.

## Instalación

1. Copia el contenido del proyecto al directorio raíz de tu servidor web. Por ejemplo en XAMPP:

   ```sh
   C:\xampp\htdocs\Tenma
   ```

2. Asegúrate de que PHP puede escribir en las carpetas siguientes, o deja que el sistema las cree automáticamente:

   - `database/`
   - `uploads/`
   - `threads/`

3. Abre el archivo `index.php` y ajusta la configuración inicial si deseas cambiar los valores predeterminados.

4. Accede al sitio desde el navegador, por ejemplo:

   ```text
   localhost/Tenma/
   ```

5. El archivo `index.php` genera automáticamente los archivos estáticos y el `.htaccess` necesario para el funcionamiento básico.

## Configuración

La configuración se encuentra en la parte superior de `index.php` dentro del arreglo `$config`.

Valores importantes:

- `title`: título del tablón.
- `subtitle`: subtítulo o descripción corta.
- `databasefile`: ruta al archivo SQLite.
- `uploadfolder`: carpeta donde se guardan las imágenes.
- `threaddir`: carpeta donde se guardan las páginas de hilo.
- `postsperpage`: cantidad de hilos por página.
- `maxpages`: número de páginas de tablero a mantener.
- `allowedtypes`: tipos MIME permitidos para las imágenes.
- `maximagesize`: tamaño máximo de imagen en bytes.
- `trustedproxies`: lista de IPs/rangos CIDR de proxies inversos de confianza (por ejemplo, tu balanceador o Cloudflare). Solo si la petición llega desde una de estas direcciones se confía en cabeceras como `CF-Connecting-IP` o `X-Forwarded-For` para determinar la IP real y si la conexión es HTTPS. Vacío por defecto: cualquier cabecera de este tipo enviada directamente por el cliente se ignora, para que nadie pueda falsificar su IP y evadir un baneo.

### Seguridad

- **Cambia la contraseña de administrador** cuanto antes: genera un nuevo hash con `password_hash('tu-contraseña', PASSWORD_DEFAULT)` en PHP y sustituye el valor de `adminpasswordhash`. Mientras se detecte la contraseña por defecto (`admin123`), el panel de administración muestra un aviso.
- El panel de administración incluye protección contra fuerza bruta (bloqueo temporal tras varios intentos fallidos) y un botón "Cerrar sesión" que revoca de inmediato todas las cookies de administrador emitidas.
- Los hashes de IP usados para baneos incluyen un secreto local (`database/iphash.secret`) para que no puedan revertirse fácilmente si la base de datos se filtrara. También se guarda un secreto de sesión de administrador en `database/admin.secret`; ambos archivos se generan automáticamente y están protegidos por el `.htaccess` de `database/`. No los borres ni los compartas.
- No expongas `trustedproxies` a menos que el sitio esté realmente detrás de un proxy de confianza, ya que confiar en cabeceras de IP arbitrarias permite falsificar la IP de origen.

### Rutas y archivos generados

- `index.html`: página de inicio.
- `board.html` y `board-<n>.html`: páginas del tablón.
- `threads/thread-<id>.html`: página estática de cada hilo.
- `rules.html`: página de reglas.
- `help.html`: página de ayuda de formato.
- `error.html`: página de error 404.
- `.htaccess`: control de acceso y reglas de reescritura.

## Uso

- Navega al sitio principal para ver el índice y el tablón.
- Crea nuevos hilos o responde a publicaciones desde las páginas generadas.
- Los hilos aparecerán en URLs amigables como:

  ```text
  /thread-1.html
  ```

## Administración

Para regenerar las páginas estáticas después de cambios en la base de datos, ejecuta de nuevo el sitio desde el navegador o accede al modo administración si está habilitado.

## Notas adicionales

- El proyecto depende de que el servidor web respete `.htaccess` y tenga `AllowOverride` habilitado para la carpeta del proyecto.
- Si recibes un mensaje con el nombre `error.html` en lugar de la página completa de error, asegúrate de que la ruta `ErrorDocument` en el `.htaccess` generado apunte a la URL correcta.

## Estructura de carpetas

- `database/`: datos SQLite y archivo de base de datos.
- `uploads/`: imágenes subidas por los usuarios.
- `threads/`: páginas HTML generadas para cada hilo.

## Licencia

El proyecto no incluye una licencia explícita.