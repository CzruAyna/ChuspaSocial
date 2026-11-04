# Guía de Despliegue

## Prerrequisitos

Antes de instalar `local_chuspasocial` en un sitio Moodle de producción, el administrador debe confirmar que el servidor cumple con lo siguiente.

### Versiones soportadas

| Componente | Versión requerida | Cómo verificarlo |
|---|---|---|
| Moodle | 4.5 LTS | **Administración del sitio** > **Notificaciones**: la versión aparece al pie de la página. |
| PHP | 8.1, 8.2 u 8.3 | **Administración del sitio** > **Servidor** > **Información PHP**, o en consola: `php -v` |

> Moodle 4.5 también exige su propia base de datos compatible (por ejemplo MariaDB 10.6.7+, MySQL 8.0+ o PostgreSQL 13+). Si Moodle ya funciona en el servidor, este requisito está cubierto.

### Cron activo

El plugin usa tareas programadas de Moodle (notificaciones y procesos en segundo plano), por lo que el cron debe ejecutarse **cada minuto**.

1. Agregue la siguiente línea al crontab del usuario del servidor web (por ejemplo `www-data`), ajustando las rutas a su instalación:
   ```text
   * * * * * /usr/bin/php /var/www/moodle/admin/cli/cron.php > /dev/null
   ```
   Para editar el crontab de ese usuario: `sudo crontab -u www-data -e`
2. Verifique que el cron está corriendo en **Administración del sitio** > **Servidor** > **Tareas** > **Tareas programadas**: la columna de última ejecución debe mostrar horas recientes.
3. Si el cron no se ha ejecutado en las últimas 24 horas, Moodle muestra una advertencia en **Administración del sitio** > **Notificaciones**.

### Mensajería habilitada

El plugin envía notificaciones y permite contactar a vendedores del Marketplace mediante la mensajería de Moodle.

- Desde la interfaz: busque el ajuste `messaging` en el buscador de **Administración del sitio** y active **Habilitar sistema de mensajería**. Guarde los cambios.
- Desde la consola (en la carpeta raíz de Moodle):
  ```text
  php admin/cli/cfg.php --name=messaging --set=1
  ```

### Etiquetas (tags) habilitadas

El muro social usa la API de etiquetas de Moodle para los hashtags, el seguimiento de temas y el filtrado de publicaciones.

- Desde la interfaz: busque el ajuste `usetags` en el buscador de **Administración del sitio** (está en **Características avanzadas**) y active la funcionalidad de etiquetas. Guarde los cambios.
- Desde la consola:
  ```text
  php admin/cli/cfg.php --name=usetags --set=1
  ```

### Comprobación rápida

Los siguientes comandos deben devolver `1`:

```text
php admin/cli/cfg.php --name=messaging
php admin/cli/cfg.php --name=usetags
```

### Límites de subida y memoria en php.ini

El muro multimedia permite adjuntar hasta 4 imágenes por publicación (ajuste `maxpostimages`), así que PHP debe aceptar archivos de tamaño razonable y reservar memoria suficiente para validarlos y redimensionarlos. Valores sugeridos para un sitio de producción:

| Directiva | Valor sugerido | Para qué sirve |
|---|---|---|
| `upload_max_filesize` | `64M` | Tamaño máximo de cada imagen que se sube al muro. |
| `post_max_size` | `68M` | Tamaño máximo del cuerpo del POST; debe ser mayor que `upload_max_filesize` porque además de la imagen viajan los demás campos del formulario. |
| `memory_limit` | `256M` | Memoria para que Moodle procese y redimensione las imágenes antes de guardarlas. |

Notas:

- Ajuste los valores a la política del sitio. Si necesita archivos más grandes, suba `upload_max_filesize`, mantenga `post_max_size` por encima de ese valor y aumente también `memory_limit`.
- El tamaño efectivo no supera el ajuste **Tamaño máximo para subir archivos** de **Administración del sitio** > **Seguridad** > **Políticas del sitio**. Si las imágenes grandes se rechazan aunque php.ini permita más, revise ese ajuste.
- Con PHP-FPM o Apache, reinicie el servicio después de editar php.ini para que los cambios se apliquen.
- Compruebe los valores activos en **Administración del sitio** > **Servidor** > **Información PHP** o desde la consola:

```text
php -i | grep -E 'upload_max_filesize|post_max_size|memory_limit'
```

### Permisos de moodledata

El muro guarda las imágenes en el área de archivos de Moodle, dentro de `moodledata`, así que el usuario del servidor web necesita permisos de escritura en esa carpeta. Antes de instalar, verifique:

1. Que `moodledata` esté **fuera** del directorio público del sitio.
2. Que el usuario del servidor web (por ejemplo `www-data`) sea el propietario y pueda escribir:

   ```text
   ls -ld /var/www/moodledata
   sudo -u www-data test -w /var/www/moodledata && echo "moodledata con escritura para www-data"
   ```

   Si el segundo comando no imprime el mensaje, corrija el propietario o los permisos antes de continuar; la instalación y las subidas del muro fallan sin escritura en `moodledata`.
3. Que **Administración del sitio** > **Notificaciones** no muestre advertencias sobre la carpeta de datos.

---

## Instalación desde archivo ZIP (Producción)

Esta guía describe el procedimiento para instalar el plugin `local_chuspasocial` mediante la interfaz gráfica de Moodle para entornos de producción.

### Requisitos previos
- Cuenta con rol de **Administrador del sitio** en Moodle.
- Versión de Moodle 4.5 o superior.
- Archivo comprimido `local_chuspasocial.zip` descargado desde la sección de **Releases** del repositorio.

