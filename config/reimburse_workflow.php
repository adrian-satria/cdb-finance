<?php

return [
    // Reimburse workflow: finance -> kasir (editable via config per plan).
    'transitions' => [
        'REIMBURSE_FLOW' => [
            'MANAGER_KEUANGAN' => [
                'approve' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
            ],
            'KASIR_PUSAT' => [
                'approve' => ['next' => 'FINISH', 'status' => 'Approved'],
            ],
        ],
    ],
    'common_actions' => [
        'revise' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Revisi'],
        'reject' => ['next' => 'REJECTED', 'status' => 'Rejected'],
    ],
    'default_initial_position' => 'MANAGER_KEUANGAN',
];
