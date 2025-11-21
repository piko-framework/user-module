<?php

use Piko\ModularApplication;
use Piko\I18n;

require realpath(__DIR__ . '/../../vendor/autoload.php');

$envFile = realpath(__DIR__ . '/../../env.php');

if ($envFile) {
    foreach (require $envFile as $key => $val) {
        putenv("{$key}={$val}");
    }
}

$config = [
    'basePath' => realpath(__DIR__),
    'defaultLayoutPath' => '@app/layouts',
    'defaultLayout' => 'main',

    'components' => [
        'Piko\View' => [],
        'Piko\Router' =>  [
            'construct' => [
                [
                    'routes' => ['/' => 'user/default/login'],
                ]
            ]
        ],
        'Piko\I18n' => [
            'language' => 'en'
        ],
        'Piko\User' => [
            'identityClass' => 'Piko\UserModule\Models\User',
            'checkAccess' => 'Piko\UserModule\AccessChecker::checkAccess',
        ],
        'PDO' => [
            'construct' => [
                getenv('DSN'),
                getenv('DB_USERNAME') ? getenv('DB_USERNAME') : null,
                getenv('DB_PASSWORD')  ? getenv('DB_PASSWORD') : null,
            ]
        ],
        'Nette\Mail\Mailer' => [
            'class' => '\Nette\Mail\SendmailMailer'
        ]
    ],
    'modules' => [
        'user' => [
            'class' => 'Piko\UserModule',
            'redirectUrlAfterLogin' => '/user/default/edit'
        ],
    ],
    'bootstrap' => [
        'user'
    ]
];

$app = new ModularApplication($config);

$i18n = $app->getComponent('Piko\I18n');
I18n::setInstance($i18n);

/**@var \Piko\View $view */
$view = $app->getComponent('Piko\View');

// Inject the user component in the view
$view->params['user'] = $app->getComponent('Piko\User');

$app->run();
