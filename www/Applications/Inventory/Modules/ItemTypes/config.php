<?php
return [
    'display_name' => 'Item Types',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-th-list',
    'permissions' => [
        'module' => [
            [
                'Item Types' => [
                    ['action' => 'View Item Types'],
                    ['action' => 'Create Item Types'],
                    ['action' => 'Edit Item Types'],
                    ['action' => 'Delete Item Types']
                ]
            ]
        ]
    ]
];
