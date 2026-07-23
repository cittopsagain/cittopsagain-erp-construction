<?php
return [
    'display_name' => 'BOQ',
    'parent' => 'Operations',
    'iconCls' => 'x-fa fa-money',
    'permissions' => [
        'module' => [
            [
                'BOQ' => [
                    ['action' => 'View BOQ'],
                    ['action' => 'Create BOQ'],
                    ['action' => 'Edit BOQ'],
                    ['action' => 'Delete BOQ']
                ]
            ]
        ]
    ]
];
