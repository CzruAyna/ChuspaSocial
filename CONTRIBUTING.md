# Cómo contribuir a este proyecto

Gracias por tu interés en contribuir. Este proyecto usa
el **Forking Workflow**. Lee este documento antes de empezar.

---

## Flujo de trabajo

### 1. Haz fork del repositorio
Botón **Fork** en la esquina superior derecha de GitHub.

### 2. Clona tu fork
```bash
git clone https://github.com/TU-USUARIO/ChuspaSocial.git
cd ChuspaSocial
```

### 3. Agrega el repo original como upstream
```bash
git remote add upstream https://github.com/sis-inf/ChuspaSocial.git
```

### 4. Sincroniza antes de trabajar
`git fetch upstream` descarga la información más reciente del repositorio original y actualiza las referencias remotas, sin modificar directamente la rama de trabajo.

```bash
git fetch upstream
```

`git rebase upstream/dev` actualiza la rama de trabajo colocando sus cambios sobre la versión más reciente de la rama `dev` del repositorio original.

```bash
git rebase upstream/dev
```

`git push --force-with-lease` actualiza la rama remota después de realizar un `rebase`, evitando sobrescribir cambios remotos que no se hayan recibido previamente.

```bash
git push --force-with-lease
```


### 5. Crea tu rama de trabajo
```bash
git checkout -b tipo/descripcion-corta
```

Ejemplos de nombres de rama:

feat/post-service-create
docs/rf-012-comentarios
fix/orden-comentarios
test/post-service-create
chore/configurar-phpcs-moodle
security/validar-capabilities-publicaciones

### 6. Trabaja y haz commits pequeños
```bash
git add .
git commit -m "tipo: descripción corta en presente"
```

### 7. Sube tu rama a tu fork
```bash
git push origin tipo/descripcion-corta
```

### 8. Abre un Pull Request
- Base: `sis-inf/ChuspaSocial` → rama `dev`
- Compare: `TU-USUARIO/ChuspaSocial` → tu rama

---

## Convención de commits

| Tipo | Cuándo usarlo |
|---|---|
| `feat:` | Nueva funcionalidad |
| `fix:` | Corrección de error |
| `docs:` | Documentación |
| `test:` | Pruebas |
| `chore:` | Configuración o CI/CD |
| `refactor:` | Mejora sin cambiar comportamiento |
| `security:` | Mejora de seguridad |
| `data:` | Análisis de datos |

### Ejemplos

feat: agregar creación de publicaciones en el muro
fix: corregir orden cronológico de comentarios
docs: documentar RF-012 para ver comentarios de una publicación
test: agregar pruebas para la creación de publicaciones
chore: configurar phpcs con el estándar de Moodle

---

## Reglas importantes

- ❌ Nunca hagas push directo a `main` o `dev`
- ❌ Nunca trabajes directamente en `main` o `dev`
- ✅ Un issue = una rama = un PR
- ✅ Todo PR debe referenciar su issue con `Closes #N`
- ✅ El PR debe pasar el CI antes de ser mergeado
- ✅ Todo PR necesita al menos una revisión

---

## Verificaciones antes de abrir un PR

Antes de abrir un Pull Request, ejecuta localmente las siguientes verificaciones:

### PHP CodeSniffer

Para comprobar que el código cumple con los estándares de Moodle:

```bash
vendor/bin/phpcs
```

### PHPUnit

Para ejecutar las pruebas automatizadas:

```bash
vendor/bin/phpunit
```

Si alguna verificación presenta errores, corrígelos antes de abrir el Pull Request.

---

## Ramas del proyecto

| Rama | Propósito |
|---|---|
| `main` | Versión estable — solo recibe merges desde `dev` |
| `dev` | Rama de desarrollo principal |
| `feat/*` | Nuevas funcionalidades |
| `fix/*` | Correcciones |
| `docs/*` | Documentación |
| `test/*` | Pruebas |
| `chore/*` | Configuración |

## Convenciones de nombres de Moodle

Para mantener el código consistente con Moodle, usa estas convenciones:

- **Frankenstyle:** el nombre del componente es `local_chuspasocial`. Úsalo como prefijo cuando Moodle requiera identificar el plugin.
- **Tablas de base de datos:** sus nombres deben tener como máximo 28 caracteres y mantenerse relacionados con el componente.
- **Namespaces:** las clases del plugin deben usar el namespace `local_chuspasocial\...` y ubicarse dentro de `classes/` según su responsabilidad.
- **Claves de strings:** usa identificadores descriptivos en minúsculas y sin espacios, definidos en los archivos de idioma del componente.

## Cómo agregar strings de idioma

Los textos que ve el usuario no se escriben a mano en el código: se definen como *strings* en los archivos de idioma del plugin y se leen con `get_string()`. Hay un archivo por idioma, y los dos deben tener exactamente las mismas claves:

- `src/lang/en/local_chuspasocial.php` (inglés)
- `src/lang/es/local_chuspasocial.php` (español)

### Reglas

- **Un grupo de strings, un issue:** los strings se agregan en issues propios, uno por grupo. No los agregues por tu cuenta dentro del issue de otra funcionalidad.
- **Orden alfabético:** en cada archivo, agrega la línea en el lugar que le corresponde según su clave, en orden alfabético.
- **Las mismas claves en los dos idiomas:** si una clave existe en `en`, debe existir también en `es`, y al revés.
- **Clave y texto:** la clave va en minúsculas y sin espacios (ver «Convenciones de nombres de Moodle») y el texto entre comillas simples.

