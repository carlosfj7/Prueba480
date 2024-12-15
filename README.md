# User Management API

Este proyecto es una API para la gestión de usuarios utilizando **Symfony**. La API permite realizar las siguientes operaciones:

- Registrar un nuevo usuario.
- Obtener la lista de todos los usuarios registrados.
- Eliminar un usuario.

## **Requisitos previos**

Antes de comenzar, asegúrate de tener instalados los siguientes requisitos:

- **PHP 8.1 o superior**.
- **Composer**.
- **Symfony CLI** (opcional pero recomendado).
- **SQLite** (No se necesita instalación).
- **Cliente HTTP**(Postman o Thunder Client)

## **Instalación**

1. **Clona el repositorio**:
   ```bash
   git clone https://github.com/carlosfj7/Prueba480.git
   cd prueba480
   ```

2. **Instala las dependencias**:
   ```bash
   composer install
   ```

3. **Configura la base de datos**:
   Para la base de datos del proyceto se ha utilzado SQLite por tanto, 
   para utilizarlo solo sera necesario crear el documento de la base de datos en el directorio var/.
   Tras ello ejecuta el siguiente comando para creaer el esquema de la base de datos:
    ```bash
       php bin/console doctrine:schema:create
   ```
   Abre el archivo `.env` en la raíz del proyecto y configura la conexión de base de datos:
   ```env
   DATABASE_URL="sqlite:///%kernel.project_dir%/var/nombre_de_la_BBDD.db"
   ```

4. **Ejecuta las migraciones**:
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
5. **Configuración JWT**
   Para generar las claves necesarias, ejecuta el siguiente comando:
   ```bash
    php bin/console lexik:jwt:generate-keypair
   ```
   Esto creará los archivos config/jwt/private.pem y config/jwt/public.pem.
   Para confirmar que las variabes esten bien configuradas abre el archivo .env.
   En el archivo config/packages/security.yaml esta la configuración para proteger las rutas JWt,
   Aseguraet de que estas esten bien defenidad para que la API tenga las rutas protegidadas.
   
6. **Ejecuta el servidor de desarrollo**:
   ```bash
   symfony server:start
   ```
   La API estará disponible en [http://127.0.0.1:8000](http://127.0.0.1:8000).

## **Endpoints**

### **Registro de un usuario**

- **Endpoint**: `/user/register`
- **Método**: `POST`

**Cuerpo de la solicitud (JSON)**:
```json
[
  {
    "email": "test@example.com",
    "password": "password123",
    "nombre": "Test User",
    "edad": 25
  }
]
```

**Respuesta exitosa**:
- **Código de estado**: `201 Created`
- **Respuesta**: `"Usuario creado correctamente"`

**Errores**:
- **Código de estado**: `400 Bad Request`
- **Respuesta**: Mensaje de error indicando la causa (por ejemplo, `"El email introducido ya está registrado"`).

### **Obtener todos los usuarios**

- **Endpoint**: `/user/get`
- **Método**: `GET`

**Respuesta exitosa**:
- **Código de estado**: `200 OK`
- **Respuesta**:
```json
[
  {
    "email": "test@example.com",
    "nombre": "Test User",
    "edad": 25
  }
]
```

**Errores**:
- **Código de estado**: `404 Not Found`
- **Respuesta**: `"No hay ningún usuario"`.

### **Eliminar un usuario**

- **Endpoint**: `/user/delete`
- **Método**: `DELETE`

**Cuerpo de la solicitud (JSON)**:
```json
{
  "id": 1
}
```

**Respuesta exitosa**:
- **Código de estado**: `200 OK`
- **Respuesta**: `"Usuario eliminado correctamente"`

**Errores**:
- **Código de estado**: `404 Not Found`
- **Respuesta**: `"No se ha podido encontrar el usuario indicado"`.

## **Ejecutar los tests**

Este proyecto incluye tests para los servicios y controladores.

Antes de iniciar los test crear un usario con los siguientes datos:
```json
[
  {
    "email":"test@test.es,
    "password":"12345",
    "nombre":"test",
    "edad":21,
  }
]
```
Este usuario servirai para los el JWT en los tests

Ejecuta los tests con el siguiente comando:
```bash
php bin/phpunit
```



