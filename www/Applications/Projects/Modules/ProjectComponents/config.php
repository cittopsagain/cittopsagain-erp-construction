<?php
return [
    'display_name' => 'Project Components',
    'parent' => 'Masterlist',
    'iconCls' => 'x-fa fa-cubes',
    'permissions' => [
        'module' => [
            [
                'ProjectComponents' => [
                    ['action' => 'View Project Components'],
                    ['action' => 'Create Project Components'],
                    ['action' => 'Edit Project Components'],
                    ['action' => 'Delete Project Components']
                ]
            ]
        ]
    ]
];
