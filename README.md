## Guía de instalación

### Requisitos

- PHP 8.2 o superior
- Composer
- MySQL
- Extensión PHP `pdo_mysql`
- Stripe (claves de prueba)

### Instalación

Clona el repositorio y accede al proyecto:

```bash
git clone <URL_DEL_REPOSITORIO>
cd laravel/API-E-commerce
```

Instala las dependencias:

```bash
composer install
```

Copia el archivo de configuración:

```bash
copy .env.example .env
```

Genera la clave de la aplicación:

```bash
php artisan key:generate
```

Configura la conexión de base de datos en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_ecommerce
DB_USERNAME=root
DB_PASSWORD=
```

Configura las credenciales de Stripe:

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxxx
```

Crea la base de datos `api_ecommerce` en MySQL y ejecuta las migraciones junto con los seeders:

```bash
php artisan migrate:fresh --seed
```

Inicia el servidor:

```bash
php artisan serve
```

La API estará disponible en:

```text
http://127.0.0.1:8000/api
```

### Documentación Swagger

Genera la documentación:

```bash
php artisan l5-swagger:generate
```

Accede a Swagger en:

```text
http://127.0.0.1:8000/api/documentation
```

### Inicio de sesión

Utiliza el endpoint `POST /api/login` con las credenciales creadas por los seeders:

```json
{
    "email": "cliente@example.com",
    "password": "password"
}
```

Copia el token de la respuesta y envíalo en las rutas protegidas:

```text
Authorization: Bearer TU_TOKEN
Accept: application/json
```
