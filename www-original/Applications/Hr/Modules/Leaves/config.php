<?php
return [
    'display_name' => 'Leaves',
    'iconCls' => 'x-fa fa-calendar',
    'permissions' => [
        'module' => [
            [
                'Leaves' => [
                    ['action' => 'View Leaves'],
                    ['action' => 'Apply Leave'],
                    ['action' => 'Approve Leave'],
                    ['action' => 'Reject Leave']
                ]
            ]
        ]
    ]
];

