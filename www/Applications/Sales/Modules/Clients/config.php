<?php
return [
    'display_name' => 'Clients',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-briefcase',
    'permissions' => [
        'module' => [
            [
                'Clients' => [
                    ['action' => 'View Clients'],
                    ['action' => 'Create Client'],
                    ['action' => 'Edit Client'],
                    ['action' => 'Delete Client']
                ]
            ]
        ]
    ]
];
