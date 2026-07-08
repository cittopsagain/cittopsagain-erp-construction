<?php
return [
    'display_name' => 'Quotations',
    'parent' => 'Quotations',
    'iconCls' => 'x-fa fa-file-text-o',
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
