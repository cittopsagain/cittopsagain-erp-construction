<?php
return [
    'display_name' => 'Holiday Calendar',
    'parent' => 'Organization',
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
