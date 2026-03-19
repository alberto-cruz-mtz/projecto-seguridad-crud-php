# Documento de Implementación: Manejador de Pagos de Estudiantes

Este documento describe la especificación técnica y las historias de usuario necesarias para la implementación del módulo de **Gestión de Pagos de Estudiantes** en el sistema.

## Contexto del Dominio

La tabla `payments` de la base de datos almacena los pagos realizados por los alumnos. Cada pago registra:

| Campo              | Tipo              | Descripción                                              |
|--------------------|-------------------|----------------------------------------------------------|
| `id`               | `SERIAL`          | Identificador autogenerado del pago.                     |
| `student_id`       | `VARCHAR(10)`     | Referencia al identificador único del estudiante.        |
| `week_number`      | `SMALLINT`        | Semana del período lectivo a la que corresponde el pago. |
| `payment_date`     | `TIMESTAMPTZ`     | Fecha y hora del registro del pago (por defecto: ahora). |
| `amount`           | `DECIMAL(10,2)`   | Monto pagado (debe ser mayor a `0.00`).                  |
| `receiver_user_id` | `UUID`            | Usuario del sistema (tesorero/admin) que recibe el pago. |

## Reglas de Codificación y Estándares del Proyecto

Todo el código desarrollado para este módulo deberá seguir las mismas directrices definidas en `guia_directrices_proyecto.md`:

