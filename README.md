# bank_app
Este proyecto pretende demostrar mediante código limpio y el uso de symfony y composer, cómo gestionar una sencilla app
 bancaria, para aprendizaje personal.
## Objetivo 
Desarrollar una pequeña aplicación web que permita:
* Dar de alta a usuarios: Nombre, apellido, email, dirección, municipio, ciudad y CP.
* Login de los usuarios.
* Edición el perfil de los usuarios.
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
* Si el ingreso es mayor de 2000€, la app dirá que no es una operación válida para realizar desde la
aplicación.
* Realización de pruebas para la comprobación que funciona correctamente

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
