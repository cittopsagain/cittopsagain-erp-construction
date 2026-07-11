<?php
return [
    'display_name' => 'Installation Methods',
    'parent' => 'Engineering',
    'iconCls' => 'x-fa fa-list',
    'permissions' => [
        'module' => [
            [
                'InstallationMethods' => [
                    ['action' => 'View Installation Methods'],
                    ['action' => 'Create Installation Method'],
                    ['action' => 'Edit Installation Method'],
                    ['action' => 'Delete Installation Method']
                ]
            ]
        ]
    ]
];
