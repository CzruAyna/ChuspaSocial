# Casos de prueba funcionales — Notificaciones

## Objetivo

Documentar pruebas funcionales manuales para verificar las notificaciones de
ChuspaSocial registradas mediante el sistema de mensajería de Moodle.

Los proveedores relevantes para estos casos se declaran en
`src/db/messages.php`. Los casos se consideran pendientes hasta que se ejecuten
en una instalación de Moodle con los flujos correspondientes disponibles.

## Casos de prueba

### CP-NOTIF-01 — Notificación al recibir un nuevo seguidor

- **Identificador:** CP-NOTIF-01.
- **Título:** Notificación al recibir un nuevo seguidor.
- **Precondiciones:**
  - Existen dos usuarios activos: Usuario A y Usuario B.
  - Usuario A todavía no sigue a Usuario B.
  - Usuario B tiene habilitadas las notificaciones de ChuspaSocial para el
    proveedor `newfollower`.
- **Pasos de ejecución:**
  1. Iniciar sesión como Usuario A.
  2. Abrir el perfil social del Usuario B.
  3. Seleccionar la opción para seguir al Usuario B.
  4. Cerrar la sesión del Usuario A e iniciar sesión como Usuario B.
  5. Abrir la campana o panel de notificaciones de Moodle.
  6. Revisar las notificaciones recientes de ChuspaSocial.
- **Resultado esperado:** Usuario B recibe una notificación de nuevo seguidor y
  la acción de seguimiento queda registrada. Usuario A no recibe esa
  notificación como destinatario.
- **Criterio de aceptación:** Existe una notificación `newfollower` dirigida al
  Usuario B después de que Usuario A comienza a seguirlo.

### CP-NOTIF-02 — Notificación por comentario en una publicación propia

- **Identificador:** CP-NOTIF-02.
- **Título:** Notificación al recibir un comentario en una publicación propia.
- **Precondiciones:**
  - Existen dos usuarios activos: Usuario A y Usuario B.
  - Usuario A tiene una publicación visible.
  - Usuario B puede ver y comentar la publicación del Usuario A.
  - Usuario A tiene habilitadas las notificaciones de ChuspaSocial para el
    proveedor `newcomment`.
- **Pasos de ejecución:**
  1. Iniciar sesión como Usuario B.
  2. Abrir la publicación creada por Usuario A.
  3. Escribir un comentario válido y enviarlo.
  4. Cerrar la sesión del Usuario B e iniciar sesión como Usuario A.
  5. Abrir la campana o panel de notificaciones de Moodle.
  6. Revisar las notificaciones recientes de ChuspaSocial.
- **Resultado esperado:** El comentario queda asociado a la publicación y
  Usuario A recibe una notificación informando que su publicación tiene un
  nuevo comentario.
- **Criterio de aceptación:** Después de registrar el comentario, existe una
  notificación `newcomment` dirigida al autor de la publicación.

### CP-NOTIF-03 — Desactivar notificaciones de ChuspaSocial

- **Identificador:** CP-NOTIF-03.
- **Título:** Preferencias de usuario para desactivar notificaciones.
- **Precondiciones:**
  - El usuario destinatario puede modificar sus preferencias de notificaciones
    en Moodle.
  - Existe al menos un proveedor de ChuspaSocial habilitado, por ejemplo
    `newfollower` o `newcomment`.
  - Existe otro usuario capaz de provocar la acción asociada al proveedor.
- **Pasos de ejecución:**
  1. Iniciar sesión con el usuario que recibirá la notificación.
  2. Abrir las preferencias de notificaciones de Moodle.
  3. Localizar las notificaciones de ChuspaSocial.
  4. Desactivar el canal que se va a comprobar para el proveedor elegido.
  5. Cerrar la sesión.
  6. Iniciar sesión con el segundo usuario y realizar la acción que genera la
     notificación.
  7. Volver a iniciar sesión con el usuario destinatario.
  8. Revisar el canal de notificación que fue desactivado.
- **Resultado esperado:** La acción principal del plugin se completa, pero el
  usuario no recibe la notificación mediante el canal que desactivó.
- **Criterio de aceptación:** El canal deshabilitado respeta la preferencia del
  usuario sin impedir la operación que originaría la notificación.

## Registro de ejecución

| ID | Resultado obtenido | Estado | Observaciones |
|---|---|---|---|
| CP-NOTIF-01 | Pendiente de ejecución | Pendiente | |
| CP-NOTIF-02 | Pendiente de ejecución | Pendiente | |
| CP-NOTIF-03 | Pendiente de ejecución | Pendiente | |
