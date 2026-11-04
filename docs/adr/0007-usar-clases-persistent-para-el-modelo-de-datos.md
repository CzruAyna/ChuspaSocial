# ADR-007: Usar clases persistent para el modelo de datos

## Estado

Aceptado.

## Contexto

ChuspaSocial necesita representar entidades de su modelo de datos, como publicaciones, comentarios, reacciones y anuncios, manteniendo reglas consistentes al leer y modificar información.

Moodle permite acceder directamente a la base de datos mediante `$DB`, pero también ofrece `\core\persistent` para representar registros mediante clases que centralizan la definición de propiedades, la validación y las operaciones habituales de persistencia.

## Decisión

Usar clases basadas en `\core\persistent` para representar las entidades principales del modelo de datos de ChuspaSocial.

Cada persistent definirá la tabla asociada y las propiedades de la entidad, de modo que la creación, lectura, actualización y eliminación de registros se realicen mediante una interfaz consistente. Las llamadas directas a `$DB` se reservarán para consultas específicas que no encajen adecuadamente en las operaciones de una persistent.

Las propiedades se declaran en el método estático protegido `define_properties()`, que devuelve un arreglo asociativo donde cada clave es el nombre de la propiedad y cada valor describe su definición. La definición incluye la clave obligatoria `type` con la constante `PARAM_*` que corresponde, el `default` que se asigna cuando la propiedad no se indica, la clave `null` para aceptar o rechazar valores nulos, `choices` cuando solo se admiten ciertos valores y `message` para personalizar el mensaje de error. Las propiedades `id`, `timecreated`, `timemodified` y `usermodified` son agregadas por la clase base y no hace falta declararlas.

El tipado estricto trae ventajas: cada valor se valida con la constante `PARAM_*` antes de llegar a la base de datos, de modo que un dato con formato incorrecto se rechaza en lugar de guardarse; los valores permitidos se restringen con `choices`; y las reglas propias de la entidad se implementan en métodos `validate_<propiedad>` que devuelven `true` o un `lang_string` con el error. Así, la validación queda centralizada en la clase y no repartida entre los servicios que la utilizan.

En cuanto a las transacciones, las operaciones `create()`, `update()` y `delete()` de una persistent ejecutan una única sentencia sobre la base de datos y no abren una transacción por sí mismas. Cuando una operación del modelo necesita varios cambios que deben aplicarse juntos, se usan las transacciones delegadas de Moodle: se abre la transacción con `$DB->start_delegated_transaction()`, se realizan las operaciones y se confirma con `$DB->commit_delegated_transaction($transaction)`; si alguna falla, `$DB->rollback_delegated_transaction($transaction, $e)` deshace el bloque completo. Las transacciones delegadas pueden anidarse, pero solo se confirman si todas las internas también lo hacen.

## Alternativas descartadas

- Usar llamadas directas a `$DB` para todas las operaciones del modelo. Se descarta porque repartiría la validación y las reglas de cada entidad entre distintos servicios, aumentando la duplicación y el acoplamiento con la estructura de las tablas.
- Crear una capa de acceso a datos propia sin utilizar `\core\persistent`. Se descarta porque duplicaría mecanismos que Moodle ya proporciona para definir propiedades, validar datos y manejar el ciclo de vida de los registros.

## Consecuencias

Las entidades principales tendrán una representación orientada a objetos y sus reglas básicas podrán mantenerse cerca de la definición de cada persistent. Esto facilita reutilizar validaciones, mantener un comportamiento consistente y probar el modelo de datos de forma aislada.

Como costo, será necesario crear y mantener una clase persistent por cada entidad que lo requiera y respetar las convenciones de `\core\persistent`. Las consultas complejas que involucren agregaciones o varias tablas podrán seguir utilizando `$DB` cuando resulte más apropiado.
