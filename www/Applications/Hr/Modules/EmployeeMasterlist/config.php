<?php
return [
    'display_name' => 'Employee Masterlist',
    'parent' => 'Employee Management',
    'iconCls' => 'x-fa fa-users',
    'permissions' => [
        'module' => [
            [
                'Employee Masterlist' => [
                    ['action' => 'View Employee Masterlist'],
                    ['action' => 'Create Employee Masterlist'],
                    ['action' => 'Edit Employee Masterlist'],
                    ['action' => 'Delete Employee Masterlist']
                ]
            ]
        ]
    ]
];
