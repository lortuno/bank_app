# bank_app
Este proyecto pretende demostrar mediante código limpio y el uso de symfony y composer, cómo gestionar una sencilla app
 bancaria, para aprendizaje personal.
## Objetivo 
Desarrollar una pequeña aplicación web que permita:
* Dar de alta a usuarios: Nombre, apellido, email, dirección, municipio, ciudad y CP.
* Login de los usuarios.
* Edición del perfil de los usuarios.
* Dar de baja a los usuarios, almacenando en un histórico dichas bajas sin datos sensibles (quién y cuándo)
* Crear cuentas bancarias y relacionarlas con los usuarios.
* Un usuario puede tener varias cuentas y varios usuarios pueden estar relacionados
con una misma cuenta.
* Poder sacar e ingresar dinero y con cada movimiento llevar también un registro de los
movimientos.
* Quitar permisos de acceso a cuentas de algún usuario.
* Dar de baja cuentas.

## Especificaciones:
* Todas estas acciones se harán usando servicios
* Las respuestas deben serializarse en formato JSON.
* El número de cuenta es alfanumérico. 
* No se podrá sacar dinero si no dispone de la cantidad requerida y el mensaje tendrá
que ser personalizable con el dinero que dispone.
* Si la operación es mayor de 2000€, la app dirá que no es una operación válida para realizar desde la
aplicación.
* Realización de pruebas para la comprobación que funciona correctamente.

## Configuración del proyecto
**Descargar dependencias de composer**

Descargar composer, y una vez instalado hacer:

```
composer install
```

O, según la versión de composer el comando podría ser:  `php composer.phar install`

**Configurar el fichero de entorno  .env**

Hay que tener un `.env` , si  no existe el fichero duplicar el `.env.dist` y cambiarle el nombre.
Posteriormente reajustar parámetros, especialmente en los referente a base de datos.  `DATABASE_URL`.

**Inicializar la Database**

Ejecutar los siguientes comandos. Los fixtures introducen data en la BD.

```
php bin/console doctrine:database:create
php bin/console doctrine:migrations:diff 
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

**Lanzar los tests de PHPunit**

En la carpeta de tests se encuentran los ficheros para tests de phpunit, que se pueden ejecutar si lanzamos: 

```
sudo apt install phpunit
phpunit
```

Los tests de behat están en features y se ejecutan con: 
``
vendor/bin/behat 
``

O si queremos una suite concreta, por ejemplo: 
``
vendor/bin/behat -s web
``

Las sentencias disponibles están en: 
``
vendor/bin/behat -dl
``

Es necesario que el servidor esté activo para los tests. El servidor se inicializa con: 
``
php -S localhost:8000 -t public
``

**Corrección de código**
Está instalado el php-sniffer, para poder comprobar y corregir desde el terminal los fallos de codificación y mantener 
un código homogéneo. 
Para utilizarlo basta con escribir el comando, el estandar y la carpeta en la que buscar los fallos. 
```
phpcs --standard=PSR2 src
```

Para corregir automáticamente: 
```
phpcbf --standard=PSR2 src
```

Para limpiar caché: 
```
php bin/console cache:clear
```

Resolución de problemas: 
- Si se pide el apcu enabled y no lo tenemos, hay que instalarlo. Podemos comprobar si está activado en /phpinfo buscando
apcu:

``
sudo apt-get install php-apcu
``