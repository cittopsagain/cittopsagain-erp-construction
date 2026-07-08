<?php
return [
    'display_name' => 'Trades',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-briefcase',
    'permissions' => [
        'module' => [
            [
                'Trades' => [
                    ['action' => 'View Trades'],
                    ['action' => 'Create Trade'],
                    ['action' => 'Edit Trade'],
                    ['action' => 'Delete Trade']
                ]
            ]
        ]
    ]
];
