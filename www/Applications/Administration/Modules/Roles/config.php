<?php

return [
    'display_name' => 'Roles',
    'parent' => 'Security',
    'iconCls' => 'x-fa fa-user-secret',
    'permissions' => [
        'module' => [
            [
                'Roles' => [
                    ['action' => 'View Roles'],
                    ['action' => 'Create Role'],
                    ['action' => 'Edit Role'],
                    ['action' => 'Delete Role']
                ]
            ]
        ]
    ]
];
