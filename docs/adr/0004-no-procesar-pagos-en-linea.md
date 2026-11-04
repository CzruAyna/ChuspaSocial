# ADR-004: No procesar pagos en línea

## Estado

Aceptado.

## Contexto

El Marketplace de ChuspaSocial tiene como objetivo permitir que los usuarios publiquen anuncios de libros y contacten con vendedores. El proyecto no necesita actuar como plataforma de pago ni gestionar transacciones financieras para cumplir ese objetivo.

Incorporar pagos en línea aumentaría de forma importante la complejidad técnica y las responsabilidades relacionadas con datos financieros, seguridad, proveedores de pago, devoluciones y disputas.

También introduciría riesgos legales que no forman parte del alcance del proyecto, como definir responsabilidades frente a cobros fallidos, reembolsos y reclamos, cumplir las condiciones del proveedor de pagos y atender obligaciones aplicables al tratamiento de información financiera y a la protección del consumidor. Estos requisitos pueden variar según el proveedor y la jurisdicción, por lo que evitarlos reduce el alcance legal que tendría que asumir ChuspaSocial.

## Decisión

El Marketplace de ChuspaSocial solo publicará anuncios y facilitará el contacto entre usuarios. ChuspaSocial no procesará, almacenará ni confirmará pagos en línea.

Cualquier acuerdo de compra y pago se realizará fuera de ChuspaSocial entre comprador y vendedor.

## Alternativas descartadas

- Integrar una pasarela de pagos dentro de ChuspaSocial. Se descarta porque requeriría integrar y mantener servicios externos, gestionar estados de transacción y aumentar la superficie de seguridad del sistema.
- Almacenar información de pago para completar compras desde el Marketplace. Se descarta por los riesgos técnicos y de protección de datos asociados al manejo de información financiera.

## Consecuencias

El Marketplace mantiene un alcance más simple: publicar anuncios y facilitar el contacto entre usuarios.

Se evitan responsabilidades técnicas relacionadas con cobros, reembolsos, conciliación de transacciones y almacenamiento de información financiera. Como limitación, ChuspaSocial no podrá verificar si una operación económica se completó correctamente, ya que el pago ocurre fuera de la plataforma.

## Consideraciones de seguridad y mitigación de disputas

Esta sección complementa la decisión anterior: ChuspaSocial sigue sin procesar, almacenar ni confirmar pagos, y lo que sigue no cambia ese alcance. Describe qué hace y qué no hace la plataforma, y qué recomendaciones se dan a los usuarios, que acuerdan y pagan fuera de ella.

### Alcance de la responsabilidad de ChuspaSocial

- ChuspaSocial publica anuncios y facilita el contacto entre usuarios; no es parte de la compraventa ni interviene en el pago.
- No custodia dinero ni garantiza que el libro corresponda a lo publicado, que el vendedor lo entregue o que el comprador pague.
- No verifica que una operación económica se haya completado ni media entre comprador y vendedor: los acuerdos y sus consecuencias son responsabilidad de quienes los pactan.
- Este documento describe el alcance técnico de la plataforma y no constituye asesoría legal. Cualquier definición sobre responsabilidad jurídica debe ser revisada por quien corresponda en la institución.

### Recomendaciones para transacciones seguras

Se recomienda a los usuarios:

- Concretar la entrega en persona, en un lugar público y concurrido de la universidad, y revisar el libro antes de pagar.
- Evitar pagos por adelantado a personas que no conocen y preferir pagar al recibir el libro.
- Preferir efectivo contra entrega o un medio de pago que deje comprobante, y conservarlo.
- No compartir datos financieros (números de tarjeta, claves o códigos) por la mensajería de Moodle ni en los anuncios.
- Mantener la conversación por la mensajería de Moodle, que deja registro de lo acordado: precio, estado del libro, lugar y hora de entrega.
- Desconfiar de precios muy por debajo del mercado y de quien presione para pagar con urgencia o por un medio distinto al acordado.

### Mitigación de disputas

- Ante un desacuerdo, las partes deben resolverlo directamente entre ellas, apoyándose en lo acordado por mensajería.
- Ante un anuncio, una publicación o un comentario sospechoso, se puede usar la función de reportes del plugin, que notifica a quienes pueden moderar.
- ChuspaSocial no reembolsa dinero ni obliga a cumplir lo acordado.
