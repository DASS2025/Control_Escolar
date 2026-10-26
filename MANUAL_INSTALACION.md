# Guía de instalación desde GitHub — Sistema Administrativo Escolar

Esta guía está dirigida a principiantes que quieren descargar y ejecutar el
sistema en su propia computadora con **Windows y XAMPP**. Es una instalación
local para aprendizaje y pruebas, no una guía para publicar el sistema en Internet.

Repositorio: <https://github.com/DASS2025/Control_Escolar>

## 1. Resumen del proceso

1. Instalar las herramientas (XAMPP, Git, Composer y Node.js).
2. Clonar el repositorio.
3. Crear una base de datos vacía.
4. Configurar el archivo `.env`.
5. Instalar dependencias, crear las tablas con migraciones y cargar datos de prueba.
6. Compilar el frontend e iniciar el sistema.

No necesitas importar ningún archivo `.sql`: **las migraciones crean todas las
tablas**. El archivo `saes_db` de la raíz del proyecto es una base SQLite antigua
que no se usa en esta instalación; no lo importes en phpMyAdmin.

## 2. Qué vas a instalar

| Herramienta | Requisito o recomendación | Para qué sirve |
|---|---|---|
| Git para Windows | Versión estable | Descargar el repositorio |
| XAMPP | Distribución con PHP 8.2 o superior compatible con Composer | Incluye PHP, servidor de base de datos y phpMyAdmin |
| PHP | 8.2 o superior dentro de la rama 8.x; verificar con Composer | Ejecutar Laravel 12 |
| Composer | 2.x | Instalar las dependencias de PHP |
| Node.js | Recomendado: 24 LTS; también 22.12.0 o superior en la rama 22 | Compilar los recursos del frontend |
| npm | El incluido con Node.js | Instalar las dependencias del frontend |
| MySQL/MariaDB | Servidor incluido en XAMPP | Guardar la información del sistema |
| Editor de texto | Recomendado: Visual Studio Code | Editar la configuración |
| Navegador | Edge, Chrome o Firefox actualizado | Abrir el sistema |

Vite 7 y el complemento de Laravel requieren Node.js
`^20.19.0 || >=22.12.0`. **Node.js 18 no es compatible**. Usa una versión LTS
vigente que cumpla ese requisito, preferentemente Node.js 24.

Necesitas conexión a Internet para descargar herramientas y dependencias.
Las pantallas también cargan Bootstrap e iconos desde un CDN, así que sin
Internet su apariencia puede verse afectada.

No necesitas instalar Laravel globalmente, Docker, Redis ni un servicio de correo
para seguir esta guía con la configuración de ejemplo.

### Descargas oficiales

- Git: <https://git-scm.com/downloads/win>
- XAMPP: <https://www.apachefriends.org/download.html>
- Composer: <https://getcomposer.org/download/>
- Node.js: <https://nodejs.org/>
- Visual Studio Code (opcional): <https://code.visualstudio.com/>

## 3. Instalar las herramientas

1. Instala **XAMPP** en `C:\xampp`. Comprueba que su PHP sea 8.2 o superior.
2. Instala **Git para Windows**. Permite usar Git desde la línea de comandos.
3. Instala **Composer** con su instalador para Windows. Cuando solicite el
   ejecutable de PHP, selecciona `C:\xampp\php\php.exe`.
4. Instala **Node.js LTS**, incluyendo npm y la opción de agregarlo al `PATH`.
5. Si `php` no se reconoce, busca en Windows **Editar las variables de entorno
   de esta cuenta**. Edita `Path`, agrega `C:\xampp\php` como una entrada nueva
   y guarda. No borres las entradas existentes.
6. Cierra las terminales abiertas y abre una nueva ventana de **PowerShell**.

> Todos los comandos siguientes están escritos para PowerShell. Ejecuta cada
> bloque en orden. No copies los símbolos de las cercas de código ni el texto
> del indicador de la terminal. Si un comando falla, resuelve el error antes
> de continuar.

### Comprobar las instalaciones

```powershell
git --version
php -v
composer --version
node -v
npm.cmd -v
```

Cada comando debe mostrar una versión. Se usa `npm.cmd` para evitar el bloqueo
de `npm.ps1` por la política de ejecución de PowerShell; no necesitas cambiar
esa política.

Comprueba además qué PHP utiliza la terminal y qué configuración carga:

```powershell
where.exe php
php --ini
php -m
```

Si tienes varias instalaciones, verifica que la primera ruta de PHP sea la que
quieres utilizar, normalmente `C:\xampp\php\php.exe`.

