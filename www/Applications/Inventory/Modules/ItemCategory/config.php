<?php
return [
    'display_name' => 'Item Category',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-folder-open',
    'permissions' => [
        'module' => [
            [
                'Item Category' => [
                    ['action' => 'View Item Categories'],
                    ['action' => 'Create Item Category'],
                    ['action' => 'Edit Item Category'],
                    ['action' => 'Delete Item Category']
                ]
            ]
        ]
    ]
];
