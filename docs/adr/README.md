# ARCHITECTURE DECISION RECORDS (ADR)

Los Architecture Decision Records (ADR) son documentos que registran decisiones importantes de arquitectura tomadas durante el desarrollo del proyecto.

Su objetivo es conservar el contexto de las decisiones para que el equipo pueda entender qué se decidió, por qué se decidió y cuáles fueron sus consecuencias.

## Índice de ADR

Resumen de las decisiones arquitectónicas registradas en esta carpeta.

| ADR | Título | Estado |
| --- | --- | --- |
| ADR-001 | [Usar un plugin de tipo local](0001-usar-un-plugin-de-tipo-local.md) | Aceptado |
| ADR-002 | [Cómo mostrar el muro al ingresar a Moodle](0002-como-mostrar-el-muro-al-ingresar-a-moodle.md) | Aceptado |
| ADR-004 | [No procesar pagos en línea](0004-no-procesar-pagos-en-linea.md) | Aceptado |
| ADR-005 | [Usar la API de tags de Moodle](0005-usar-la-api-de-tags-de-moodle.md) | Aceptado |
| ADR-006 | [Usar la mensajería de Moodle](0006-usar-la-mensajeria-de-moodle.md) | Aceptado |
| ADR-007 | [Usar clases persistent para el modelo de datos](0007-usar-clases-persistent-para-el-modelo-de-datos.md) | Aceptado |
| ADR-010 | [Un issue, un archivo](0010-un-issue-un-archivo.md) | Aceptado |

## Estructura de un ADR

Cada ADR debe contener las siguientes cuatro secciones:

### 1. Estado

Indica la situación actual de la decisión.

Ejemplos:

- Propuesto
- Aceptado
- Rechazado
- Reemplazado
- Obsoleto

### 2. Contexto

Describe el problema, necesidad o situación que llevó al equipo a tomar una decisión.

Aquí se debe explicar la información relevante que se tuvo en cuenta antes de decidir.

### 3. Decisión

Describe la solución o alternativa que el equipo decidió adoptar.

Debe indicar claramente qué se decidió implementar y, cuando sea necesario, las razones principales de la decisión.

### 4. Consecuencias

Describe los efectos de la decisión.

Puede incluir beneficios, limitaciones, riesgos, costos técnicos o cambios que deberán considerarse en el futuro.

## Ejemplo

```text
# ADR-001: Uso de PostgreSQL

## Estado

Aceptado

## Contexto

El proyecto necesita una base de datos relacional para almacenar y consultar la información de los usuarios.

## Decisión

Se utilizará PostgreSQL como sistema gestor de base de datos.

## Consecuencias

El equipo deberá configurar PostgreSQL en los entornos de desarrollo y producción.
