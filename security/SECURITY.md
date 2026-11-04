# Política de Seguridad

## Reportar una vulnerabilidad

Si encuentras una vulnerabilidad de seguridad:

1. **No** crees un issue público ni publiques los detalles en foros, mensajería o redes sociales.
2. Repórtala mediante GitHub Private Vulnerability Reporting en `Security` → `Advisories` → `Report a vulnerability`. El reporte solo es visible para los mantenedores del repositorio.
3. Incluye la versión del plugin y de Moodle, una descripción detallada del problema, los pasos para reproducirlo y, si es posible, una propuesta de corrección.
4. Espera confirmación antes de divulgar. La divulgación se coordina con el equipo mantenedor una vez publicada la corrección.

Si no puedes usar GitHub Private Vulnerability Reporting, solicita al equipo docente un canal alternativo por los medios institucionales del curso. No envíes los detalles por canales públicos.

## Tiempos de respuesta

El equipo mantenedor se compromete a los siguientes plazos para cada reporte recibido por el canal confidencial:

| Etapa | Plazo |
|---|---|
| Confirmación de recepción | 2 días hábiles |
| Evaluación inicial y clasificación de severidad | 5 días hábiles |
| Corrección de vulnerabilidades críticas o altas | 30 días calendario |
| Corrección de vulnerabilidades medias o bajas | 90 días calendario |

Si no se puede cumplir un plazo de corrección, se informará al reportante y se acordará una fecha de divulgación.

## Qué se considera una vulnerabilidad

En ChuspaSocial se considera una vulnerabilidad cualquier fallo que permita comprometer la seguridad, privacidad o acceso autorizado a la información de los usuarios.

Algunos ejemplos son:

- Acceder o visualizar publicaciones de materias en las que el usuario no está inscrito.
- Ejecutar código malicioso mediante ataques XSS (Cross-Site Scripting).
- Realizar acciones no autorizadas mediante ataques CSRF (Cross-Site Request Forgery).
- Acceder, descargar o visualizar archivos privados sin los permisos correspondientes.

## Análisis de seguridad

Este proyecto ejecuta análisis automático de seguridad
en cada PR mediante GitHub Actions.

## Versiones soportadas

ChuspaSocial está en fase `0.x` (pre-1.0), por lo que solo se atienden reportes de seguridad sobre la última versión del plugin, ejecutándose en Moodle 4.5 LTS.

| Componente | Versión | Soportada |
|---|---|---|
| Plugin `local_chuspasocial` | 0.x (última versión) | ✅ |
| Plugin `local_chuspasocial` | Versiones 0.x anteriores a la última | ❌ |
| Moodle | 4.5 LTS (rama `MOODLE_405_STABLE`, incluidas las actualizaciones 4.5.x) | ✅ |
| Moodle | 4.4 y anteriores | ❌ |
| Moodle | 5.0 y posteriores | ❌ |

La base soportada es Moodle 4.5 LTS con las actualizaciones menores de seguridad aplicadas. Las revisiones y correcciones de seguridad se hacen sobre esa rama; no se garantiza compatibilidad con otras versiones de Moodle.
