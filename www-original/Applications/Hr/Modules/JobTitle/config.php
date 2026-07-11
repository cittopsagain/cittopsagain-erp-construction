<?php
return [
    'display_name' => 'Job Title',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-briefcase',
    'permissions' => [
        'module' => [
            [
                'Job Title' => [
                    ['action' => 'View Job Title'],
                    ['action' => 'Create Job Title'],
                    ['action' => 'Edit Job Title'],
                    ['action' => 'Delete Job Title']
                ]
            ]
        ]
    ]
];
