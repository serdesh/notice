<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => '',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'ru-RU',
    'name' => 'АДС АСУС МКД',
    'defaultRoute' => 'petition',
    'timeZone' => 'Europe/Moscow',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ],
        'drive' => [
            'class' => 'app\modules\drive\Module',
            'defaultRoute' => 'google'
        ],
        'address' => [
            'class' => 'app\modules\fias\Module',
            'defaultRoute' => 'address'

        ],
        'api' => [
            'class' => 'app\modules\api\Module',
//            'defaultRoute' => 'v1'
        ],
    ],
    'components' => [
        'backup' => [
            'class' => 'demi\backup\Component',
            // The directory for storing backups files
            'backupsFolder' => dirname(__DIR__) . '/backups', // <project-root>/backups
            // Directories that will be added to backup
            'directories' => [
                'files' => '@webroot/files',
                'images' => '@webroot/images',
            ],
        ],
        'dadata' => [
            'class' => '\gietos\yii\Dadata\Client',
            'token' => 'c2ae0bae1eaeb49d2994e4bc31be54543b871251',
            'secret' => '4a7e8448a6dc065bb54163b43c9b6e6786963b67',
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['super_administrator', 'super_manager', 'administrator', 'manager', 'specialist'], // Здесь нет роли "guest", т.к. эта роль виртуальная и не присутствует в модели UserExt
        ],
        'request' => [
            'baseUrl'=> '',
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'GqAe_ZdasdasdFrO8APgSUHtAJRYYfvew5nfoBb',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => '/site/login'
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['test'],
                    'logFile' => '@app/runtime/logs/test.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['request'],
                    'logFile' => '@app/runtime/logs/request.log',
//                    'logFile' => '@app/runtime/logs/request_'.date('Y-m-d h:s:i').'.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['_error'],
                    'logFile' => '@app/runtime/logs/_error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'categories' => ['file'],
                    'logFile' => '@app/runtime/logs/file.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'datetimeFormat' => 'dd.MM.yyyy H:mm',
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
