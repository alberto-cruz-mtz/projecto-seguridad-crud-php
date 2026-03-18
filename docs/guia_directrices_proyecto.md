# Guía de Orientación y Directrices del Proyecto

Este documento proporciona una visión general de la arquitectura, el stack tecnológico, los estándares de codificación y los principios de seguridad aplicados en el **Módulo de Seguridad CRUD en PHP**. Su propósito es servir de referencia para los desarrolladores actuales y futuros que contribuyan al proyecto.

## 1. Stack Tecnológico

El proyecto se sustenta en un stack moderno, minimalista y libre de frameworks pesados para un mayor control sobre el ciclo de vida y la seguridad.

- **Lenguaje Base**: PHP 8.2+
- **Gestor de Dependencias**: Composer (`composer.json`)
- **Enrutamiento HTTP**: `alberto-cruz-mtz/vanilla-router` (^0.1.0) - Enrutador ligero para la definición de rutas semánticas.
- **Acceso a Base de Datos**: Extensión `ext-pdo` nativa de PHP (Uso obligatorio de *Prepared Statements*).
- **Entorno Web**: Todas las peticiones convergen al *Front Controller* ubicado en la carpeta `public/` (presumiblemente `public/index.php`).

## 2. Arquitectura de Software

El proyecto sigue una arquitectura fuertemente orientada a separar responsabilidades (Separation of Concerns), estructurándose en capas especializadas:

- `src/Entity/`: **Entidades del Dominio.** Clases planas y puras que representan los conceptos principales del negocio (ej. `User.php`, `Role.php`). No contienen lógica de base de datos ni acceso a servicios de red.
- `src/Repository/`: **Capa de Persistencia (Data Mapper).** Clases responsables de la comunicación exclusiva con la base de datos a través de PDO. Encapsulan las consultas SQL (ej. `UserRepository`).
- `src/Service/`: **Lógica de Negocio (Casos de Uso).** Orquesta las acciones principales del sistema de manera independiente al protocolo HTTP. Consumen repositorios y operan sobre las entidades.
- `src/Controller/`: **Controladores de Presentación.** Procesan la petición HTTP de entrada, extraen los datos (Request), invocan a la capa de servicios y retornan la respuesta (Response o View).
- `src/Middleware/`: **Filtros HTTP.** Interceptan el ciclo de vida de la petición para realizar tareas transversales como Autenticación (verificar la sesión JWT o tradicional), Autorización (permisos basados en Roles) o Logging.
- `src/Core/`: **Núcleo y Utilidades.** Clases de soporte agnósticas al dominio, tales como el manejo de Identificadores (ej. `UUID.php`).
- `src/View/`: **Capa de Renderizado.** Manejo de las plantillas e interfaces de usuario que devuelven HTML.

### Flujo Típico de Ejecución
1. Cliente envía petición HTTP a `public/index.php`.
2. El enrutador (`vanilla-router`) resuelve el endpoint.
3. El `Middleware` intercepta para verificar permisos de seguridad (ej. token o sesión válida).
4. El `Controller` recibe la petición saneada, recolecta la información y llama al `Service`.
5. El `Service` aplica la regla de negocio y se comunica con el `Repository`.
6. El `Repository` ejecuta sentencias preparadas en la Base de Datos y devuelve una `Entity`.
7. El `Controller` renderiza la vista en `View` y la retorna al cliente.

## 3. Estilo de Codificación y Estándares

El proyecto adhiere estrictamente a los estándares formales del PHP-FIG para garantizar interoperabilidad y limpieza.

### PSR-4: Autoloading
Todas las clases del proyecto se cargan de forma automática gracias al estándar **PSR-4** configurado en `composer.json` bajo el namespace raíz `Tito\App\`, el cual mapea directamente al directorio `src/`.
- Regla de oro: **1 Archivo = 1 Clase**. El nombre del archivo debe coincidir exactamente con el nombre de la clase.

### PSR-12: Estilo de Código (Coding Style)
- Las clases, interfaces y traits deben usar notación `PascalCase` (Ej: `UserProfileService`).
- Las propiedades, métodos y variables deben usar notación `camelCase` (Ej: `$passwordHash`, `getFullName()`).
- Indentación basada en 4 espacios (no tabuladores).
- Las constantes deben declararse en mayúsculas separadas por guion bajo (Ej: `MAX_LOGIN_ATTEMPTS`).

### Tipado Estricto (Strict Typing)
Aprovechando las características de PHP 8+, es mandatorio usar la declaración estricta de tipos:
- **Tipado de propiedades**: Todas las propiedades de una clase deben tener su tipo declarado.
- **Parámetros y Retorno**: Todos los métodos deben declarar el tipo de datos que reciben y el tipo de dato que devuelven.
```php
public function findById(UUID $id): ?User 
{ 
    // ... 
}
```

## 4. Principios de Seguridad y Calidad del Código

El objetivo central del sistema es proveer un módulo resistente a vulnerabilidades, por lo cual se deben acatar las siguientes normas:

- **Clean Code y SOLID**: Mantener las funciones pequeñas, delegar responsabilidades únicas a las clases (SRP) e inyectar dependencias (DI) en lugar de instanciarlas estáticamente dentro de las clases.
- **Inyección SQL**: **ESTRICTAMENTE PROHIBIDO** concatenar variables en cadenas SQL. Toda interacción mediante PDO en la capa `Repository` debe efectuarse con **Prepared Statements**.
- **Gestión de Credenciales**: Las contraseñas jamás deben almacenarse, loguearse ni transmitirse en texto plano. Se debe emplear la función nativa `password_hash()` con los algoritmos integrados más fuertes (bcrypt o argon2i).
- **Sanitización y Validación**: Nunca confiar en la entrada del usuario (`$_GET`, `$_POST`). Todo dato que ingresa debe ser validado formalmente en el `Controller` o `Middleware` antes de tocar la capa `Service`.
- **Value Objects**: Se recomienda el uso de objetos inmutables para representar datos críticos, como la clase `Tito\App\Core\UUID`, que garantiza que un identificador es válido en todo momento e impide alteraciones accidentales en el ciclo de vida.