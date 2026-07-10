# Piko user module

User management module for [Piko](https://piko-framework.github.io/) based projects.

## Features
- Optional registration
- Registration with an optional confirmation per mail
- Password recovery
- Account and profile management
- User management interface
- Permissions management (RBAC)
- Support for MYSQL ans Sqlite

## Installation

1 - Install module via composer:

```bash
composer require piko/user-module
```

2 - Edit your Piko config :

```php
[
  'components' => [
    // ...
    'Piko\User' => [
        'identityClass' => 'Piko\UserModule\Models\User',
        'checkAccess' => 'Piko\UserModule\AccessChecker::checkAccess'
    ],
  ],
  'modules' => [
    // ...
    'user' => [
      'class' => 'Piko\UserModule',
      'adminRole' => 'admin',
      'allowUserRegistration' => true
    ],
  ],
  'bootstrap' => ['user'],
]
```

3 - Install the module tables and create an admin user.

```bash
export DSN=mysql:host=127.0.0.1;dbname=yourdatabase;charset=utf8mb4
export DB_USERNAME=mysqluser
export DB_PASSWORD=yourpassword

# Install the module tables
./vendor/bin/migrate run -p ./vendor/piko/user-module/migrations
# Create interactively an admin user
./vendor/bin/user-module user:create -i
```

## Routes
- **/user/default/login** : Process login
- **/user/default/logout** : Process logout
- **/user/default/register** : Process user registration
- **/user/default/edit** : User account form
- **/user/admin/users** : Manage users, roles, permissions
