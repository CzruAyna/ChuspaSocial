# ChuspaSocial

> Red social académica y marketplace de libros integrados en Moodle.

## ¿Qué es?

ChuspaSocial es un plugin para **Moodle 4.5 LTS** que convierte la página de inicio
(Dashboard) en un **muro social**. Al entrar a Moodle, cada estudiante ve las
publicaciones de sus materias, las noticias que le interesan según tags y un
**marketplace de libros** para comprar y vender entre miembros de la institución.

Todas las cuentas son institucionales: cada publicación y cada anuncio pertenece
a una persona identificada de la comunidad.

## Funcionalidades

### Muro social
- Publicaciones con texto e imágenes, comentarios y reacciones.
- Muro por materia: las publicaciones de un curso solo las ven los inscritos en él.
- **Tags / hashtags** para etiquetar publicaciones, seguir temas y filtrar el muro.
- Seguir a otros usuarios y ver su perfil social.
- Noticias según tags, que llegan de tres fuentes:
  - publicaciones de usuarios,
  - anuncios oficiales de docentes y administradores,
  - actividad de Moodle (tareas nuevas, foros, calificaciones).
- Notificaciones de comentarios, reacciones y nuevos seguidores, más mensajes directos.
- Opción de reportar contenido inapropiado.

### Marketplace de libros
- Anuncios de libros vinculados a una o varias materias ("libros para tu materia").
- Anuncio con precio; el pago y la entrega se acuerdan fuera de la plataforma.
- Contacto con el vendedor por la mensajería de Moodle o, si el vendedor lo
  indica, por un medio externo.

## Tecnologías

| Componente | Tecnología |
|---|---|
| Plataforma | Moodle 4.5 LTS (plugin tipo `local_chuspasocial` + bloque de Dashboard) |
| Backend | PHP 8.1 – 8.3, APIs de Moodle (DML, Events, Tags, Messaging, Capabilities) |
| Base de datos | MariaDB / MySQL / PostgreSQL (vía XMLDB de Moodle) |
| Frontend | Plantillas Mustache, módulos JavaScript AMD, Bootstrap (tema Boost) |
| Dependencias | Composer 2, Node.js 20 + Grunt (para compilar JS) |
| Calidad | PHPUnit, Behat, PHP_CodeSniffer con el estándar `moodle-cs` |
| CI | GitHub Actions + `moodle-plugin-ci` |

**Extensiones PHP obligatorias** (Moodle 4.5 LTS):

`ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `iconv`, `intl`, `json`, `mbstring`, `openssl`, `pcre`, `simplexml`, `sodium`, `spl`, `xml`, `xmlreader`, `zip`, `zlib`

Además, la extensión de tu base de datos (`pgsql` o `mysqli`).

**Recomendadas:** `exif`, `soap`, `tokenizer`

La interfaz está disponible en español e inglés (`lang/es`, `lang/en`). El código
se escribe en inglés, según la convención de Moodle.

## Primeros pasos

### 1. Requisitos

Instala lo siguiente:

- **PHP 8.1 – 8.3** con las extensiones `intl`, `mbstring`, `xml`, `zip`, `gd`,
  `curl`, `sodium`, `openssl` y `mysqli` o `pgsql`. En Windows, **Laragon** o
  **XAMPP** traen PHP y MariaDB ya configurados.
- **Composer 2**: <https://getcomposer.org>
- **Git**
- **Node.js 20** (se recomienda instalarlo con `nvm`)
- Un servidor de base de datos: **MariaDB/MySQL** o **PostgreSQL**

Comprueba que todo esté instalado:

```bash
php -v
composer -V
node -v
```

### 2. Haz fork y clona el repositorio

Primero haz **Fork** de `sis-inf/ChuspaSocial` en GitHub. Luego clona tu fork:

```bash
git clone https://github.com/TU-USUARIO/ChuspaSocial.git
cd ChuspaSocial
git remote add upstream https://github.com/sis-inf/ChuspaSocial.git
git checkout dev
```

### 3. Descarga Moodle 4.5

Clona Moodle en una carpeta **hermana** de ChuspaSocial, no dentro de ella:

```bash
cd ..
git clone -b MOODLE_405_STABLE --depth 1 https://github.com/moodle/moodle.git
```

Estructura resultante:

```
Proyectos/
├── ChuspaSocial/   ← este repositorio (el código del plugin está en src/)
└── moodle/         ← Moodle 4.5
```

### 4. Instala las dependencias de Moodle con Composer

Con esto se instalan PHPUnit, Behat y las demás herramientas de desarrollo:

```bash
cd moodle
composer install
```

### 5. Enlaza el plugin dentro de Moodle

Moodle busca los plugins locales en `moodle/local/`. Crea un enlace para que
`moodle/local/chuspasocial` apunte a `ChuspaSocial/src`. Así editas en tu repo y
Moodle ve los cambios al instante.

**Linux / macOS:**

```bash
ln -s "$(pwd)/../ChuspaSocial/src" local/chuspasocial
```

**Windows (PowerShell, desde la carpeta `moodle`):**

```powershell
New-Item -ItemType Junction -Path local\chuspasocial -Target ..\ChuspaSocial\src
```

### 6. Crea la base de datos e instala Moodle

1. Crea una base de datos vacía llamada `moodle` (con cotejamiento `utf8mb4_unicode_ci`
   en MySQL/MariaDB).
2. Crea una carpeta de datos **fuera** de la carpeta de Moodle, por ejemplo `../moodledata`.
3. Ejecuta el instalador:

```bash
php admin/cli/install.php --lang=es --wwwroot=http://localhost/moodle --dataroot=../moodledata --dbtype=mariadb --dbname=moodle --dbuser=root --dbpass= --fullname="ChuspaSocial Dev" --shortname=chuspa --adminpass=Admin123! --agree-license --non-interactive
```

Ajusta `--wwwroot`, `--dbtype` (`mariadb`, `mysqli` o `pgsql`) y las credenciales
a tu entorno. Si usas Laragon o XAMPP, la carpeta `moodle` debe quedar dentro
de `www/` o `htdocs/`.

### 7. Instala o actualiza el plugin

Cada vez que cambies `version.php` o el esquema de la base de datos (`db/`), ejecuta:

```bash
php admin/cli/upgrade.php --non-interactive
php admin/cli/purge_caches.php
```

Abre `http://localhost/moodle` e inicia sesión con el usuario `admin`.

### 8. Activa el modo desarrollador

Agrega lo siguiente a `moodle/config.php`, antes de la línea `require_once`:

```php
$CFG->debug = E_ALL;
$CFG->debugdisplay = 1;
$CFG->cachejs = false;
$CFG->cachetemplates = false;

// Para las pruebas unitarias
$CFG->phpunit_prefix = 'phpu_';
$CFG->phpunit_dataroot = '/ruta/a/moodledata_phpunit';
```

### 9. Herramientas de calidad

**Estándar de código de Moodle (phpcs):**

```bash
composer global require moodlehq/moodle-cs
phpcs --standard=moodle ../ChuspaSocial/src
```

**Pruebas unitarias (PHPUnit), desde la carpeta `moodle`:**

```bash
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite local_chuspasocial_testsuite
```

**JavaScript (módulos AMD), desde la carpeta `moodle`:**

```bash
npm install
npx grunt amd --root=local/chuspasocial
```

> **Alternativa con Docker:** si no quieres instalar PHP y la base de datos en
> tu máquina, usa [moodle-docker](https://github.com/moodlehq/moodle-docker) y
> monta `src/` en `local/chuspasocial`.

## Estructura del repositorio

```
ChuspaSocial/
├── src/        código del plugin (se enlaza como moodle/local/chuspasocial)
├── docs/       requerimientos, arquitectura, API, despliegue
├── tests/      plan de pruebas, casos y reportes
├── data/       análisis de datos
├── security/   política y análisis de seguridad
└── .github/    plantillas de issues/PR y workflows de CI
```

## Documentación
Ver la carpeta [docs/](docs/)

## Contribuir
El proyecto usa el **Forking Workflow**: un issue = una rama = un PR hacia `dev`.
Lee [CONTRIBUTING.md](CONTRIBUTING.md) antes de empezar.

## Licencia
Este proyecto está licenciado bajo la [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).
