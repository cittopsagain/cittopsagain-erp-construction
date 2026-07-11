<?php
return [
    'display_name' => 'Services',
    'parent' => 'Engineering',
    'iconCls' => 'x-fa fa-list',
    'permissions' => [
        'module' => [
            [
                'Services' => [
                    ['action' => 'View Services'],
                    ['action' => 'Create Service'],
                    ['action' => 'Edit Service'],
                    ['action' => 'Delete Service']
                ]
            ]
        ]
    ]
];