1. **Arquitectura por Capas**: `Entity` → `Repository` → `Service` → `Controller`.
2. **Tipado Estricto**: Declarar tipos en propiedades, parámetros y retornos.
3. **Namespaces y PSR-4**: Bajo el namespace `Tito\App\` (ej. `Tito\App\Entity\Payment`).
4. **Convenciones de Nomenclatura (PSR-12)**: `PascalCase` para clases, `camelCase` para métodos/propiedades.
5. **Seguridad**: Todas las consultas a la base de datos deben usar **Prepared Statements** en el `Repository`.

---

## Historias de Usuario (HU)

### HU-P01: Registro de Pago de un Estudiante

**Como** tesorero o administrador del sistema  
**Quiero** registrar el pago semanal de un estudiante indicando el monto, la semana y el estudiante  
**Para que** quede un historial auditado de los pagos recibidos.

#### Criterios de Aceptación, Validaciones y Restricciones

- El sistema debe recibir los siguientes datos obligatorios: `student_id`, `week_number` y `amount`.
- El campo `receiver_user_id` debe asignarse automáticamente con el UUID del usuario autenticado que realiza el registro; no debe ser editable por el cliente.
- El campo `payment_date` se asigna automáticamente con la fecha y hora del servidor (valor por defecto `CURRENT_TIMESTAMP`).
- **Validaciones**:
  - `student_id` debe existir en la tabla `students`; retornar error `404` si no se encuentra.
  - `week_number` debe ser un entero entre **1 y 53** (inclusive).
  - `amount` debe ser un número decimal **mayor a `0.00`** y con un máximo de 10 dígitos en total y 2 decimales.
- **Restricciones**:
  - Solo usuarios con rol `treasurer` o `admin` pueden acceder a este endpoint (proteger con Middleware de autorización).
  - No se deben admitir dos pagos del mismo `student_id` para la misma `week_number` (restricción de unicidad de negocio); retornar error `409 Conflict` si el par ya existe.

> **Comentario de Implementación**:
> - Crear la entidad `Tito\App\Entity\Payment` con propiedades tipadas: `int $id`, `string $studentId`, `int $weekNumber`, `\DateTimeImmutable $paymentDate`, `string $amount`, `UUID $receiverUserId`.
>   - El campo `amount` debe representarse como `string` (no `float`) para preservar la precisión decimal. Utilizar la extensión **BCMath** de PHP para todas las operaciones aritméticas sobre montos (ej. `bccomp($amount, '0.00', 2)`), evitando así los errores de precisión propios de los números de punto flotante.
> - En `PaymentRepository`, implementar `save(Payment $payment): Payment` usando un *Prepared Statement* `INSERT`.
> - En `PaymentService`, crear el método `registerPayment(string $studentId, int $weekNumber, float $amount, UUID $receiverUserId): Payment` que valide la existencia del estudiante, aplique las reglas de negocio y delegue la persistencia al repositorio.

---

### HU-P02: Listado de Pagos

**Como** tesorero o administrador del sistema  
**Quiero** consultar el listado de pagos registrados, pudiendo filtrar por un estudiante específico o ver todos los pagos del sistema  
**Para que** pueda monitorear y auditar los pagos realizados.

#### Criterios de Aceptación, Validaciones y Restricciones

- El endpoint debe soportar dos modos de consulta:
  - **Todos los pagos**: sin filtros adicionales, retorna todos los registros del sistema.
  - **Pagos de un estudiante**: filtrado por `student_id`, retorna únicamente los pagos del alumno indicado.
- Cada registro del listado debe incluir: `id`, `student_id`, `week_number`, `payment_date`, `amount` y `receiver_user_id`.
- **Validaciones**:
  - Si se proporciona un `student_id` como filtro, verificar que dicho estudiante exista antes de ejecutar la consulta; retornar `404` si no se encuentra.
  - Los parámetros de filtro deben sanearse para prevenir inyección SQL (usar siempre parámetros vinculados en el `Repository`).
- **Restricciones**:
  - Solo usuarios con rol `treasurer` o `admin` pueden acceder a este endpoint.
  - Implementar **paginación** mediante `LIMIT` y `OFFSET` para evitar cuellos de botella al consultar grandes volúmenes de pagos. Los parámetros `page` y `limit` deben proveerse en la petición (con valores por defecto: `page=1`, `limit=20`).
  - El `limit` máximo permitido por petición es **100 registros**.

> **Comentario de Implementación**:
> - En `PaymentRepository`, implementar dos métodos:
>   - `findAll(int $limit, int $offset): array` — retorna todos los pagos con paginación.
>   - `findByStudentId(string $studentId, int $limit, int $offset): array` — retorna los pagos filtrados por alumno con paginación.
> - En `PaymentService`, crear `listPayments(?string $studentId, int $page, int $limit): array` que calcule el `offset` y delegue al método adecuado del repositorio.

---

### HU-P03: Actualización de los Datos de un Pago Registrado

**Como** administrador del sistema  
**Quiero** corregir el monto o la semana de un pago ya registrado  
**Para que** los registros reflejen fielmente el cobro correcto en caso de errores de captura.

#### Criterios de Aceptación, Validaciones y Restricciones

- Los únicos campos modificables son: `week_number` y `amount`. No se permite actualizar `student_id`, `payment_date` ni `receiver_user_id`.
- La petición debe incluir el `id` del pago a modificar y al menos uno de los campos actualizables.
- **Validaciones**:
  - El pago identificado por `id` debe existir en la base de datos; retornar `404` si no se encuentra.
  - Si se envía `week_number`, debe ser un entero entre **1 y 53** (inclusive).
  - Si se envía `amount`, debe ser un decimal **mayor a `0.00`** con el formato correcto. Utilizar **BCMath** para la comparación (`bccomp($amount, '0.00', 2) > 0`).
  - No se deben aceptar peticiones con todos los campos actualizables ausentes o vacíos (`400 Bad Request`).
- **Restricciones**:
  - Solo usuarios con rol `admin` pueden actualizar pagos ya registrados (mayor nivel de privilegio que el registro inicial).
  - Se recomienda registrar en un log interno quién realizó la modificación, cuándo y cuáles fueron los valores anteriores y nuevos (auditoría de cambios).

> **Comentario de Implementación**:
> - En `PaymentRepository`, implementar `update(int $paymentId, array $fieldsToUpdate): Payment` con una construcción dinámica del `SET` en el *Prepared Statement*. **Los nombres de los campos en `$fieldsToUpdate` deben validarse contra una lista de campos permitidos** (allowlist: `['week_number', 'amount']`) antes de incluirlos en la consulta, para prevenir inyección SQL en los nombres de columna.
> - En `PaymentService`, crear el método `updatePayment(int $paymentId, ?int $weekNumber, ?string $amount): Payment` que verifique que el pago exista, aplique las validaciones y delegue al repositorio.

---

### HU-P04: Consulta del Detalle de un Pago

**Como** tesorero o administrador del sistema  
**Quiero** consultar toda la información de un pago específico usando su identificador  
**Para que** pueda verificar los datos de un pago puntual sin necesidad de recorrer el listado completo.

#### Criterios de Aceptación, Validaciones y Restricciones

- El endpoint debe recibir el `id` del pago como parámetro de ruta (ej. `GET /payments/{id}`).
- La respuesta debe incluir todos los campos: `id`, `student_id`, `week_number`, `payment_date`, `amount` y `receiver_user_id`.
- **Validaciones**:
  - El `id` del pago debe ser un entero positivo; retornar `400 Bad Request` si el formato es inválido.
  - Si el pago no existe, retornar `404 Not Found` con un mensaje descriptivo.
- **Restricciones**:
  - Solo usuarios con rol `treasurer` o `admin` pueden acceder a este endpoint.

> **Comentario de Implementación**:
> - En `PaymentRepository`, implementar `findById(int $id): ?Payment`.
> - En `PaymentService`, crear `getPaymentById(int $id): Payment` que lance una excepción de dominio (ej. `NotFoundException`) si el resultado es `null`.

---

### HU-P05: Anulación (Eliminación) de un Pago

**Como** administrador del sistema  
**Quiero** eliminar un pago registrado por error  
**Para que** los reportes financieros reflejen únicamente los pagos válidos.

#### Criterios de Aceptación, Validaciones y Restricciones

- El endpoint debe recibir el `id` del pago a eliminar como parámetro de ruta (ej. `DELETE /payments/{id}`).
- **Validaciones**:
  - Verificar que el pago existe antes de intentar eliminarlo; retornar `404` si no se encuentra.
  - El `id` debe ser un entero positivo; retornar `400 Bad Request` si el formato es inválido.
- **Restricciones**:
  - Solo usuarios con rol `admin` pueden ejecutar eliminaciones.
  - Considerar la implementación de **borrado lógico** (*soft delete*) agregando un campo `deleted_at TIMESTAMPTZ` a la tabla `payments`, en lugar del borrado físico, para conservar el historial de auditoría. Si se opta por borrado físico, documentar el impacto en los reportes.
  - Se debe requerir confirmación explícita en el cliente (parámetro `confirm=true` en el cuerpo de la petición) antes de proceder con la eliminación, para evitar borrados accidentales.

> **Comentario de Implementación**:
> - En `PaymentRepository`, implementar `delete(int $paymentId): void` (borrado físico) o `softDelete(int $paymentId): void` (borrado lógico con `UPDATE payments SET deleted_at = NOW()`).
> - En `PaymentService`, crear `deletePayment(int $paymentId): void` que valide la existencia del registro antes de invocar el repositorio.
