<?php
return [
    'display_name' => 'Material Groups',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-tags',
    'permissions' => [
        'module' => [
            [
                'Material Groups' => [
                    ['action' => 'View Material Groups'],
                    ['action' => 'Create Material Group'],
                    ['action' => 'Edit Material Group'],
                    ['action' => 'Delete Material Group']
                ]
            ]
        ]
    ]
];
