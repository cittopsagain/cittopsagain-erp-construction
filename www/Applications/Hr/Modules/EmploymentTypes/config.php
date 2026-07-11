<?php
return [
    'display_name' => 'Employment Types',
    'parent' => 'Organization',
    'iconCls' => 'x-fa fa-file-text-o',
    'permissions' => [
        'module' => [
            [
                'Employment Types' => [
                    ['action' => 'View Employment Types'],
                    ['action' => 'Create Employment Type'],
                    ['action' => 'Edit Employment Type'],
                    ['action' => 'Delete Employment Type']
                ]
            ]
        ]
    ]
];
