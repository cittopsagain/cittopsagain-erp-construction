<?php
return [
    'display_name' => 'Quotations',
    'parent' => 'Operations',
    'iconCls' => 'x-fa fa-list',
    'permissions' => [
        'module' => [
            [
                'Quotations' => [
                    ['action' => 'View Quotations'],
                    ['action' => 'Create Quotation'],
                    ['action' => 'Edit Quotation'],
                    ['action' => 'Delete Quotation']
                ]
            ]
        ]
    ]
];
