![logo](./public/images/logo.png)
# SORTEOS CROSSOVER

## Características principales

Sorteos CrossOver ofrece una plataforma para organizar y participar en distintos tipos de sorteos, con funcionalidades específicas para usuarios comunes y administradores.

### Sorteos

1. **Imagen de Logo** --> Posibilidad de adjuntar una imagen de logo para cada sorteo.
2. **Modalidades**: 
   1. Sorteos ilimitados
   2. Sorteos con máxima cantidad de participantes
3. **Múltiples ganadores** --> Posibilidad de añadir la opción de que haya más de un ganador
4. **Notificación por Correo** --> Al realizarse el sorteo, se envía una notificación por correo electrónico a **todos los participantes**.

### Permisos

La aplicación distingue entre dos roles:
1. **Usuario Común**
   1. **Página principal** --> muestra todos los sorteos disponibles
   2. **Sorteos pendientes** --> Listado de sorteos a los que el usuario se ha unido y aún no se han realizado
   3. **Sorteos realizados** --> Listado de sorteos finalizados para ver quién fue el ganador
2. **Usuario Admin**
   1. **Ventana inicial** --> Sorteos disponibles, pendientes y ocultos/realizados
   2. **Tarjeta de sorteo** --> Cada tarjeta incluye, nombre, descripción, fecha, máximo de participantes y tres botones:
      1. **Unirse**
      2. **Ver participantes**
      3. **Ocultar**
   3. **Botones adicionales**
      1. **Histórico** --> Registro de todos los ganadores de sorteos pasados
      2. **Añadir admin** --> Permite crear un nuevo usuario admin
      3. **Crear nuevo sorteo** --> Acceso al formulario para configurar y lanzar un nuevo sorteo

### Proceso de instalación

Con el siguiente comando podrás descargar el código fuente al directorio sorteos-crossover 

``` 
git clone https://github.com/aimansito/AppSorteos.git sorteos-crossover 
```

Cambio al directorio recién clonado

```
cd sorteos-crossover
```

#### Instalación de dependencias

Necesario tener composer y Symfony instalados para poder instalar todas las dependencias del proyecto. Tras obtener dichas aplicaciones, instalaremos todas las dependencias definidas en el archivo `composer.json`

```
composer install
```

### Configuración y despliegue

Para poder desplegar de forma correcta la aplicación para su correcto funcionamiento, hay que configurar las variables de entorno y revisar un Controller

#### Archivos a editar

`.env`

Asegurate de configurar correctamente los siguientes parámetros de entorno:
1. `MAILER_DSN` --> Para el envio de correos electrónicos. **Ejemplo con Gmail**:
```code
MAILER_DSN=gmail://USER:PASS@localhost
```
2. `DATABASE_URL` --> Para la conexión a la base de datos. **Ejemplo con MySQL**:
```code
DATABASE_URL="mysql://root:Contraseña@127.0.0.1:3306/sorteo?serverVersion=8.0&charset=utf8mb4"
```

`src/Controller/SorteoController.php`

En este controlador, debes establecer la constante para el correo remitente, el cual se utilizará para el envío de notificaciones.

```php
private const CORREO = 'correo@correo.com'; // <- Reemplazar por el correo real
```

Una vez realizados estos cambios procederemos a ejecutar el siguiente comando en la consola de Symfony en el caso de que no tengamos creada la base de datos:

```
php bin/console doctrine:database:create
```

Posteriormente ejecutaremos las migraciones para poder crear la estructura de dicha base de datos definida en el archivo `.env`

```
php bin/console doctrine:migrations:migrate
```

### Ejecutar la aplicación

Para iniciar el servidor web local de Symfony simplemente habrá que ejecutar el comando 

```
symfony server:start
```

Este comando hará que la aplicación se muestre en [127.0.0.1:8000/](127.0.0.1:8000/)