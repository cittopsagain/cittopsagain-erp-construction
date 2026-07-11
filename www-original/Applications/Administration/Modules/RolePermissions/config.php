<?php

return [
    'display_name' => 'Role Permissions',
    'parent' => 'Security',
    'iconCls' => 'x-fa fa-shield',
    'permissions' => [
        'module' => [
            [
                'Role Permissions' => [
                    ['action' => 'View Role Permissions'],
                    ['action' => 'Assign Role Permissions'],
                    ['action' => 'Revoke Role Permissions']
                ]
            ]
        ]
    ]
];
