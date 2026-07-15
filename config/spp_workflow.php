<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SPP Workflow Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the approval workflow for SPP (Surat Permintaan Pembayaran).
    | All workflow logic is centralized here for easy maintenance and modification.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Project Type Mapping
    |--------------------------------------------------------------------------
    |
    | Maps project codes to their respective workflow types.
    | Each workflow type has different approval paths.
    |
    */
    'project_types' => [
        'PROJECT_FLOW' => ['38', '40'],
        'PO_PK_TC_FLOW' => ['01', '03', '07'],
        'BATRA_KLINIK_DIKLAT_FLOW' => ['02', '04', '06'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Initial Position Mapping
    |--------------------------------------------------------------------------
    |
    | Defines the first approver role after MAKER submits SPP.
    | Key: project code, Value: initial role
    |
    */
    'initial_positions' => [
        '38' => 'AREA_MANAGER',
        '40' => 'AREA_MANAGER',
        '01' => 'KOORDINATOR_KEUANGAN',
        '03' => 'KOORDINATOR_PK',
        '07' => 'KOORDINATOR_TC',
        '02' => 'KOORDINATOR_DIKLAT',
        '04' => 'KOORDINATOR_KLINIK',
        '06' => 'KOORDINATOR_BATRA',
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Threshold
    |--------------------------------------------------------------------------
    |
    | SPP amounts exceeding this threshold require DIREKTUR approval.
    | Format: string for bcmath precision
    |
    */
    'director_approval_threshold' => '50000000', // 50 million

    /*
    |--------------------------------------------------------------------------
    | Workflow Transitions - PROJECT_FLOW (Project 38, 40)
    |--------------------------------------------------------------------------
    |
    | Approval path for area-based projects.
    | Format: 'current_role' => ['next_role', 'status']
    |
    */
    'transitions' => [
        'PROJECT_FLOW' => [
            'AREA_MANAGER' => [
                'approve' => ['next' => 'FINANCE_PROJECT', 'status' => 'Pending'],
            ],
            'FINANCE_PROJECT' => [
                'approve' => ['next' => 'PROJECT_MANAGER', 'status' => 'Pending'],
            ],
            'PROJECT_MANAGER' => [
                'approve' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Pending'],
            ],
            'MANAGER_KEUANGAN' => [
                'approve_under_threshold' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
                'approve_over_threshold' => ['next' => 'DIREKTUR', 'status' => 'Pending Director Otorisasi'],
            ],
            'DIREKTUR' => [
                'approve' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Workflow Transitions - PO_PK_TC_FLOW (Project 01, 03, 07)
        |--------------------------------------------------------------------------
        |
        | Approval path for Program Office, PK, and TC projects.
        |
        */
        'PO_PK_TC_FLOW' => [
            'KOORDINATOR_KEUANGAN' => [
                'approve' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Pending'],
            ],
            'KOORDINATOR_PK' => [
                'approve' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Pending'],
            ],
            'KOORDINATOR_TC' => [
                'approve' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Pending'],
            ],
            'MANAGER_KEUANGAN' => [
                'approve_under_threshold' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
                'approve_over_threshold' => ['next' => 'DIREKTUR', 'status' => 'Pending Director Otorisasi'],
            ],
            'DIREKTUR' => [
                'approve' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Workflow Transitions - BATRA_KLINIK_DIKLAT_FLOW (Project 02, 04, 06)
        |--------------------------------------------------------------------------
        |
        | Approval path for Batra, Klinik, and Diklat projects.
        |
        */
        'BATRA_KLINIK_DIKLAT_FLOW' => [
            'KOORDINATOR_BATRA' => [
                'approve' => ['next' => 'MANAGER_PKP', 'status' => 'Pending'],
            ],
            'KOORDINATOR_KLINIK' => [
                'approve' => ['next' => 'MANAGER_PKP', 'status' => 'Pending'],
            ],
            'KOORDINATOR_DIKLAT' => [
                'approve' => ['next' => 'MANAGER_PKP', 'status' => 'Pending'],
            ],
            'KOORDINATOR_BIDANG' => [
                'approve' => ['next' => 'MANAGER_PKP', 'status' => 'Pending'],
            ],
            'MANAGER_PKP' => [
                'approve' => ['next' => 'MANAGER_KEUANGAN', 'status' => 'Pending'],
            ],
            'MANAGER_KEUANGAN' => [
                'approve_under_threshold' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
                'approve_over_threshold' => ['next' => 'DIREKTUR', 'status' => 'Pending Director Otorisasi'],
            ],
            'DIREKTUR' => [
                'approve' => ['next' => 'KASIR_PUSAT', 'status' => 'Approved'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Common Actions (All Workflow Types)
    |--------------------------------------------------------------------------
    |
    | These actions are available in all workflows regardless of type.
    |
    */
    'common_actions' => [
        'revise' => ['next' => 'MAKER', 'status' => 'Revisi'],
        'reject' => ['next' => 'REJECTED', 'status' => 'Rejected'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Fallback
    |--------------------------------------------------------------------------
    |
    | Default initial position if project code not found in mapping.
    |
    */
    'default_initial_position' => 'MANAGER_KEUANGAN',

];
