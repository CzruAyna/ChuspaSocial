# Plan de Pruebas

## 1. Objetivo

Verificar que el plugin `local_chuspasocial` para Moodle 4.5 LTS cumple sus requisitos funcionales: muro social general y por materia, comentarios y reacciones, etiquetas, seguimiento de usuarios, notificaciones, reporte de contenido inapropiado y marketplace de libros, respetando los permisos y la visibilidad de cada funcionalidad. También se comprueba que el plugin funcione con PHP 8.1 a 8.3 y con MariaDB, MySQL y PostgreSQL, y que su interfaz esté disponible en español e inglés.

## 2. Alcance
### En alcance
- Muro social general y por materia: publicaciones con texto e imágenes; el muro por materia solo es visible para usuarios inscritos en el curso.
- Comentarios y reacciones: creación, visualización y gestión de comentarios y reacciones asociados a publicaciones, respetando los permisos.
- Muro por materia: visibilidad restringida a los inscritos en el curso.
- Tags / hashtags: etiquetar publicaciones, seguir temas y filtrar el muro.
- Seguir a otros usuarios y ver su perfil social.
- Noticias según tags (publicaciones de usuarios, anuncios oficiales y actividad de Moodle).
- Eventos: consulta y visualización de eventos del plugin, verificando su información y visibilidad según los permisos correspondientes.
- Notificaciones (comentarios, reacciones, nuevos seguidores) y mensajes directos.
- Reporte de contenido inapropiado.
- Marketplace de libros: anuncios con precio vinculados a una o varias materias, y contacto con el vendedor.
- Validación de formularios (por ejemplo, precio mayor o igual a 0 en los anuncios).
- Permisos y capacidades de acceso a cada funcionalidad.
- Funciones externas (AJAX) del plugin `local_chuspasocial`.
- Compatibilidad con Moodle 4.5 LTS, PHP 8.1 a 8.3 y MariaDB, MySQL y PostgreSQL.
- Interfaz en español e inglés (`lang/es`, `lang/en`).

### Fuera de alcance
- Pago y entrega de los libros: se acuerdan fuera de la plataforma.
- Pasarela de pagos y gestión de transacciones económicas.
- Funcionamiento interno del núcleo de Moodle y de sus plugins de terceros.
- Versiones de Moodle distintas de 4.5 LTS.
- Aplicaciones móviles nativas (solo la interfaz web con el tema Boost).
- Pruebas de usabilidad con usuarios reales y de accesibilidad avanzada.

## 3. Tipos de prueba
- [ ] Unitarias
- [ ] Integración
- [ ] Funcionales manuales
- [ ] Rendimiento
- [ ] Seguridad
- [ ] Regresión

## 4. Entornos

| Entorno | SO | Versión |
|---|---|---|
| Local | | |
| CI | Ubuntu latest | |

### Dependencias
- Moodle 4.5 LTS con `local_chuspasocial` instalado y habilitado.
- PHP 8.1 a 8.3 y MariaDB, MySQL o PostgreSQL.
- Usuarios de prueba con roles, capacidades y matrículas diferenciadas; cursos y datos de ejemplo para publicaciones, comentarios, eventos y anuncios.
- Herramientas de pruebas automatizadas disponibles en CI: PHPUnit, Behat y `moodle-plugin-ci`.

## 5. Responsables

| Rol | Responsable |
|---|---|
| Diseño de casos | |
| Ejecución manual | |
| Automatización | |
| Reporte | |

## 6. Criterios de salida
- [ ] Cobertura mínima de ___%
- [ ] Cero bugs críticos abiertos
- [ ] Todos los casos ejecutados

## 7. Riesgos

| Riesgo | Probabilidad | Impacto | Mitigación |
|---|---|---|---|
| Incompatibilidad con Moodle 4.5 LTS o con versiones de PHP (8.1 a 8.3) | Media | Alta | Ejecutar CI con `moodle-plugin-ci` en varias versiones de PHP y probar sobre `MOODLE_405_STABLE` |
| Fallos de esquema o consultas en distintos motores de base de datos (MariaDB, MySQL, PostgreSQL) | Media | Alta | Validar el esquema XMLDB y las consultas DML en los tres motores antes de cada entrega |
| Fuga de publicaciones entre materias (un muro de curso lo deben ver solo los inscritos) | Media | Alta | Casos de prueba de permisos y capacidades por curso, con usuarios inscritos y no inscritos |
| Exposición de datos personales en perfiles, seguidores o mensajería | Media | Alta | Revisar capacidades y privacidad; documentar y verificar lo definido en `security/` |
| Vulnerabilidades web (XSS en publicaciones y comentarios, CSRF, subida de imágenes) | Media | Alta | Limpiar y validar entradas, probar con datos maliciosos y ejecutar pruebas de seguridad |
| Contenido inapropiado o abuso en publicaciones y comentarios | Alta | Media | Probar el flujo de reportes de contenido y de moderación de principio a fin |
| Datos inconsistentes en el marketplace (precio inválido, contacto externo incorrecto) | Media | Media | Pruebas de validación del formulario de anuncios (precio mayor o igual a 0, campos requeridos) |
| Bajo rendimiento del muro con muchas publicaciones, tags y notificaciones | Media | Media | Usar paginación, revisar consultas y ejecutar pruebas de carga con datos de ejemplo |
| Cobertura de pruebas insuficiente (PHPUnit y Behat) por tiempo y tamaño del equipo | Alta | Media | Priorizar los casos críticos y revisar la cobertura en CI antes de fusionar a `dev` |
| Diferencias entre entornos locales y CI (Windows, Linux, Docker) | Media | Baja | Documentar la instalación, fijar versiones de dependencias y usar `moodle-docker` como alternativa |