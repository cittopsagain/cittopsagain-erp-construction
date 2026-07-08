<?php
return [
    'display_name' => 'Project Types',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-list',
    'permissions' => [
        'module' => [
            [
                'ProjectTypes' => [
                    ['action' => 'View Project Types'],
                    ['action' => 'Create Project Type'],
                    ['action' => 'Edit Project Type'],
                    ['action' => 'Delete Project Type']
                ]
            ]
        ]
    ]
];
