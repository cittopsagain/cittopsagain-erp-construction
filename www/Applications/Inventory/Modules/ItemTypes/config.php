<?php
return [
    'display_name' => 'Item Type',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-th-list',
    'permissions' => [
        'module' => [
            [
                'Item Type' => [
                    ['action' => 'View Item Types'],
                    ['action' => 'Create Item Type'],
                    ['action' => 'Edit Item Type'],
                    ['action' => 'Delete Item Type']
                ]
            ]
        ]
    ]
];
