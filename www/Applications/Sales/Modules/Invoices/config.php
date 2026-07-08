<?php
return [
    'display_name' => 'Invoices',
    'iconCls' => 'x-fa fa-file-text-o',
    'permissions' => [
        'module' => [
            [
                'Invoices' => [
                    ['action' => 'View Invoices'],
                    ['action' => 'Create Invoice'],
                    ['action' => 'Edit Invoice'],
                    ['action' => 'Cancel Invoice']
                ]
            ]
        ]
    ]
];
