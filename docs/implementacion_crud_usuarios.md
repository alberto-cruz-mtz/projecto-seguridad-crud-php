# Documento de Implementación: CRUD de Usuarios

Este documento describe la especificación técnica y las historias de usuario necesarias para la implementación del módulo de Gestión de Usuarios en el sistema. 

## Reglas de Codificación y Estándares del Proyecto

Basado en el estilo actual del proyecto, todo el código desarrollado para este CRUD deberá seguir estrictamente las siguientes directrices:

1. **Arquitectura por Capas**: El código debe separar las responsabilidades en `Entity` (modelos de dominio), `Repository` (acceso a base de datos), `Service` (lógica de negocio) y `Controller` (manejo de peticiones HTTP).
2. **Tipado Estricto**: Se deben usar declaraciones de tipos tanto para los parámetros de entrada como para los valores de retorno (ej. `public function getEmail(): string`). También deben tiparse las propiedades de las clases.
3. **Namespaces y PSR-4**: Todo archivo debe tener su namespace correspondiente (ej. `namespace Tito\App\Entity;`) y las dependencias importarse mediante la sentencia `use`.
4. **Convenciones de Nomenclatura (PSR-12)**:
   - **Clases e Interfaces**: `PascalCase` (ej. `UserRepository`).
   - **Métodos y Propiedades**: `camelCase` (ej. `getFullName()`, `$passwordHash`).
   - **Variables y Parámetros**: `camelCase`.
5. **Value Objects**: Se promueve el uso de Value Objects para tipos especiales, como `Tito\App\Core\UUID` para identificadores o enums para roles.
6. **Seguridad**:
   - Las contraseñas **NUNCA** deben guardarse en texto plano; se debe utilizar un algoritmo robusto como `PASSWORD_BCRYPT` o `PASSWORD_ARGON2I`.
   - Todas las consultas a la base de datos deben utilizar consultas preparadas (Prepared Statements) en la capa de `Repository` para evitar inyecciones SQL.

---

## Historias de Usuario (HU)

### HU-01: Registro de Nuevo Usuario y Asignación de Rol
**Como** administrador del sistema
**Quiero** registrar a un nuevo usuario con sus datos personales, credenciales y asignarle un rol (admin, treasurer o student)
**Para que** pueda acceder al sistema con los permisos correspondientes a su cargo o estado.

#### Criterios de Aceptación, Validaciones y Restricciones
- El sistema debe recibir: Nombre completo, Correo electrónico, Contraseña y Rol.
- **Roles permitidos**: Administrativo (`admin`, `treasurer`) o Estudiante (`student`). Se sugiere usar una clase Enum o un Value Object `Role` (como ya existe en `Tito\App\Entity\Role`).
- **Validaciones**:
  - El correo electrónico debe tener un formato válido.
  - El correo electrónico debe ser **único** en el sistema (validar en el `UserRepository` antes de insertar).
  - La contraseña debe cumplir criterios mínimos de seguridad (ej. mínimo 8 caracteres, contener letras y números).
- **Restricciones**: El registro de roles administrativos (admin, treasurer) debe estar estrictamente protegido y solo podrá ser ejecutado por un usuario que ya sea administrador.

> **Comentario de Implementación**: 
> - En `UserService`, crear un método `registerUser(array $data)` que aplique el hash a la contraseña usando `password_hash()` antes de instanciar la Entidad `User`.
> - Generar automáticamente un nuevo `UUID` para la creación del usuario.

---

### HU-02: Listado de Usuarios Filtrado por Rol
**Como** administrador
**Quiero** listar a los usuarios del sistema, pudiendo verlos todos juntos o filtrarlos por categoría (administrativos o estudiantes)
**Para que** pueda auditar y administrar fácilmente los grupos de usuarios.

