<?php
return [
    'display_name' => 'Work Phases',
    'parent' => 'Engineering',
    'iconCls' => 'x-fa fa-tasks',
    'permissions' => [
        'module' => [
            [
                'WorkPhases' => [
                    ['action' => 'View Work Phases'],
                    ['action' => 'Create Work Phase'],
                    ['action' => 'Edit Work Phase'],
                    ['action' => 'Delete Work Phase']
                ]
            ]
        ]
    ]
];