Ejemplo:

```php
$string['addcomment'] = 'Add comment';
```

### ¿Qué pasa si el string todavía no existe?

Mientras un string no esté definido, Moodle muestra su clave entre corchetes dobles en lugar del texto, por ejemplo `[[addcomment]]`. Es normal: significa que ese string aún no se agregó, no que tu código esté mal. No lo "arregles" escribiendo el texto fijo en el código.

### Comprobar tus cambios

Verifica que los archivos de idioma no tengan errores de sintaxis:

```bash
php -l src/lang/en/local_chuspasocial.php
php -l src/lang/es/local_chuspasocial.php
```

Después, desde la carpeta de tu Moodle local, borra la caché para que Moodle lea los strings nuevos:

```bash
php admin/cli/purge_caches.php
```

---

## Quién sube `version.php`

Los PR no modifican `version.php`. La versión del plugin se sube en issues aparte, así se evitan conflictos entre los PR.

### Probar cambios de `db/` en tu copia local

Moodle solo aplica los cambios de `db/` si detecta una versión nueva del plugin. Para probarlos:

1. Sube `$plugin->version` en `version.php`, solo en tu copia local.
2. Entra a tu Moodle local como administrador. Moodle detecta la versión nueva y muestra la pantalla de actualización: confírmala desde ahí (también puedes ir a Administración del sitio > Notificaciones).
3. Comprueba que el cambio se aplicó.
4. Antes de hacer commit, descarta el cambio de versión:
```bash
   git restore version.php
```

No uses `git add .`: agrega solo los archivos de tu issue y confirma con `git status` que `version.php` no aparece como modificado.

---

## Resolver conflictos de merge

Un conflicto aparece cuando tu rama y `dev` cambiaron las mismas líneas de un archivo. Pasa seguido en archivos que muchos editan a la vez, como `docs/glosario.md`. Ejemplo: tú agregaste el término «Hilo» y otro PR ya fusionado agregó «Grupo» en el mismo lugar de la tabla.

### 1. Trae los cambios de `dev` a tu rama
```bash
git checkout docs/mi-rama
git fetch upstream
git merge upstream/dev
```

Git avisa qué archivo tiene conflicto:

```text
CONFLICTO (contenido): Conflicto de fusión en docs/glosario.md
```

### 2. Busca las marcas de conflicto en el archivo
```text
<<<<<<< HEAD
| Hilo | Conversación formada por una publicación y sus comentarios. |
=======
| Grupo | Conjunto de usuarios de un curso que comparten publicaciones. |
>>>>>>> upstream/dev
```

- Entre `<<<<<<< HEAD` y `=======` está tu versión.
- Entre `=======` y `>>>>>>> upstream/dev` está lo que ya tiene `dev`.

### 3. Deja el resultado correcto y borra las marcas
En una tabla como la del glosario casi siempre hay que conservar las dos filas, en orden alfabético:

```text
| Grupo | Conjunto de usuarios de un curso que comparten publicaciones. |
| Hilo | Conversación formada por una publicación y sus comentarios. |
```

Comprueba que no quedó ninguna marca (no debe mostrar nada):

```bash
git grep -n '<<<<<<<' -- docs/glosario.md
```

### 4. Termina el merge y sube tu rama
```bash
git add docs/glosario.md
git commit --no-edit
git push origin docs/mi-rama
```

El PR se actualiza solo con el nuevo commit. Nunca uses `git push --force` para resolver un conflicto.

---

## ¿No sabes por dónde empezar?

1. Revisa los issues abiertos con la etiqueta `good first issue`
2. Comenta en el issue que quieres trabajarlo
3. Espera confirmación antes de empezar
4. Sigue los pasos de este documento

## Cómo tomar un issue

Antes de empezar a trabajar en un issue, sigue estos pasos:

1. **Comenta en el issue:** indica que deseas trabajar en él y, si es posible, explica brevemente cómo planeas abordarlo.
2. **Espera la asignación:** no empieces a implementar cambios hasta que un colaborador o mantenedor te asigne el issue.
3. **Respeta el límite de trabajo:** cada persona puede tener como máximo 2 issues en progreso al mismo tiempo. Antes de asumir otro, termina o libera uno de los que tienes asignados.
4. **Crea una rama desde `dev`:** utiliza una rama específica para el issue, siguiendo las convenciones de nombres de este documento.
5. **Mantén el alcance de la contribución:** un issue = un archivo = un PR. Cada contribución debe centrarse en el archivo indicado y en los objetivos definidos en el issue.
6. **Abre el Pull Request contra `dev`:** cuando termines, sube tu rama a tu fork y abre el PR dirigido a `dev`, nunca a `main`. Incluye una referencia al issue correspondiente, por ejemplo, `Closes #413`.

## Cómo revisar un PR

Al realizar la revisión de un Pull Request, por favor verifica que cumpla con el siguiente checklist:

- [ ] **Cierra su issue:** El PR debe vincular y cerrar su issue correspondiente (ej. usando `Closes #N`).
- [ ] **Toca un solo archivo:** Los cambios deben mantenerse enfocados y limitarse a los archivos estrictamente necesarios.
- [ ] **Cumple los criterios:** La solución debe satisfacer todos los criterios de aceptación detallados en el issue.
- [ ] **Pasa el CI:** Las pruebas de Integración Continua (Continuous Integration) deben ejecutarse y finalizar sin errores.
- [ ] **Comentarios respetuosos:** Toda retroalimentación u observación debe comunicarse de manera constructiva y respetuosa.
