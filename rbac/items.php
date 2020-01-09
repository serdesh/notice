<?php
return [
    'login' => [
        'type' => 2,
    ],
    'authorization' => [
        'type' => 2,
    ],
    'logout' => [
        'type' => 2,
    ],
    'error' => [
        'type' => 2,
    ],
    'index' => [
        'type' => 2,
    ],
    'view' => [
        'type' => 2,
    ],
    'update' => [
        'type' => 2,
    ],
    'delete' => [
        'type' => 2,
    ],
    'bulk-delete' => [
        'type' => 2,
    ],
    'create' => [
        'type' => 2,
    ],
    'backup' => [
        'type' => 2,
    ],
    'get-spec-by-company' => [
        'type' => 2,
    ],
    'download' => [
        'type' => 2,
    ],
    'import-mkd' => [
        'type' => 2,
    ],
    'import-ls' => [
        'type' => 2,
    ],
    'test' => [
        'type' => 2,
    ],
    'change' => [
        'type' => 2,
    ],
    'profile' => [
        'type' => 2,
    ],
    'menu-position' => [
        'type' => 2,
    ],
    'instructions' => [
        'type' => 2,
    ],

    'guest' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'login',
            'authorization',
        ],
    ],

    'super_administrator' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
//            'guest',
//            'user',
//            'manager',
//            'specialist',
//            'administrator',
//            'login',
//            'authorization',
//            'update',
//            'delete',
//            'bulk-delete',
//            'create',
//            'backup',
            'get-spec-by-company',
            'download',
            'test',
            'instructions',
        ],
    ],
    'super_manager' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
//            'guest',
//            'manager',
//            'specialist',
        ],
    ],
    'administrator' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'guest',
            'user',
            'manager',
            'specialist',
            'login',
            'authorization',
            'update',
            'delete',
            'bulk-delete',
            'create',
            'backup',
            'import-mkd',
            'import-ls',
            'change',
            'profile',
        ],
    ],
    'manager' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'update',
            'guest',
            'login',
            'authorization',
            'logout',
            'error',
            'index',
            'view',
            'create',
            'delete',
        ],
    ],
    'specialist' => [
        'type' => 1,
        'ruleName' => 'userGroup',
        'children' => [
            'update',
            'guest',
            'login',
            'authorization',
            'logout',
            'error',
            'index',
            'view',
            'create',
            'menu-position',
        ],
    ],
];
