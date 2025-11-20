<?php

use Piko\ModularApplication;
use Piko\I18n;

require realpath(__DIR__ . '/../../vendor/autoload.php');

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
                'sqlite:' . __DIR__ . '/../runtime/app.sqlite'
            ]
        ],
        'Nette\Mail\Mailer' => [
            'class' => '\Nette\Mail\SendmailMailer'
        ]
    ],
    'modules' => [
        'user' => 'Piko\UserModule',
    ],
    'bootstrap' => [
        'user'
    ]
];

$app = new ModularApplication($config);

$i18n = $app->getComponent('Piko\I18n');
I18n::setInstance($i18n);

$app->run();