#### Criterios de Aceptación, Validaciones y Restricciones
- El sistema debe mostrar una lista con la información esencial de cada usuario (Nombre, Correo, Rol).
- El sistema debe permitir aplicar un filtro en la consulta:
  - Mostrar "Todos".
  - Mostrar "Administrativos" (roles `admin` y `treasurer`).
  - Mostrar "Estudiantes" (rol `student`).
- **Validaciones**: Ningún usuario no autenticado o sin rol de administrador debe tener acceso a este endpoint.
- **Restricciones**: Implementar paginación a nivel de `Repository` (LIMIT, OFFSET) para evitar cuellos de botella en la memoria si el volumen de usuarios es alto.

> **Comentario de Implementación**: 
> - En el `UserRepository`, crear el método `findAllByRoles(array $roles, int $limit, int $offset)` que retorne un array de objetos de tipo `Entity\User`. Si el array `$roles` está vacío, trae todos.

---

### HU-03: Búsqueda de Usuario por Correo
**Como** administrador
**Quiero** buscar un usuario específico introduciendo su dirección de correo electrónico
**Para que** pueda localizar rápidamente el registro sin tener que buscar página por página.

#### Criterios de Aceptación, Validaciones y Restricciones
- El sistema debe contar con un campo de búsqueda exacta o parcial por email.
- **Validaciones**: Sanear la entrada del correo para evitar inyección de SQL o XSS (si la búsqueda es parcial usar `LIKE %...%` con parámetros vinculados).
- **Restricciones**: Si el usuario no existe, se debe retornar un mensaje amigable o una lista vacía, sin exponer información del error interno o de la base de datos.

> **Comentario de Implementación**: 
> - Si se requiere búsqueda exacta, utilizar el método `findByEmail(string $email): ?User` existente o agregarlo al `UserRepository`.

---

### HU-04: Actualización de Datos Personales
**Como** administrador o como el propio usuario
**Quiero** actualizar la información personal (como nombre completo u otros datos demográficos)
**Para que** la información se mantenga actualizada en caso de errores o cambios.

#### Criterios de Aceptación, Validaciones y Restricciones
- El sistema debe permitir modificar campos de datos personales como el Nombre Completo.
- **Restricciones**:
  - El propio usuario NO puede modificar su propio Rol. Solo un Administrador puede modificar roles.
  - Si se permite cambiar el correo, se debe volver a verificar que el nuevo correo sea **único** y no pertenezca a otra cuenta existente (excluyendo el propio ID del usuario actual).
- **Validaciones**: Validar que los campos enviados no estén vacíos.

> **Comentario de Implementación**: 
> - Crear un método `updatePersonalInfo(UUID $userId, string $newFullName)` en el `UserService`.
> - Extraer la entidad desde el repositorio, llamar a métodos *setters* si los hay, o instanciar un nuevo modelo modificado y luego pasarlo a `UserRepository::update(User $user)`.

---

### HU-05: Eliminación de Usuario
**Como** administrador
**Quiero** eliminar la cuenta de un usuario
**Para que** deje de tener acceso al sistema y se liberen recursos.

#### Criterios de Aceptación, Validaciones y Restricciones
- El sistema debe requerir una confirmación antes de ejecutar la eliminación.
- **Validaciones**: Verificar que el ID proporcionado existe antes de intentar eliminar.
- **Restricciones**:
  - Un administrador **NO puede eliminarse a sí mismo** (validar `$currentUser->getId() !== $userIdToDelete`).
  - Evaluar la implementación de *Soft Delete* (borrado lógico agregando un campo `deleted_at`) en lugar de borrar el registro físico en la base de datos, en caso de que existan registros relacionados (pagos, calificaciones) que se romperían por integridad referencial.

> **Comentario de Implementación**: 
> - Implementar método `delete(UUID $userId)` en `UserRepository`. 
> - Si se adopta borrado físico y hay claves foráneas, asegurar que la base de datos maneje el comportamiento con `ON DELETE CASCADE` o restringirlo explícitamente y mostrar un error controlado informando que el usuario tiene historial en el sistema.