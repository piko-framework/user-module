# Piko user module

User management module for [Piko](https://piko-framework.github.io/)

## Features
- Optional registration
- Registration with an optional confirmation per mail
- Password recovery
- Account and profile management
- User management interface
- Permissions management (RBAC)

## Installation

1 - Install module via composer:

```bash
composer require ilhooq/piko-user 
```

2 - Edit your Piko config :

```php
[
  'components' => [
    // ...
    'user' => [
      'class' => 'piko\User',
        'identityClass' => 'piko\user\models\User',
        'accessCheckerClass' => 'piko\user\AccessChecker',
    ],
  ],
  'modules' => [
    // ...
    'user' => [
      'class' => 'piko\user\Module',
      'adminRole' => 'admin',
      'allowUserRegistration' => true
    ],
  ],
  'bootstrap' => ['user'],
]
```

3 - Install module tables. Create a php at the root folder of your project (install.php) and put this code :

```php
require(__DIR__ . '/vendor/autoload.php');

(new \piko\Application(require __DIR__ . '/config.php'));

\piko\user\Module::install();
\piko\user\Module::createUser();
```

4 - Execute install.php on the command line : `php install.php` and follow instructions to create the admin user


## Routes
- **/user/default/login** : Process login
- **/user/default/logout** : Process logout
- **/user/default/register** : Process user registration
- **/user/default/edit** : User account form
- **/user/admin/users** : Manage users, roles, permissions

