<?php

return [
    'display_name' => 'Permissions',
    'parent' => 'Security',
    'iconCls' => 'x-fa fa-key',
    'permissions' => [
        'module' => [
            [
                'Permissions' => [
                    ['action' => 'View Permissions'],
                    ['action' => 'Create Permission'],
                    ['action' => 'Edit Permission'],
                    ['action' => 'Delete Permission']
                ]
            ]
        ]
    ]
];
