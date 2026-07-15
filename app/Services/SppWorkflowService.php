<?php

namespace App\Services;

use Exception;

class SppWorkflowService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('spp_workflow');
    }

    /**
     * Determine the initial approval position based on project code.
     *
     * @param string $kodeProject
     * @return string Initial role/position
     */
    public function determineInitialPosition(string $kodeProject): string
    {
        return $this->config['initial_positions'][$kodeProject] 
            ?? $this->config['default_initial_position'];
    }

    /**
     * Get the next position in workflow based on current role, project, nominal, and action.
     *
     * @param string $currentRole Current approver role
     * @param string $kodeProject Project code
     * @param string $nominal SPP nominal amount (string for bcmath precision)
     * @param string $action Action taken: 'approve', 'revise', 'reject'
     * @return array ['next' => next_role, 'status' => new_status]
     * @throws Exception if workflow unmapped
     */
    public function getNextPosition(
        string $currentRole,
        string $kodeProject,
        string $nominal,
        string $action
    ): array {
        // Handle common actions (revise, reject)
        if (in_array($action, ['revise', 'reject'], true)) {
            return $this->config['common_actions'][$action];
        }

        // Get workflow type for this project
        $flowType = $this->getWorkflowType($kodeProject);

        // Get transitions for this workflow type
        $transitions = $this->config['transitions'][$flowType] ?? [];

        // Check if role exists in transitions
        if (!isset($transitions[$currentRole])) {
            throw new Exception(
                "WORKFLOW UNMAPPED: Role {$currentRole} tidak punya transisi {$action} pada flow {$flowType}."
            );
        }

        $roleTransitions = $transitions[$currentRole];

        // For approve action, check if threshold matters
        if ($action === 'approve') {
            // Special case: MANAGER_KEUANGAN checks nominal threshold
            if ($currentRole === 'MANAGER_KEUANGAN') {
                $threshold = $this->config['director_approval_threshold'];
                $isOverThreshold = bccomp($nominal, $threshold, 2) === 1;

                if ($isOverThreshold && isset($roleTransitions['approve_over_threshold'])) {
                    return $roleTransitions['approve_over_threshold'];
                } elseif (!$isOverThreshold && isset($roleTransitions['approve_under_threshold'])) {
                    return $roleTransitions['approve_under_threshold'];
                }
            }

            // For other roles, use simple approve
            if (isset($roleTransitions['approve'])) {
                return $roleTransitions['approve'];
            }
        }

        // If no matching transition found
        throw new Exception(
            "WORKFLOW UNMAPPED: Role {$currentRole} tidak punya transisi {$action} pada flow {$flowType}."
        );
    }

    /**
     * Validate if workflow transition is allowed.
     *
     * @param object $surat SPP object with posisi_saat_ini property
     * @param string $currentRole Role attempting to approve
     * @return bool True if valid, false otherwise
     */
    public function validateWorkflowTransition(object $surat, string $currentRole): bool
    {
        // Admin can act on behalf of any role
        if ($currentRole === 'ADMIN') {
            return true;
        }

        // Check if current role matches SPP's current position
        return $surat->posisi_saat_ini === $currentRole;
    }

    /**
     * Get workflow type based on project code.
     *
     * @param string $kodeProject
     * @return string Workflow type name
     */
    protected function getWorkflowType(string $kodeProject): string
    {
        foreach ($this->config['project_types'] as $flowType => $projectCodes) {
            if (in_array($kodeProject, $projectCodes, true)) {
                return $flowType;
            }
        }

        // Default to PROJECT_FLOW if not found
        return 'PROJECT_FLOW';
    }

    /**
     * Get all possible roles for a workflow type.
     *
     * @param string $kodeProject
     * @return array List of roles in this workflow
     */
    public function getWorkflowRoles(string $kodeProject): array
    {
        $flowType = $this->getWorkflowType($kodeProject);
        $transitions = $this->config['transitions'][$flowType] ?? [];

        return array_keys($transitions);
    }

    /**
     * Check if a role is part of the workflow for given project.
     *
     * @param string $role
     * @param string $kodeProject
     * @return bool
     */
    public function isRoleInWorkflow(string $role, string $kodeProject): bool
    {
        $workflowRoles = $this->getWorkflowRoles($kodeProject);
        return in_array($role, $workflowRoles, true);
    }

    /**
     * Get the approval threshold amount.
     *
     * @return string Threshold amount as string
     */
    public function getApprovalThreshold(): string
    {
        return $this->config['director_approval_threshold'];
    }

    /**
     * Check if nominal requires director approval.
     *
     * @param string $nominal
     * @return bool
     */
    public function requiresDirectorApproval(string $nominal): bool
    {
        $threshold = $this->config['director_approval_threshold'];
        return bccomp($nominal, $threshold, 2) === 1;
    }
}
