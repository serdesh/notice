<?php

use app\models\Users;

//$permission = Yii::$app->user->identity->permission;
//$id = Yii::$app->user->identity->id;
?>
<aside class="main-sidebar">

    <section class="sidebar">

        <?php
        //
        //        if (Users::isManager()) {
        //            $items = [
        //                ['label' => 'Menu', 'options' => ['class' => 'header']],
        //                ['label' => 'Контрагенты', 'icon' => 'cubes', 'url' => ['/contractor']],
        //                ['label' => 'Заявки', 'icon' => 'files-o', 'url' => ['/request']],
        //                ['label' => 'События', 'icon' => 'history', 'url' => ['/actions']],
        //                [
        //                    'label' => 'Номенклатура',
        //                    'icon' => 'book',
        //                    'url' => '#',
        //                    'items' => [
        //                        ['label' => 'Для менеджера', 'icon' => 'user', 'url' => ['/nomenclature/manager-nomenclature']],
        //                    ],
        //                ],
        //                ['label' => 'Детали по заявкам', 'icon' => 'cogs', 'url' => ['/order-part/request-details']],
        //            ];
        //        } elseif (Users::isTech()) {
        //            $items = [
        //                ['label' => 'Menu', 'options' => ['class' => 'header']],
        //                ['label' => 'Заявки', 'icon' => 'files-o', 'url' => ['/request']],
        //                ['label' => 'События', 'icon' => 'history', 'url' => ['/actions']],
        //                [
        //                    'label' => 'Номенклатура',
        //                    'icon' => 'book',
        //                    'url' => '#',
        //                    'items' => [
        //                        ['label' => 'Для специалиста', 'icon' => 'user', 'url' => ['/nomenclature/tech-nomenclature']],
        //                    ],
        //                ],
        //                ['label' => 'Детали по заявкам', 'icon' => 'cogs', 'url' => ['/order-part/request-details']],
        //            ];
        //        } elseif (Users::isAdmin() || Users::isUser()) {
        //            $items = [
        //                ['label' => 'Menu', 'options' => ['class' => 'header']],
        //                ['label' => 'Контрагенты', 'icon' => 'cubes', 'url' => ['/contractor']],
        //                ['label' => 'Заявки', 'icon' => 'files-o', 'url' => ['/request']],
        //                ['label' => 'События', 'icon' => 'history', 'url' => ['/actions']],
        //                [
        //                    'label' => 'Номенклатура',
        //                    'icon' => 'book',
        //                    'url' => '#',
        //                    'items' => [
        //                        ['label' => 'Для менеджера', 'icon' => 'user', 'url' => ['/nomenclature/manager-nomenclature']],
        //                        ['label' => 'Для специалиста', 'icon' => 'user', 'url' => ['/nomenclature/tech-nomenclature']],
        //                    ],
        //                ],
        //                [
        //                    'label' => 'Справочники',
        //                    'icon' => 'list-alt',
        //                    'url' => '#',
        //                    'items' => [
        //                        ['label' => 'Сотрудники', 'icon' => 'user', 'url' => ['/users']],
        //                        ['label' => 'Поставщики', 'icon' => 'bus', 'url' => ['/suppliers']],
        //                        ['label' => 'Города', 'icon' => 'building', 'url' => ['/cities']],
        //                        ['label' => 'Группы номенклатур', 'icon' => 'id-card', 'url' => ['/nomenclature-group']],
        //                        ['label' => 'Бренды', 'icon' => 'navicon', 'url' => ['/brand']],
        //                        ['label' => 'Статусы заказов', 'icon' => 'spinner', 'url' => ['/order-status']],
        //                        ['label' => 'Статус Доп Заказы', 'icon' => 'circle-o-notch', 'url' => ['/additional-order-status']],
        //                        ['label' => 'Срок поставки', 'icon' => 'calendar-o', 'url' => ['/delivery-time']],
        //                        ['label' => 'Валюта', 'icon' => 'dollar', 'url' => ['/currency']],
        //                        ['label' => 'Тип механизма', 'icon' => 'gear', 'url' => ['/type-of-mechanism']],
        //                        ['label' => 'Тип запчасти', 'icon' => 'gear', 'url' => ['/type-of-parts']],
        //                        ['label' => 'Единица Измерения', 'icon' => 'gear', 'url' => ['/measure']],
        //                    ],
        //                ],
        //                ['label' => 'Детали по заявкам', 'icon' => 'cogs', 'url' => ['/order-part/request-details']],
        //            ];
        //        }
        if (Users::isSuperAdmin()) {
            $items = [
                ['label' => 'Меню', 'options' => ['class' => 'header']],
                ['label' => 'Компании', 'icon' => 'industry', 'url' => ['/company']],
                ['label' => 'Отчеты', 'icon' => 'tasks', 'url' => ['/petition/report']],
                ['label' => 'Сотрудники', 'icon' => 'user', 'url' => ['/users']],
                [
                    'label' => 'Настройки',
                    'icon' => 'gear',
                    'items' => [
                        ['label' => 'Общие настройки', 'icon' => 'gear', 'url' => ['/settings']],
                        ['label' => 'Инструкции', 'icon' => 'file', 'url' => ['/site/instructions']],
                        ['label' => 'Неисправности', 'icon' => 'hourglass-2', 'url' => ['/trouble']],
                        ['label' => 'Скачать ошибки', 'icon' => 'download', 'url' => ['site/download']],
//                        [
//                            'label' => 'Импорт',
//                            'icon' => 'sign-in',
//                            'items' => [
//                                ['label' => 'Сведений о МКД', 'icon' => 'sign-in', 'url' => ['site/import-mkd']],
//                                ['label' => 'Сведений о ЛС', 'icon' => 'sign-in', 'url' => ['site/import-ls']],
//                            ]
//                        ],
                    ]
                ],
                ['label' => 'Выход', 'icon' => 'sign-out', 'url' => ['/site/logout'], 'template' => '<a href="{url}" data-method="post"><i class="fa fa-sign-out"></i> {label}</a>']

            ];
        } elseif (Users::isSuperManager()) {
            $items = [
                ['label' => 'Меню', 'options' => ['class' => 'header']],
                ['label' => 'Компании', 'icon' => 'industry', 'url' => ['/company']],
                ['label' => 'Жильцы', 'icon' => 'child', 'url' => ['/resident']],
                ['label' => 'Выход', 'icon' => 'sign-out', 'url' => ['/site/logout'], 'template' => '<a href="{url}" data-method="post"><i class="fa fa-sign-out"></i> {label}</a>']
            ];

        } elseif (Users::isAdmin()) {
            $items = [
                ['label' => 'Меню', 'options' => ['class' => 'header']],
                ['label' => 'Главная', 'icon' => 'bullhorn', 'url' => ['/petition']],
                ['label' => 'Письма', 'icon' => 'envelope', 'url' => ['/message']],
                ['label' => 'Звонки', 'icon' => 'phone', 'url' => ['/call']],
                ['label' => 'Жалобы', 'icon' => 'exclamation', 'url' => ['/petition/complaint']],
                ['label' => 'Архив обращений', 'icon' => 'file-archive-o', 'url' => ['/petition/archive']],
                [
                    'label' => 'Базы данных',
                    'icon' => 'database',
                    'items' => [
                        ['label' => 'Сотрудники', 'icon' => 'user', 'url' => ['/users']],
                        ['label' => 'Дома', 'icon' => 'home', 'url' => ['/house']],
                        ['label' => 'Помещения', 'icon' => 'map-marker', 'url' => ['/apartment']],
                        ['label' => 'Документы', 'icon' => 'folder', 'url' => ['/document']],
                        ['label' => 'Жильцы', 'icon' => 'child', 'url' => ['/resident']],
                    ]
                ],
                ['label' => 'Отчеты', 'icon' => 'tasks', 'url' => ['/petition/report']],

                [
                    'label' => 'Настройки',
                    'icon' => 'gear',
                    'items' => [
                        ['label' => 'Общие настройки', 'icon' => 'gear', 'url' => ['/settings']],
                        [
                            'label' => 'Импорт',
                            'icon' => 'sign-in',
                            'items' => [
                                ['label' => 'Сведений о МКД', 'icon' => 'sign-in', 'url' => ['site/import-mkd']],
                                ['label' => 'Сведений о ЛС', 'icon' => 'sign-in', 'url' => ['site/import-ls']],
                            ]
                        ],
                    ]
                ],
                ['label' => 'Выход', 'icon' => 'sign-out', 'url' => ['/site/logout'], 'template' => '<a href="{url}" data-method="post"><i class="fa fa-sign-out"></i> {label}</a>']
            ];
        } elseif (Users::isManager()) {
            $items = [
                ['label' => 'Меню', 'options' => ['class' => 'header']],
                ['label' => 'Обращения', 'icon' => 'bullhorn', 'url' => ['/petition']],
                ['label' => 'Письма', 'icon' => 'envelope', 'url' => ['/message']],
                ['label' => 'Звонки', 'icon' => 'phone', 'url' => ['/call']],
                ['label' => 'Жильцы', 'icon' => 'child', 'url' => ['/resident']],
                ['label' => 'Выход', 'icon' => 'sign-out', 'url' => ['/site/logout'], 'template' => '<a href="{url}" data-method="post"><i class="fa fa-sign-out"></i> {label}</a>']
            ];
        } elseif (Users::isSpecialist()) {
            $items = [
                ['label' => 'Меню', 'options' => ['class' => 'header']],
                ['label' => 'Главная', 'icon' => 'bullhorn', 'url' => ['/petition']],
                ['label' => 'Выход', 'icon' => 'sign-out', 'url' => ['/site/logout'], 'template' => '<a href="{url}" data-method="post"><i class="fa fa-sign-out"></i> {label}</a>']
            ];
        }

        if (isset(Yii::$app->user->identity->id)) {
            try {
                echo dmstr\widgets\Menu::widget(
                    [
                        'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                        'items' => $items,
                    ]
                );
            } catch (Exception $e) {
                Yii::error($e->getTraceAsString(), '_error');
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        ?>

    </section>

</aside>
