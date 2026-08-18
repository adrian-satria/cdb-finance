<?php

return [
    // LPJ workflow: maker -> finance -> kasir (no director, editable per plan).
    'transitions' => [
        'LPJ_FLOW' => [
            'MAKER' => [
                'approve' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Pending'],
            ],
            'MANAGER_KEUANGAN' => [
                'approve' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
            ],
            'KASIR_PUSAT' => [
                'approve' => ['next' => 'FINISH', 'status' => 'Approved'],
            ],
        ],
    ],
    'common_actions' => [
        'revise' => ['next' => 'MAKER', 'status' => 'Revisi'],
        'reject' => ['next' => 'REJECTED', 'status' => 'Rejected'],
    ],
    'default_initial_position' => 'MANAGER_KEUANGAN',
];
