<?php
return [
    'display_name' => 'Composition Templates',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-file-text-o',
    'permissions' => [
        'module' => [
            [
                'Composition Templates' => [
                    ['action' => 'View Composition Templates'],
                    ['action' => 'Create Composition Template'],
                    ['action' => 'Edit Composition Template'],
                    ['action' => 'Delete Composition Template']
                ]
            ]
        ]
    ]
];
