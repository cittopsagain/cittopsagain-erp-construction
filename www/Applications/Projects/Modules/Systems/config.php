<?php
return [
    'display_name' => 'Systems',
    'parent' => 'Engineering',
    'iconCls' => 'x-fa fa-cogs',
    'permissions' => [
        'module' => [
            [
                'Systems' => [
                    ['action' => 'View Systems'],
                    ['action' => 'Create System'],
                    ['action' => 'Edit System'],
                    ['action' => 'Delete System']
                ]
            ]
        ]
    ]
];
