<?php

return [
    // UM workflow reuses SPP approval paths (per project, nominal-based threshold).
    'transitions' => config('spp_workflow.transitions', []),
    'project_types' => config('spp_workflow.project_types', []),
    'initial_positions' => config('spp_workflow.initial_positions', []),
    'director_approval_threshold' => config('spp_workflow.director_approval_threshold', '50000000'),
    'no_budget_projects' => config('spp_workflow.no_budget_projects', []),
    'common_actions' => [
        'revise' => ['next' => 'MAKER', 'status' => 'Revisi'],
        'reject' => ['next' => 'REJECTED', 'status' => 'Rejected'],
    ],
    'default_initial_position' => 'MANAGER_KEUANGAN',
    'lpj_deadline_days' => 14,
    'status_cair' => 'Cair',
];
