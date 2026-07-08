<?php
return [
    'display_name' => 'Employee Management',
    'iconCls' => 'x-fa fa-user',
    'permissions' => [
        'module' => [
            [
                'Employees' => [
                    ['action' => 'View Employee'],
                    ['action' => 'Create Employee'],
                    ['action' => 'Edit Employee'],
                    ['action' => 'Delete Employee']
                ]
            ]
        ]
    ]
];