### Pasos de instalación
1. Inicie sesión en la plataforma Moodle como **Administrador del sitio**.
2. Navegue a **Administración del sitio** > **Plugins** > **Instalar plugins**.
3. En la sección **Cargar un archivo ZIP**, arrastre el archivo `local_chuspasocial.zip` o selecciónelo mediante el explorador de archivos.
4. En la casilla **Tipo de plugin**, asegúrese de seleccionar `Local plugin (local)`.
5. Presione el botón **Instalar plugin desde archivo ZIP**.
6. En la pantalla de verificación de requerimientos y compatibilidad, presione **Continuar**.
7. En la vista de actualización de la base de datos, haga clic en **Actualizar base de datos de Moodle ahora**.

---

## Ajustes del plugin

Una vez instalado, el administrador configura el plugin desde **Administración del sitio** > **Plugins** > **Plugins locales** > **ChuspaSocial**. Los ajustes se guardan en la base de datos de Moodle con el botón **Guardar cambios**; no hace falta editar `config.php` ni definir variables de entorno.

| Ajuste | Variable | Descripción | Valor por defecto |
| :--- | :--- | :--- | :--- |
| Habilitar marketplace | `enablemarketplace` | Activa o desactiva el marketplace del plugin. | `1` (activado) |
| Longitud máxima de publicación | `maxpostlength` | Cantidad máxima de caracteres por publicación. | `2000` |
| Máximo de imágenes por publicación | `maxpostimages` | Cantidad máxima de imágenes por publicación. | `4` |
| Publicaciones por página | `postsperpage` | Cantidad de publicaciones que se muestran por página. | `20` |
| Moneda | `currency` | Código de la moneda en que se expresan los precios. | `BOB` |
---

## Evidencia de validación en instalación limpia

Prueba realizada en una instalación limpia de Moodle 4.5.15, con el ZIP del
plugin generado desde la rama `dev` **sin ninguna modificación**.

| Componente | Versión |
| --- | --- |
| Moodle | 4.5.15 |
| PHP | 8.2.12 |
| MariaDB | 10.11.19 |

El ZIP se generó con `git archive --format=zip --prefix=chuspasocial/
upstream/dev:src` y se subió desde Administración del sitio > Plugins >
Instalar plugins.

Resultado: al continuar con la actualización de la base de datos, Moodle falla en `admin/upgradesettings.php` con este error:

```text
Exception - syntax error, unexpected variable "$settings", expecting ")"
```

Comprobación con PHP sobre el archivo instalado:

```text
PHP Parse error: syntax error, unexpected variable "$settings", expecting ")" in local\chuspasocial\settings.php on line 56
```

Causa: en `src/settings.php`, el ajuste `enablemarketplace` (líneas 51 a 55)
no se cierra con `));` antes de abrir el siguiente `$settings->add(` en la
línea 56. Es un defecto del plugin en `dev`, ajeno a esta guía, y se reporta
por separado. Hasta que se corrija, los pasos de ajustes de esta guía no se
pueden verificar en Moodle 4.5.

---

## Actualización del plugin

Procedimiento para actualizar `local_chuspasocial` a una nueva versión en un sitio Moodle de producción. Requiere acceso de **Administrador del sitio** y acceso al servidor donde está instalado Moodle.

### 1. Respaldo previo
Antes de cualquier cambio, respalde:
- La base de datos de Moodle.
- La carpeta de datos `moodledata`.
- La carpeta actual del plugin, por ejemplo:
  ```text
  cp -r /var/www/moodle/local/chuspasocial /root/respaldo-chuspasocial
  ```

### 2. Activar el modo mantenimiento
Desde la carpeta raíz de Moodle:
```text
php admin/cli/maintenance.php --enable
```

### 3. Reemplazar la carpeta `local/chuspasocial`
1. Descargue el archivo `local_chuspasocial.zip` de la nueva versión desde la sección de **Releases** del repositorio.
2. Elimine la carpeta anterior del plugin. **No copie la nueva versión encima de la anterior**: los archivos que fueron eliminados en la nueva versión quedarían en el servidor.
   ```text
   rm -rf /var/www/moodle/local/chuspasocial
   ```
3. Descomprima la nueva versión en `local/`, de modo que el resultado sea `local/chuspasocial/version.php`:
   ```text
   unzip local_chuspasocial.zip -d /var/www/moodle/local/
   ```
4. Devuelva los permisos al usuario del servidor web (por ejemplo `www-data`):
   ```text
   chown -R www-data:www-data /var/www/moodle/local/chuspasocial
   ```

### 4. Ejecutar la actualización
Elija **una** de las dos opciones.

**Opción A: desde Notificaciones (interfaz web)**
1. Inicie sesión como **Administrador del sitio**.
2. Vaya a **Administración del sitio** > **Notificaciones**. Moodle detecta la nueva versión del plugin.
3. Revise la lista de plugins a actualizar, donde `local_chuspasocial` debe aparecer con la versión nueva.
4. Haga clic en **Actualizar base de datos de Moodle ahora**.

**Opción B: desde la consola (`admin/cli/upgrade.php`)**
```text
sudo -u www-data php admin/cli/upgrade.php --non-interactive
```
El proceso termina sin errores cuando muestra `-->local_chuspasocial` seguido de `++ Success ++`.

### 5. Desactivar el modo mantenimiento y purgar cachés
```text
php admin/cli/maintenance.php --disable
php admin/cli/purge_caches.php
```

### 6. Verificación
- En **Administración del sitio** > **Plugins** > **Vista general de los plugins**, `local_chuspasocial` muestra la nueva versión.
- **Administración del sitio** > **Notificaciones** no muestra actualizaciones pendientes.

> Si Notificaciones no detecta ninguna actualización, el número `$plugin->version` del nuevo `version.php` no es mayor que el instalado. Restaure la carpeta desde el respaldo del paso 1.

