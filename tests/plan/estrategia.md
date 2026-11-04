# Estrategia de Pruebas

## Enfoque general

La estrategia sigue la pirámide de pruebas: la base son muchas pruebas unitarias, rápidas y baratas de ejecutar; en el medio hay menos pruebas de integración; y en la cima hay pocas pruebas end-to-end (e2e), más lentas y costosas. Cuanto más arriba en la pirámide, más cerca está la prueba de lo que ve el usuario y menos pruebas se escriben.

| Nivel | Cantidad | Velocidad | Herramienta |
|---|---|---|---|
| Unitarias | Muchas | Alta | PHPUnit |
| Integración | Intermedia | Media | PHPUnit |
| End-to-end (e2e) | Pocas | Baja | Behat |

## Niveles de prueba

### Pruebas unitarias
- Herramienta: PHPUnit.
- Cobertura mínima: 70 % del código ubicado en `classes/`, según el requisito RNF-012.
- Ubicación de las pruebas: `src/tests/`.
- Responsable: El equipo de desarrollo realiza las pruebas unitarias y el responsable de revisión de código verifica sus resultados
- Entorno: Local con Moodle 4.5 (`vendor/bin/phpunit --testsuite local_chuspasocial_testsuite`) y CI en GitHub Actions con `moodle-plugin-ci`.

### Pruebas de integración
- Herramienta: PHPUnit (con `advanced_testcase`, la base de datos de pruebas y los generadores de datos de Moodle).
- Alcance: Verifican que las clases del plugin funcionen junto con Moodle: base de datos, capacidades, eventos y servicios (por ejemplo, `post_service`).
- Responsable: El equipo de desarrollo, con la revisión del responsable de revisión de código.
- Entorno: Local con Moodle 4.5 y el plugin enlazado en `local/chuspasocial`, y CI en GitHub Actions con `moodle-plugin-ci`.

### Pruebas end-to-end (e2e)
- Herramienta: Behat.
- Alcance: Recorren flujos completos como lo haría un usuario en el navegador (por ejemplo, un estudiante que abre la página del muro). Se escriben en archivos `.feature` ubicados en `src/tests/behat/`.
- Responsable: El equipo de desarrollo.
- Entorno: Local con Moodle 4.5 y Behat inicializado.

### Pruebas manuales
- Frecuencia: Antes de cada release del sistema.
- Responsable: Equipo encargado de las pruebas manuales.
- Casos de prueba: Se utilizarán los casos funcionales disponibles en `tests/casos/funcionales/` para verificar el correcto funcionamiento del sistema.
- Evidencias: Los resultados y las evidencias de las pruebas realizadas se almacenarán en `tests/manuales/evidencias/`.
  

## Criterios de priorización de bugs

| Severidad | Descripción | Tiempo de resolución |
|---|---|---|
| Crítico | El sistema no funciona | Inmediato |
| Alto | Funcionalidad principal afectada | 24 horas |
| Medio | Funcionalidad secundaria afectada | 1 semana |
| Bajo | Problema cosmético | Siguiente sprint |