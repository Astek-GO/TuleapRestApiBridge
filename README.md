README
======

What is Tuleap REST API Bridge ?
-----------------

Tuleap REST API Bridge is a bridge between a Tuleap server and a Jetbrain IDE (IntelliJ, PHPStorm...).
It allows to use a Tuleap tracker as a generic tasks provider for the IDE.

Requirements
------------

Tuleap REST API Bridge is only supported on PHP 5.3 and up.

Installation
------------

1. Copy sources to the destination dir
2. Download vendors via Composer with :
```shell
composer install
```
3. Edit configuration in /app/config.php file
4. Edit Apache configuration with following :
```apache
Alias "/bridge" "<destination dir>"
<Directory <destination dir>>
    # Enable the .htaccess rewrites
    AllowOverride All
    Order allow, deny
    Allow from All
    # *** PHP log Configuration ***
    php_value error_log "/opt/tuleap-rest-api-bridge/log/php_errors.log"
</Directory>
```

Usage
-------------
1. Access Jetbrains IDE configuration helper via [https://host.address/bridge/config](https://host.address/bridge/config)
2. In IDE, choose "Tools" > "Tasks & Context" > "Configure Servers..."
3. Add a new "generic" server and use values given by the Jetbrains IDE configuration helper


Documentation
-------------

Use Jetbrains IDE configuration helper via [https://host.address/bridge/config](https://host.address/bridge/config)
