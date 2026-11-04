# ADR-010: Un issue, un archivo

## Estado

Aceptado.

## Contexto

ChuspaSocial se desarrolla mediante trabajo paralelo de aproximadamente 100 personas. Cuando varios issues modifican los mismos archivos o dependen entre sí, aumenta la posibilidad de conflictos entre ramas y se dificulta la revisión de los cambios.

Para facilitar el trabajo paralelo, se busca que cada issue sea pequeño, independiente y pueda revisarse sin depender de otros cambios.

Sin embargo, muchas funcionalidades requieren cambios en varios archivos (strings de idioma, clases, plantillas, JavaScript). Por eso, además de la regla de alcance, el equipo necesita un procedimiento común para dividir esas tareas en entregas que puedan desarrollarse, revisarse y fusionarse por separado.

## Decisión

Cada issue debe modificar un solo archivo y no depender de otros issues.

Esta decisión permite que varias personas trabajen en paralelo con menor riesgo de conflictos. Además, mantiene los Pull Requests pequeños y facilita una revisión rápida de cada cambio.

### Regla de un solo archivo

- Cada issue indica en «Archivos a tocar» el único archivo que se puede modificar.
- Se aplica la correspondencia un issue = una rama = un Pull Request.
- La rama se crea desde `dev` y el Pull Request se abre contra `dev`.
- Un Pull Request no debe incluir cambios en archivos ajenos al issue, aunque sean pequeños (por ejemplo, corregir un error de formato en otro documento). Esos cambios se proponen en un issue nuevo.
- Los Pull Requests no modifican `version.php`; la versión del plugin se actualiza en issues propios.
- La única excepción permitida es un cambio en `src/amd/src/*.js` acompañado de su archivo compilado en `src/amd/build/*.min.js`, porque ambos representan el mismo módulo.
- El flujo de CI verifica automáticamente la cantidad de archivos modificados y rechaza los Pull Requests que no cumplen la regla.

### Pautas para dividir un cambio en entregas independientes

1. Identificar todos los archivos que necesita el cambio completo y crear un issue por archivo.
2. Redactar cada issue para que tenga sentido por sí solo: contexto propio, archivo a tocar y criterios de aceptación que se puedan verificar sin revisar otros issues.
3. Ordenar las entregas de las bases a los usos: primero lo que otros archivos van a utilizar (strings de idioma, clases, tablas) y después lo que lo consume (páginas, plantillas, JavaScript).
4. Cada entrega debe dejar la rama `dev` funcionando una vez fusionada, aunque la funcionalidad completa todavía no esté terminada.
5. Si un issue necesita algo que aún no existe en `dev`, no se trabaja sobre la rama de otra persona: se espera a que ese cambio se fusione y recién entonces se publica o se asigna el issue siguiente.
6. Cada rama se crea siempre desde `dev` actualizado, nunca desde otra rama de trabajo, para que los Pull Requests no arrastren cambios ajenos.
7. Si durante el trabajo se descubre que hace falta modificar otro archivo, no se agrega al Pull Request actual: se comenta en el issue y se propone un issue nuevo.

Ejemplo: agregar un botón «Me gusta» se divide en un issue para el string en `lang/en`, otro para el string en `lang/es`, otro para la clase que registra la reacción, otro para la plantilla que muestra el botón y otro para el JavaScript (`src` y `build` juntos).

## Alternativas descartadas

- Permitir que un mismo issue modifique varios archivos. Se descarta porque aumenta la posibilidad de conflictos y hace que los Pull Requests sean más grandes y difíciles de revisar.
- Hacer que un issue dependa de otros issues. Se descarta porque dificulta el trabajo paralelo y puede bloquear el avance de otros colaboradores.

## Consecuencias

Los issues serán más pequeños e independientes, permitiendo que aproximadamente 100 personas puedan trabajar en paralelo con menos conflictos.

Los Pull Requests serán más fáciles y rápidos de revisar. Cuando un cambio requiera modificar varios archivos, deberá dividirse en issues independientes siguiendo las pautas de este ADR.

Como cada rama parte de `dev` y toca un solo archivo, las ramas se mantienen independientes entre sí: un Pull Request puede fusionarse, corregirse o descartarse sin afectar a los demás.

A cambio, una funcionalidad completa requiere más issues y Pull Requests, y su avance depende del orden en que se fusionen. Quienes crean los issues deben planificar ese orden para evitar bloqueos.
