<?php
return [
    'display_name' => 'Items',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-cube',
    'permissions' => [
        'module' => [
            [
                'Items' => [
                    ['action' => 'View Items'],
                    ['action' => 'Create Item'],
                    ['action' => 'Edit Item'],
                    ['action' => 'Delete Item']
                ]
            ]
        ]
    ]
];
