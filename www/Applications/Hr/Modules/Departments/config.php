<?php
return [
    'display_name' => 'Departments',
    'parent' => 'Organization',
    'iconCls' => 'x-fa fa-sitemap',
    'permissions' => [
        'module' => [
            [
                'Departments' => [
                    ['action' => 'View Departments'],
                    ['action' => 'Create Department'],
                    ['action' => 'Edit Department'],
                    ['action' => 'Delete Department']
                ]
            ]
        ]
    ]
];
