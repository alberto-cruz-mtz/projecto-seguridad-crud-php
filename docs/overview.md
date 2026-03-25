Documento de Definición del Proyecto: Módulo de Seguridad Web

1. Descripción General

El presente proyecto tiene como objetivo el desarrollo de un módulo de seguridad funcional para una aplicación web, aplicando rigurosamente principios de código seguro, buenas prácticas de desarrollo y lineamientos de Clean Code.

El sistema gestionará la autenticación de usuarios, el control de accesos basado en roles y la recuperación de contraseñas. Se implementará una arquitectura robusta que garantice la integridad de los datos y la separación de responsabilidades, asegurando que las vistas (frontend) estén completamente aisladas de la lógica de negocio (backend). 2. Stack Tecnológico y Entorno

Para cumplir con las restricciones técnicas y mantener un control total sobre el flujo de la aplicación, el proyecto utilizará las siguientes tecnologías:

    Backend: PHP de forma exclusiva.

    Frontend: HTML, CSS y JavaScript nativo. La manipulación del DOM se realizará estrictamente mediante las APIs nativas del navegador. No se utilizará código PHP incrustado en las vistas.

    Base de Datos: Sistema gestor relacional (MySQL, MariaDB o PostgreSQL).

    Librerías Permitidas:

        vanilla-router: Librería de desarrollo personal para gestionar el enrutamiento del lado del cliente.

        PHPMailer: Para el envío seguro de correos electrónicos en el módulo de recuperación de contraseñas.

3. Arquitectura y Estructura de Carpetas

El proyecto estará fundamentado en el patrón de diseño MVC (Modelo-Vista-Controlador) con una estricta separación de responsabilidades. La estructura de directorios será la siguiente:

    📂 src/ (Lógica de backend - Solo PHP)

        controller/: Controladores que reciben y procesan las peticiones HTTP.

        service/: Capa que contiene la lógica de negocio.

        repository/: Encargada de las consultas y persistencia en la base de datos.

        model/: Clases que representan las entidades del sistema.

        core/: Configuraciones globales, conexión a BD y enrutador base.

        dto/: Data Transfer Objects para el manejo de datos entre capas.

    📂 resources/ (Recursos y Frontend)

        views/: Archivos puramente estáticos en HTML, CSS y JS (login, dashboards, etc.).

        sql/: Scripts de creación y configuración de la base de datos relacional.

4. Alcance Funcional

El desarrollo contempla los siguientes módulos principales:

    Módulo de Autenticación (Login): Formulario con validación tanto en el cliente como en el servidor. Incluirá manejo de sesiones PHP seguras (regeneración de ID), bloqueo temporal de cuenta por N intentos fallidos (protección contra fuerza bruta) y mensajes de error personalizados.

    Recuperación de Contraseña: Flujo de restablecimiento donde el usuario ingresará su correo registrado para recibir un enlace (vía PHPMailer). Este enlace contendrá un token único y seguro con un tiempo de vida limitado (entre 30 minutos y 2 horas).

    Gestión de Usuarios (CRUD): Módulo para listar, crear, editar y eliminar cuentas de usuario (con confirmación previa), solicitando como datos mínimos: nombre completo, correo, contraseña y rol.

    Dashboard y Menú de Navegación: Una vez autenticado, el usuario será redirigido a un panel según su rol (Admin, Treasurer, Student). Cada menú de navegación incluirá:

        Enlace al CRUD de usuarios.

        Dos opciones con paneles estáticos para validar el control de accesos (Configuración y Registro de pagos).

        Botón para cerrar sesión e indicador del usuario autenticado.

5. Seguridad y Calidad de Código

El criterio central del sistema es la seguridad. Se implementarán las siguientes medidas y estándares:

Protecciones Obligatorias:

- Prevención de inyecciones SQL (SQL Injection).

- Prevención de ataques XSS (Cross-Site Scripting).

- Inclusión y validación de tokens de seguridad.

- Almacenamiento y gestión de contraseñas seguras mediante funciones de hashing en PHP.

Control de Accesos: Validación de roles en cada petición al servidor. Todo acceso no autorizado redirigirá al login o mostrará acceso denegado, sin exponer nunca contenido protegido.

Buenas Prácticas: El código fuente respetará los lineamientos de Clean Code. Además, la arquitectura del software demostrará la aplicación de al menos dos principios SOLID.
