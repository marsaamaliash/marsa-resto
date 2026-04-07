<?php

return [
    'roles' => [
        'President Director' => ['all'],
        'Director' => ['finance.view', 'hr.view'],
        'Manager' => ['finance.input'],
        'Staff' => ['finance.input.own'],
    ],
    'scopes' => [
        'holding' => true,
        'department' => true,
        'division' => true,
    ],
];
