<?php
return [
    'display_name' => 'Units',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-balance-scale',
    'permissions' => [
        'module' => [
            [
                'Units' => [
                    ['action' => 'View Units'],
                    ['action' => 'Create Unit'],
                    ['action' => 'Edit Unit'],
                    ['action' => 'Delete Unit']
                ]
            ]
        ]
    ]
];
