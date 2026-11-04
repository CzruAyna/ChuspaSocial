# Integración continua

ChuspaSocial utiliza GitHub Actions para ejecutar verificaciones automáticas y tareas relacionadas con integración continua, despliegue y seguridad.

## Workflows

| Workflow | Cuándo corre | Qué verifica o ejecuta |
|---|---|---|
| `ci.yml` — CI | En Pull Requests dirigidos a `dev` o `main` | Verifica que el Pull Request tenga una descripción y que haga referencia a un issue mediante `Closes #`, `Fixes #` o `Refs #`. |
| `deploy.yml` — Deploy | En cada push a `main` | Ejecuta el workflow de despliegue. Actualmente contiene un paso de ejemplo pendiente de definir para el despliegue del proyecto. |
| `security.yml` — Security scan | En cada push a `dev` y en Pull Requests dirigidos a `main` | Ejecuta un análisis de seguridad mediante CodeQL para JavaScript/TypeScript. |

## Protección de ramas

Las ramas `main` y `dev` deben estar protegidas para evitar cambios directos y mantener las verificaciones del proyecto.

Las reglas de protección son:

- Los cambios deben realizarse mediante un Pull Request.
- Cada Pull Request requiere al menos **1 aprobación** antes de poder integrarse.
- Los **checks obligatorios** de GitHub Actions deben completarse correctamente antes de integrar el Pull Request.
- No se permite realizar **force push** sobre las ramas protegidas.
- Estas reglas son activadas y administradas por el docente.

### Rama `dev`

Los Pull Requests dirigidos a `dev` deben pasar las verificaciones correspondientes antes de ser integrados. El workflow `CI` comprueba la descripción del Pull Request y que este haga referencia a un issue.

Además, `Security scan` se ejecuta cuando se realiza un push a `dev`.

### Rama `main`

Los cambios hacia `main` deben realizarse mediante Pull Request y cumplir las reglas de protección antes de ser integrados.

El workflow `CI` verifica los Pull Requests dirigidos a `main`, mientras que `Security scan` realiza el análisis de seguridad en los Pull Requests dirigidos a `main`.

El workflow `Deploy` se ejecuta después de un push a `main` para realizar las tareas de despliegue configuradas.

## Cómo corregir errores de PHPCS

El workflow `CI`, definido en `.github/workflows/ci.yml`, ejecuta el paso `Ejecutar phpcs` mediante el comando `moodle-plugin-ci phpcs --max-warnings 0`. Esta verificación comprueba que el código PHP cumpla los estándares de estilo configurados para el proyecto. Como se utiliza `--max-warnings 0`, las advertencias también pueden provocar que la verificación falle.

### 1. Identificar el error en GitHub Actions

1. Abre el Pull Request en GitHub.
2. Entra en la sección de verificaciones (*Checks*) y abre el workflow `CI`.
3. Busca el paso `Ejecutar phpcs` y revisa su salida.
4. Identifica el archivo, el número de línea, el mensaje y el código del error.

Por ejemplo, una salida de PHPCS puede tener este formato:

```text
src/classes/example.php:25:1: ERROR: Missing file doc comment
```

Este ejemplo es ilustrativo. Indica que el problema está en `src/classes/example.php`, en la línea 25, columna 1. El mensaje describe el problema detectado y debe corregirse según las reglas del proyecto.

### 2. Localizar y corregir el problema

Abre el archivo indicado en el mensaje y ve a la línea señalada. Revisa también las líneas cercanas, porque el problema puede depender del contexto del código.

Antes de modificarlo, consulta el mensaje completo de PHPCS y conserva el comportamiento esperado del programa. No elimines código ni cambies la lógica únicamente para ocultar el error.

### 3. Ejecutar PHPCS localmente

Si las dependencias están instaladas, puedes revisar el código desde la raíz del repositorio con:

```bash
vendor/bin/phpcs
```

Para revisar un archivo concreto:

```bash
vendor/bin/phpcs src/classes/example.php
```

Sustituye la ruta del ejemplo por la del archivo que necesitas comprobar. La salida mostrará los problemas detectados y sus ubicaciones.

En Windows PowerShell, puedes utilizar las rutas equivalentes:

```powershell
.\vendor\bin\phpcs
```

O para un archivo concreto:

```powershell
.\vendor\bin\phpcs src\classes\example.php
```

### 4. Intentar corregir automáticamente con PHPCBF

PHPCBF intenta corregir automáticamente los problemas de estilo que admite. Desde la raíz del repositorio, puedes ejecutarlo así:

```powershell
.\vendor\bin\phpcbf
```

Para aplicarlo a un archivo concreto:

```powershell
.\vendor\bin\phpcbf src\classes\example.php
```

Revisa los cambios realizados después de la ejecución. PHPCBF no puede corregir todos los problemas, y algunas correcciones pueden requerir revisión manual.

### 5. Volver a ejecutar las verificaciones

Después de corregir el código, ejecuta de nuevo PHPCS:

```powershell
.\vendor\bin\phpcs
```

Si siguen apareciendo errores, revisa cada mensaje y corrige manualmente los problemas pendientes. Si no sabes cómo resolver alguno, consulta el estándar indicado por PHPCS o pide ayuda al equipo.

Finalmente, guarda los cambios, realiza el commit y actualiza tu Pull Request. GitHub Actions volverá a ejecutar las verificaciones correspondientes.

**Importante:** `moodle-plugin-ci phpcs --max-warnings 0` es el comando utilizado por el workflow `CI`. Los comandos locales con `vendor/bin/phpcs` y `vendor/bin/phpcbf` son alternativas para revisar y corregir el estilo durante el desarrollo; no sustituyen las verificaciones del Pull Request.

## Ejecución Local de Validaciones (CI)

### 1. Validar el Estándar de Código (PHPCS)

Estándar exigido por el CI: `moodle`

```bash
vendor/bin/phpcs --standard=moodle local/chuspasocial
```

### 2. Ejecutar Pruebas Unitarias (PHPUnit)

Primero se debe inicializar el entorno desde la carpeta raíz de Moodle:

```bash
php admin/tool/phpunit/cli/init.php
```

Luego, ejecuta las pruebas indicando la carpeta específica del plugin:

```bash
vendor/bin/phpunit local/chuspasocial/tests/
```
