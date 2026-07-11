<?php
return [
    'display_name' => 'Work Schedules',
    'parent' => 'Organization',
    'iconCls' => 'x-fa fa-clock-o',
    'permissions' => [
        'module' => [
            [
                'Work Schedules' => [
                    ['action' => 'View Schedules'],
                    ['action' => 'Create Schedule'],
                    ['action' => 'Edit Schedule'],
                    ['action' => 'Delete Schedule']
                ]
            ]
        ]
    ]
];
