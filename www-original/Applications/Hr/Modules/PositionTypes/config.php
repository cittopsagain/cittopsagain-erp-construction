<?php
return [
    'display_name' => 'Position Types',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-tags',
    'permissions' => [
        'module' => [
            [
                'Position Types' => [
                    ['action' => 'View Position Types'],
                    ['action' => 'Create Position Type'],
                    ['action' => 'Edit Position Type'],
                    ['action' => 'Delete Position Type']
                ]
            ]
        ]
    ]
];
