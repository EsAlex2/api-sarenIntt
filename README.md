# API Saren INTT - Módulo de Verificación y Registro con JWT

Este proyecto es una API en Laravel diseñada para realizar la verificación y registro automatizado de usuarios (Personas Naturales y Empresas), integrando un esquema de autenticación sin contraseña mediante la validación de correos electrónicos y emisión de JSON Web Tokens (JWT).

La infraestructura JWT ha sido desarrollada de forma nativa en la aplicación para garantizar la máxima compatibilidad, velocidad y estabilidad en Laravel.

---

## Requisitos de Instalación

1. **Clonar/Copiar el repositorio** y entrar en la carpeta del proyecto.
2. **Configuración de Dependencias**:
   ```bash
   composer install
   ```
3. **Archivo de Entorno**:
   Copia el archivo `.env.example` como `.env` (si aún no existe) y define tus credenciales de base de datos.
   
   Asegúrate de agregar la clave `JWT_SECRET` al final del archivo `.env`:
   ```env
   JWT_SECRET=tu_clave_secreta_de_al_menos_32_caracteres_aleatorios
   ```
4. **Ejecutar las Migraciones**:
   ```bash
   php artisan migrate
   ```
5. **Iniciar el Servidor Local**:
   ```bash
   php artisan serve --port=8000
   ```

---

## Clasificación de Tipo de Persona

El sistema analiza automáticamente la primera letra del parámetro `{documento}` enviado al endpoint de verificación para deducir el tipo de persona:
* **Persona Natural**: Si el documento inicia con **`V`** (Venezolano), **`E`** (Extranjero) o **`P`** (Pasaporte).
* **Empresa (Jurídico/Gubernamental)**: Si el documento inicia con **`J`** (Jurídico) o **`G`** (Gubernamental).

---

## Documentación de Endpoints

### 1. Verificación de Usuario
* **Ruta**: `GET /users/verify/{documento}`
* **Descripción**: Verifica si un usuario existe en la base de datos por su número de documento e identifica su tipo de persona.
* **Parámetros URL**:
  - `documento` (obligatorio): Código de documento completo, por ejemplo: `V27391753`, `J-987654321`.
* **Parámetros Query**:
  - `correo` (opcional): Correo electrónico del usuario para validación.

#### Casos de Uso y Respuestas:

* **Caso A: Usuario no existe**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V99999999`
  - *Respuesta (HTTP 200)*:
    ```json
    {
      "existe": false,
      "persona_tipo": "natural",
      "mensaje": "El usuario no existe. Debe registrarse (POST)."
    }
    ```

* **Caso B: Usuario existe (No se envía el correo para verificar)**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V27391753`
  - *Respuesta (HTTP 200)*:
    ```json
    {
      "existe": true,
      "persona_tipo": "natural",
      "correo_registrado_parcial": "ju********@example.com",
      "mensaje": "Usuario encontrado. Por favor, valide su correo electrónico ?correo=tu_correo@dominio.com para autenticarse."
    }
    ```

* **Caso C: Usuario existe, pero el correo no coincide**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V27391753?correo=incorrecto@domain.com`
  - *Respuesta (HTTP 400)*:
    ```json
    {
      "existe": true,
      "persona_tipo": "natural",
      "mensaje": "El correo electrónico no coincide con el registrado."
    }
    ```

* **Caso D: Usuario existe y el correo es correcto (Autenticación)**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V27391753?correo=juan.perez@example.com`
  - *Respuesta (HTTP 200)*:
    ```json
    {
      "existe": true,
      "persona_tipo": "natural",
      "mensaje": "Validación exitosa. Acceso concedido.",
      "user": {
        "id": 1,
        "primer_nombre": "Juan",
        "segundo_nombre": "Carlos",
        "primer_apellido": "Perez",
        "segundo_apellido": "Rodriguez",
        "tipo_documento": "V",
        "numero_documento": "27391753",
        "correo": "juan.perez@example.com",
        "fecha_nacimiento": "1990-05-15",
        "nombre_empresa": "No Aplica",
        "digito_rif": "273917530",
        "telef_celular": "04121234567",
        "created_at": "2026-07-23T16:04:15.000000Z",
        "updated_at": "2026-07-23T16:04:15.000000Z"
      },
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEsImVtYWlsIjoianVhbi..."
    }
    ```

---

### 2. Registro de Usuario (Insert Automático)
* **Ruta**: `POST /users`
* **Descripción**: Registra un nuevo usuario en la base de datos y genera automáticamente su token JWT.
* **Headers**: `Content-Type: application/json`, `Accept: application/json`
* **Cuerpo de la Petición (JSON)**:
  ```json
  {
    "primer_nombre": "Juan",
    "segundo_nombre": "Carlos",
    "primer_apellido": "Perez",
    "segundo_apellido": "Rodriguez",
    "tipo_documento": "V",
    "numero_documento": "27391753",
    "correo": "juan.perez@example.com",
    "fecha_nacimiento": "1990-05-15",
    "nombre_empresa": "No Aplica",
    "digito_rif": "273917530",
    "telef_celular": "04121234567"
  }
  ```
* **Respuesta (HTTP 201 Created)**:
  Devuelve un mensaje de confirmación, los datos del usuario registrado y su correspondiente `token` JWT.

---

### 3. Perfil de Usuario Protegido (Prueba de JWT)
* **Ruta**: `GET /users/me`
* **Descripción**: Obtiene la información del usuario autenticado a partir del token provisto en las cabeceras.
* **Headers requeridos**:
  - `Authorization: Bearer <TOKEN_JWT_OBTENIDO>`
  - `Accept: application/json`
* **Respuesta (HTTP 200 OK)**:
  ```json
  {
    "user": {
      "id": 1,
      "primer_nombre": "Juan",
      "primer_apellido": "Perez",
      "correo": "juan.perez@example.com",
      "tipo_documento": "V",
      "numero_documento": "27391753"
      ...
    }
  }
  ```
* **Respuesta sin Token o con Token Expirado (HTTP 401 Unauthorized)**:
  ```json
  {
    "error": "Token inválido o expirado."
  }
  ```

---

## Rutas y Middleware de la Aplicación

El flujo se organiza de la siguiente manera:

* **[app/Services/JwtService.php](file:///c:/xampp/htdocs/api-sarenIntt/app/Services/JwtService.php)**: Utilidad que realiza la codificación Base64Url y firma los tokens con el algoritmo HMAC SHA-256 usando el valor de `JWT_SECRET`.
* **[app/Http/Middleware/JwtMiddleware.php](file:///c:/xampp/htdocs/api-sarenIntt/app/Http/Middleware/JwtMiddleware.php)**: Middleware de Laravel que valida que la cabecera `Authorization` contenga un token JWT de firma válida y no expirado.
* **[app/Http/Controllers/UserController.php](file:///c:/xampp/htdocs/api-sarenIntt/app/Http/Controllers/UserController.php)**: Controlador que gestiona la lógica de verificación (GET) y registro (POST).
