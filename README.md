# API Saren INTT - Módulo de Verificación y Registro con JWT

Este proyecto es una API en Laravel diseñada para realizar la verificación cruzada de usuarios entre el sistema principal (**SAREN** - tabla `user_sarens`) y el sistema secundario (**INTT** - tabla `users`), integrando un esquema de autenticación sin contraseña mediante la validación de correos electrónicos y emisión de JSON Web Tokens (JWT).

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
5. **Siembra de Datos (Seeding)**:
   Si deseas poblar la base de datos de SAREN con los registros de prueba oficiales:
   ```bash
   php artisan db:seed --class=UserSarenSeeder
   ```
6. **Iniciar el Servidor Local**:
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

### 1. Verificación Cruzada de Usuario
* **Ruta**: `GET /users/verify/{documento}`
* **Descripción**: Verifica si un usuario existe en la tabla principal (SAREN). Si existe, lo cruza contra la tabla secundaria (INTT):
  - **Si no existe en SAREN**: Retorna `404 Not Found`.
  - **Si existe en SAREN pero no en INTT**: Registra al usuario automáticamente en INTT utilizando los datos provistos en SAREN (mapeando `correo_saren` a `correo`), genera un JWT de acceso y retorna `201 Created`.
  - **Si existe en ambas y los correos coinciden**: Otorga acceso directo, generando y devolviendo un JWT con respuesta `200 OK`.
  - **Si existe en ambas pero los correos difieren**: Retorna `200 OK` indicando que los correos son distintos, mostrando ambos correos enmascarados para solicitar su actualización.
* **Parámetros URL**:
  - `documento` (obligatorio): Código de documento completo, por ejemplo: `V6436669`, `J-987654321`.

#### Casos de Uso y Respuestas:

* **Caso A: Usuario no existe en SAREN**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V99999999`
  - *Respuesta (HTTP 404)*:
    ```json
    {
      "existe_en_saren": false,
      "persona_tipo": "natural",
      "mensaje": "El usuario no está registrado en el sistema principal (SAREN)."
    }
    ```

* **Caso B: Usuario registrado en SAREN pero NO en INTT (Registro automático)**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V6436669`
  - *Respuesta (HTTP 201)*:
    ```json
    {
      "existe_en_saren": true,
      "existe_en_intt": true,
      "mensaje": "El usuario no existía en el sistema secundario (INTT) pero ha sido registrado automáticamente con los datos de SAREN.",
      "user": {
        "primer_nombre": "JOSE",
        "segundo_nombre": "RAFAEL",
        "primer_apellido": "ROMERO",
        "segundo_apellido": "CELIS",
        "tipo_documento": "V",
        "numero_documento": "6436669",
        "correo": "jose.romero669@saren.gob.ve",
        "fecha_nacimiento": "1982-08-24T00:00:00.000000Z",
        "nombre_empresa": "No Aplica",
        "digito_rif": "64366696",
        "telef_celular": "04267460692",
        "updated_at": "2026-07-23T18:07:30.000000Z",
        "created_at": "2026-07-23T18:07:30.000000Z",
        "id": 3
      },
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
    ```

* **Caso C: Usuario existe en ambas tablas y correos coinciden (Acceso concedido)**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V6436669`
  - *Respuesta (HTTP 200)*:
    ```json
    {
      "existe_en_saren": true,
      "existe_en_intt": true,
      "mensaje": "Validación exitosa. Acceso concedido.",
      "user": { ... },
      "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
    }
    ```

* **Caso D: Usuario existe en ambas tablas pero con correos diferentes**
  - *Endpoint*: `GET http://localhost:8000/users/verify/V6436669`
  - *Respuesta (HTTP 200)*:
    ```json
    {
      "existe_en_saren": true,
      "existe_en_intt": true,
      "correos_diferentes": true,
      "correo_saren_enmascarado": "jo************@saren.gob.ve",
      "correo_intt_enmascarado": "di*************@example.com",
      "mensaje": "El correo electrónico registrado en INTT no coincide con el de SAREN. Por favor, actualice su correo electrónico."
    }
    ```

---

### 2. Actualización de Correo Electrónico (Únicamente en INTT)
* **Ruta**: `POST /users/update-email`
* **Descripción**: Permite actualizar **únicamente** la columna `correo` en el sistema secundario (INTT / tabla `users`) para un usuario específico, con los datos ingresados por el usuario. Genera un nuevo JWT token de acceso una vez modificado.
* **Headers**: `Content-Type: application/json`, `Accept: application/json`
* **Cuerpo de la Petición (JSON)**:
  ```json
  {
    "tipo_documento": "V",
    "numero_documento": "6436669",
    "correo": "jose.romero669@saren.gob.ve"
  }
  ```
* **Respuesta (HTTP 200 OK)**:
  ```json
  {
    "mensaje": "Correo electrónico actualizado exitosamente en el sistema secundario (INTT).",
    "user": {
      "id": 3,
      "primer_nombre": "JOSE",
      "primer_apellido": "ROMERO",
      "tipo_documento": "V",
      "numero_documento": "6436669",
      "correo": "jose.romero669@saren.gob.ve"
      ...
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
  ```

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
      "id": 3,
      "primer_nombre": "JOSE",
      "primer_apellido": "ROMERO",
      "correo": "jose.romero669@saren.gob.ve",
      "tipo_documento": "V",
      "numero_documento": "6436669"
    }
  }
  ```

---

## Arquitectura de Código

El flujo se organiza de la siguiente manera:

* **[app/Services/JwtService.php](file:///c:/xampp/htdocs/api-sarenIntt/app/Services/JwtService.php)**: Utilidad que realiza la codificación Base64Url y firma los tokens con el algoritmo HMAC SHA-256 usando el valor de `JWT_SECRET`.
* **[app/Http/Middleware/JwtMiddleware.php](file:///c:/xampp/htdocs/api-sarenIntt/app/Http/Middleware/JwtMiddleware.php)**: Middleware de Laravel que valida que la cabecera `Authorization` contenga un token JWT de firma válida y no expirado.
* **[app/Http/Controllers/UserController.php](file:///c:/xampp/htdocs/api-sarenIntt/app/Http/Controllers/UserController.php)**: Controlador principal que gestiona las validaciones cruzadas entre `UserSaren` y `User`, registro automático y actualización exclusiva de correos.