### Extensiones de PHP

Laravel y las dependencias de desarrollo necesitan: `ctype`, `dom`, `fileinfo`,
`filter`, `hash`, `iconv`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `PDO`,
`Phar`, `session`, `tokenizer`, `xml` y `xmlwriter`. Para conectarte a MySQL
también necesitas `pdo_mysql`. Habilita `curl` y `zip` para facilitar la descarga
y extracción de paquetes.

Muchas vienen activadas o integradas en PHP. Si falta una extensión disponible,
abre el `php.ini` que indica `php --ini` y quita el `;` inicial de su línea,
por ejemplo `;extension=pdo_mysql`. Guarda y comprueba otra vez con `php -m`.
Si Apache está iniciado, reinícialo para que también cargue el cambio.
No descargues DLL de sitios desconocidos.

## 4. Clonar el repositorio de GitHub

**Clonar** significa descargar una copia del código y de su historial.
En PowerShell ejecuta:

```powershell
Set-Location C:\xampp\htdocs
git clone https://github.com/DASS2025/Control_Escolar.git System_Admin_Escolar
Set-Location C:\xampp\htdocs\System_Admin_Escolar
Get-ChildItem
```

El nombre final de la carpeta se fija en el comando para que las rutas de esta
guía coincidan. Debes ver `artisan`, `composer.json`, `package.json` y carpetas
como `app`, `database` y `resources`.

- Si Git indica que la carpeta ya existe y no está vacía, **no la borres**:
  puede ser otra instalación. Usa una carpeta nueva y adapta las rutas.
- Si aparece `Repository not found`, comprueba la URL y que tu cuenta tenga
  acceso si el repositorio es privado. No pongas contraseñas ni tokens en la URL.
- No es necesario usar la opción de GitHub **Download ZIP** si ya clonaste.

Es normal que falten `.env`, `vendor`, `node_modules` y `public\build`:
se generan localmente y no se descargan con Git.

## 5. Iniciar la base de datos

1. Abre **XAMPP Control Panel**.
2. Presiona **Start** en **MySQL** y comprueba que quede en verde.
3. Inicia también **Apache** si vas a utilizar phpMyAdmin.
4. Abre <http://localhost/phpmyadmin>.
5. Selecciona **Nueva**, escribe `saes_db` y elige el cotejamiento
   `utf8mb4_unicode_ci`.
6. Presiona **Crear**.

Para esta guía usa una **base nueva destinada a pruebas**. No utilices una base
con información de otra instalación.

Aunque XAMPP muestre el botón “MySQL”, puede incluir MariaDB. Laravel utiliza
`DB_CONNECTION=mysql` también para ese servidor.

## 6. Crear y editar la configuración local

Desde la carpeta del proyecto:

```powershell
Copy-Item .env.example .env
notepad .env
```

Ejecuta la copia **solo si todavía no existe `.env`**; no sobrescribas una
configuración que ya estés usando.

En el editor, busca las variables correspondientes y déjalas así, sin duplicarlas:

```dotenv
APP_NAME="Sistema Administrativo Escolar"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saes_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
MAIL_MAILER=log
```

Guarda y cierra el editor.

- En una instalación predeterminada de XAMPP, `root` normalmente no tiene
  contraseña. Si configuraste una, usa la real en `DB_PASSWORD`.
- Si la contraseña contiene espacios o `#`, enciérrala entre comillas dobles.
- Si cambiaste el puerto de MySQL, actualiza `DB_PORT`.
- No compartas ni subas `.env` a GitHub: contiene configuración privada.
- `APP_DEBUG=true` y el usuario `root` sin contraseña son únicamente para el
  entorno local de esta guía.

## 7. Instalar las dependencias de PHP

En la misma carpeta:

```powershell
composer install
composer check-platform-reqs
php artisan key:generate
php artisan config:clear
```

`composer install` usa las versiones de `composer.lock` y genera `vendor`.
La comprobación de plataforma debe terminar sin requisitos faltantes.
`key:generate` escribe una clave propia en `APP_KEY`.

No uses `composer update` ni `--ignore-platform-reqs` para saltarte errores de
instalación. Corrige primero la versión de PHP o las extensiones indicadas.
No vuelvas a generar la clave de una instalación que ya tenga datos cifrados.

## 8. Crear las tablas y cargar los datos de prueba

Con MySQL activo y la base `saes_db` vacía, ejecuta:

```powershell
php artisan migrate
php artisan migrate:status
```

