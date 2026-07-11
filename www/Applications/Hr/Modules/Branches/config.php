<?php
return [
    'display_name' => 'Branches',
    'parent' => 'Organization',
    'iconCls' => 'x-fa fa-building',
    'permissions' => [
        'module' => [
            [
                'Branches' => [
                    ['action' => 'View Branches'],
                    ['action' => 'Create Branch'],
                    ['action' => 'Edit Branch'],
                    ['action' => 'Delete Branch']
                ]
            ]
        ]
    ]
];
