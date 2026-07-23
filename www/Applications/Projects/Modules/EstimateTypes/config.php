<?php
return [
    'display_name' => 'Estimate Types',
    'parent' => 'Engineering',
    'iconCls' => 'x-fa fa-list-alt',
    'permissions' => [
        'module' => [
            [
                'EstimateTypes' => [
                    ['action' => 'View Estimate Types'],
                    ['action' => 'Create Estimate Type'],
                    ['action' => 'Edit Estimate Type'],
                    ['action' => 'Delete Estimate Type']
                ]
            ]
        ]
    ]
];