Todas las migraciones deben terminar en `DONE` y aparecer como `Ran`, incluida
`0001_01_01_000003_create_escolar_base_tables`, que crea las tablas escolares
(roles, usuarios, carreras, alumnos, docentes, grupos, kardex, asistencias, etc.).

Después carga los datos iniciales:

```powershell
php artisan db:seed
```

Esto crea roles, carreras, materias, aulas, periodos, grupos y usuarios de
prueba. Al terminar se muestra una tabla con las cuentas creadas. Las principales:

| Rol | Correo | Contraseña |
|---|---|---|
| Admin | `admin@saes.edu.mx` | `Admin1234!` |
| Director | `director@saes.edu.mx` | `Director1234!` |
| Coordinador de Carrera | `coordinador@saes.edu.mx` | `Coord1234!` |
| Personal Administrativo | `administrativo@saes.edu.mx` | `Administ1234!` |
| Docente | `jperez@saes.edu.mx` | `Docente1234!` |
| Alumno | `alumno1@saes.edu.mx` | `Alumno1234!` |

**Estas credenciales son públicas y solo deben usarse para pruebas locales.**
El seeder no sobrescribe usuarios que ya existan, por lo que puedes ejecutarlo
de nuevo sin duplicar información.

> **Si ya tenías una base `saes_db` de una versión anterior** (con las tablas
> creadas a mano), `php artisan migrate` también es seguro: la migración base
> detecta las tablas existentes y no las modifica ni borra datos.

**Nunca ejecutes `migrate:fresh`, `migrate:refresh` ni `db:wipe` en una base con
datos que quieras conservar: borran información.**

## 9. Instalar y compilar el frontend

```powershell
npm.cmd ci
npm.cmd run build
```

`npm ci` instala las versiones del archivo de bloqueo sin actualizarlo.
La compilación debe terminar sin errores y crear `public\build\manifest.json`.

Para esta instalación basta con compilar una vez. No necesitas dejar
`npm run dev` abierto para navegar con los recursos compilados.

## 10. Preparar el almacenamiento y limpiar cachés

```powershell
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

`storage:link` conecta `public\storage` con `storage\app\public` para los archivos
públicos que utilice la aplicación. **No asigna permisos de escritura**.
Si indica que el enlace ya existe, no necesitas crearlo de nuevo.

Laravel debe poder escribir en `storage` y `bootstrap\cache`. Si Windows bloquea
la creación del enlace, prueba habilitar el **Modo de desarrollador** de Windows
o ejecutar una terminal como administrador únicamente para ese comando.
No des permisos de control total a “Todos” para solucionar un error.

## 11. Iniciar el sistema

Con MySQL todavía activo, ejecuta:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Deja esa terminal abierta y visita:

<http://127.0.0.1:8000/login>

Ingresa con la cuenta local creada en el paso 8. Debe abrirse el panel principal.
Usa siempre el mismo host (`127.0.0.1` en esta guía) para evitar problemas de sesión.

Este método utiliza el servidor de desarrollo de Laravel, no Apache. Apache
solo es necesario aquí para acceder a phpMyAdmin; MySQL sí debe seguir activo.
No abras el proyecto haciendo doble clic en un archivo PHP.

Si el puerto 8000 está ocupado, puedes usar:

```powershell
php artisan serve --host=127.0.0.1 --port=8001
```

En ese caso cambia `APP_URL` a `http://127.0.0.1:8001`, ejecuta
`php artisan config:clear` y entra a esa misma dirección.

## 12. Comprobar que la instalación quedó completa

- [ ] `composer check-platform-reqs` termina sin requisitos faltantes.
- [ ] La importación o las migraciones terminaron sin errores.
- [ ] `php artisan migrate:status` muestra todas las migraciones ejecutadas.
- [ ] El seeder terminó correctamente y existe una cuenta de prueba.
- [ ] `npm.cmd run build` terminó y existe `public\build\manifest.json`.
- [ ] Aparece el inicio de sesión con sus estilos e imágenes.
- [ ] La cuenta de prueba puede iniciar sesión y abrir el panel.
- [ ] Puedes abrir un catálogo, como Carreras o Aulas, sin errores de tablas.
- [ ] Puedes cerrar sesión y volver a entrar.

Que aparezca el formulario de acceso **no demuestra por sí solo** que la base
esté completa: también debes comprobar el inicio de sesión y los catálogos.

## 13. Cómo abrirlo otro día

No tienes que clonar ni instalar todas las dependencias cada vez:

