# API E-commerce

API REST desarrollada con Laravel para gestionar un sistema de comercio electrónico. Permite registrar y autenticar usuarios, consultar productos públicamente, administrar el catálogo, crear órdenes de compra, consultar el historial de compras y procesar pagos mediante Stripe.

## Datos de prueba

El proyecto incluye seeders para cargar usuarios, productos y una orden de prueba en la base de datos.

Ejecuta:

```bash
php artisan db:seed
```

También puedes reiniciar la base de datos y ejecutar todos los seeders:

```bash
php artisan migrate:fresh --seed
```

### Credenciales de acceso

```text
Usuario: cliente@example.com
Contraseña: password
```

También existe un usuario administrador:

```text
Usuario: admin@example.com
Contraseña: password
```

Estas credenciales pueden utilizarse en el endpoint `POST /api/login` para obtener el token de autenticación y acceder a las rutas protegidas.