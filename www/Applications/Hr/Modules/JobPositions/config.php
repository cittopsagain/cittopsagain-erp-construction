<?php
return [
    'display_name' => 'Job Positions',
    'parent' => 'Organization',
    'iconCls' => 'x-fa fa-briefcase',
    'permissions' => [
        'module' => [
            [
                'Job Positions' => [
                    ['action' => 'View Job Positions'],
                    ['action' => 'Create Job Position'],
                    ['action' => 'Edit Job Position'],
                    ['action' => 'Delete Job Position']
                ]
            ]
        ]
    ]
];