1. Inicia **MySQL** desde XAMPP.
2. Abre PowerShell y ejecuta:

   ```powershell
   Set-Location C:\xampp\htdocs\System_Admin_Escolar
   php artisan serve --host=127.0.0.1 --port=8000
   ```

3. Abre <http://127.0.0.1:8000/login>.
4. Para detener el servidor, presiona **Ctrl+C** en esa terminal.

Si modificas CSS o JavaScript, vuelve a ejecutar `npm.cmd run build`.

## 14. Solución de problemas frecuentes

| Mensaje o problema | Qué revisar |
|---|---|
| `git`, `php`, `composer` o `node` no se reconoce | Instala la herramienta, verifica `Path` y abre otra terminal. Para PHP revisa `where.exe php`. |
| PowerShell bloquea `npm.ps1` | Ejecuta `npm.cmd`, como en esta guía, sin cambiar la política de seguridad. |
| Composer solicita una extensión `ext-...` | Comprueba `php --ini`, habilita la extensión y vuelve a ejecutar `composer install`. |
| Error de certificado SSL al descargar paquetes | Revisa la fecha del equipo y la configuración de certificados de PHP/Composer. No desactives TLS ni la verificación SSL. |
| `EJSONPARSE` en `package-lock.json` | Actualiza tu copia con `git pull`; el archivo correcto está en el repositorio. |
| `EBADENGINE` o versión de Node no compatible | Usa Node.js 24 LTS o una versión que cumpla `^20.19.0 || >=22.12.0`. |
| `Connection refused` o error 2002 | Inicia MySQL; verifica `DB_HOST` y `DB_PORT`. |
| MySQL de XAMPP no inicia o `Access denied` aunque `DB_PASSWORD` esté vacío | Otro MySQL instalado en Windows (por ejemplo, el servicio `MySQL80`) puede estar usando el puerto 3306. Revisa con `Get-Service *mysql*`. Detén ese servicio desde **Servicios** de Windows o cambia el puerto de XAMPP (por ejemplo a 3307) y `DB_PORT`. |
| `Access denied` o error 1045 | Revisa `DB_USERNAME` y `DB_PASSWORD`. Después ejecuta `php artisan config:clear`. |
| `Unknown database` o error 1049 | Crea `saes_db` o corrige `DB_DATABASE`. |
| No existe `Roles`, `Usuarios`, `Carreras` o `Alumnos` | Ejecuta `php artisan migrate` antes de `php artisan db:seed` y revisa `php artisan migrate:status`. |
| Tabla o columna duplicada al migrar | Tu base tiene un historial distinto. No borres tablas; revisa `php artisan migrate:status` y consulta al responsable. |
| No existe `sessions` o `cache` | La preparación de las tablas auxiliares está incompleta; revisa el historial y las migraciones. |
| `No application encryption key has been specified` | En una instalación nueva ejecuta `php artisan key:generate` y `php artisan config:clear`. |
| `Vite manifest not found` | Ejecuta `npm.cmd ci` y `npm.cmd run build` después de corregir el archivo de bloqueo. |
| Pantalla sin estilos o iconos | Comprueba la conexión a Internet y el acceso al CDN de Bootstrap. |
| Error 419 al iniciar sesión | Usa siempre la misma URL, revisa las tablas de sesión, limpia la configuración y vuelve a cargar el formulario. |
| Las credenciales de prueba no funcionan | Si la base ya existía, el seeder no cambia contraseñas de usuarios existentes. Consulta al responsable. |
| Apache no inicia por el puerto 80 | Revisa el puerto de Apache para phpMyAdmin. Laravel puede ejecutarse con `artisan serve` por separado. |
| Error de permisos al escribir archivos | Revisa los permisos del usuario sobre `storage` y `bootstrap\cache`; `storage:link` no los cambia. |

Para revisar un error de Laravel:

```powershell
Get-Content .\storage\logs\laravel.log -Tail 50
```

El archivo aparece cuando Laravel registra mensajes. Antes de compartir un log
o una captura, oculta contraseñas, tokens, correos y datos personales. No compartas
el archivo `.env`.

## 15. Límites de esta guía

Esta instalación escucha únicamente en tu computadora. No abras puertos del
router ni publiques la carpeta del proyecto como sitio accesible desde Internet.
Un despliegue real necesita, entre otras medidas, HTTPS, un usuario de base de
datos con permisos limitados, credenciales propias, `APP_DEBUG=false`, respaldos
y un servidor cuyo directorio público sea exclusivamente `public`.
