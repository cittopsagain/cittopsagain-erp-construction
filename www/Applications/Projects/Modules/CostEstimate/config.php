<?php
return [
    'display_name' => 'Cost Estimate',
    'parent' => 'Operations',
    'iconCls' => 'x-fa fa-calculator',
    'permissions' => [
        'module' => [
            [
                'Cost Estimate' => [
                    ['action' => 'View Cost Estimate'],
                    ['action' => 'Create Cost Estimate'],
                    ['action' => 'Edit Cost Estimate'],
                    ['action' => 'Delete Cost Estimate']
                ]
            ]
        ]
    ]
];
