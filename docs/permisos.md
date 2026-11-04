# Matriz de permisos

Capacidades del plugin `local_chuspasocial` y qué rol puede ejercerlas.

| Capacidad | Usuario autenticado | Estudiante | Docente | Docente sin edición | Gestor |
|---|---|---|---|---|---|
| `local/chuspasocial:report` | Permitir | No definido | No definido | No definido | No definido |
| `local/chuspasocial:postannouncement` | No definido | No definido | Permitir | No definido | Permitir |
| `local/chuspasocial:view` | Permitir | No definido | No definido | No definido | No definido |
| `local/chuspasocial:comment` | Permitir | Permitir | Permitir | Permitir | No definido |
| `local/chuspasocial:createlisting` | No definido | No definido | No definido | No definido | No definido |
| `local/chuspasocial:follow` | Permitir | No definido | No definido | No definido | No definido |
| `local/chuspasocial:moderate` | No definido | No definido | No definido | No definido | No definido |
| `local/chuspasocial:post` | No definido | No definido | No definido | No definido | No definido |
| `local/chuspasocial:react` | No definido | No definido | No definido | No definido | No definido |
| `local/chuspasocial:viewmarketplace` | No definido | No definido | No definido | No definido | No definido |

## Contexto de cada capacidad

| Capacidad | Contexto |
|---|---|
| `local/chuspasocial:report` | Sistema |
| `local/chuspasocial:postannouncement` | Curso |
| `local/chuspasocial:view` | Sistema |
| `local/chuspasocial:comment` | Curso |
| `local/chuspasocial:createlisting` | No definido |
| `local/chuspasocial:follow` | Sistema |
| `local/chuspasocial:moderate` | No definido |
| `local/chuspasocial:post` | No definido |
| `local/chuspasocial:react` | No definido |
| `local/chuspasocial:viewmarketplace` | No definido |

> Solo `report`, `postannouncement`, `view`, `follow` y `comment` están definidas en `src/db/access.php`. Para las otras cinco capacidades no hay roles ni contexto definidos, por lo que se marcan como "No definido" sin inferir permisos.
