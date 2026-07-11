<?php
return [
    'display_name' => 'Salary Grades',
    'parent' => 'Organization',
    'iconCls' => 'x-fa fa-money',
    'permissions' => [
        'module' => [
            [
                'Salary Grades' => [
                    ['action' => 'View Salary Grades'],
                    ['action' => 'Create Salary Grade'],
                    ['action' => 'Edit Salary Grade'],
                    ['action' => 'Delete Salary Grade']
                ]
            ]
        ]
    ]
];
