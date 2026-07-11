<?php
return [
    'display_name' => 'Holiday Calendar',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-calendar',
    'permissions' => [
        'module' => [
            [
                'Holiday Calendar' => [
                    ['action' => 'View Holidays'],
                    ['action' => 'Create Holiday'],
                    ['action' => 'Edit Holiday'],
                    ['action' => 'Delete Holiday']
                ]
            ]
        ]
    ]
];
