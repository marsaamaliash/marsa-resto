<?php

return [
    'modules' => [
        'inventaris' => [
            'module_code' => '01005',
            'permission_delete_request' => 'INV_DELETE',
            'permission_delete_approve' => 'INV_DELETE_APPROVE', // kalau belum ada, action akan fallback
        ],

        'sso' => [
            'module_code' => '00000',
            'permission_user_deactivate_request' => 'SSO_USER_DEACTIVATE',
            'permission_user_deactivate_approve' => 'SSO_USER_DEACTIVATE_APPROVE',
            'permission_approval_inbox_view' => 'APPROVAL_VIEW',
            'permission_approval_approve' => 'APPROVAL_APPROVE',
        ],
    ],
];
