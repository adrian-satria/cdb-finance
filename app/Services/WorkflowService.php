<?php

namespace App\Services;

use Exception;

class WorkflowService
{
    protected array $config;

    public function __construct(string $configKey)
    {
        $this->config = config($configKey);
    }

    public function determineInitialPosition(string $kodeProject): string
    {
        return $this->config['initial_positions'][$kodeProject]
            ?? $this->config['default_initial_position'];
    }

    public function getNextPosition(
        string $currentRole,
        string $kodeProject,
        string $nominal,
        string $action
    ): array {
        if (in_array($action, ['revise', 'reject'], true)) {
            return $this->config['common_actions'][$action];
        }

        $flowType = $this->getWorkflowType($kodeProject);
        $transitions = $this->config['transitions'][$flowType] ?? [];

        if (! isset($transitions[$currentRole])) {
            throw new Exception(
                "WORKFLOW UNMAPPED: Role {$currentRole} tidak punya transisi {$action} pada flow {$flowType}."
            );
        }

        $roleTransitions = $transitions[$currentRole];

        if ($action === 'approve') {
            if ($currentRole === 'MANAGER_KEUANGAN' && isset($this->config['director_approval_threshold'])) {
                $threshold = $this->config['director_approval_threshold'];
                $isOverThreshold = bccomp($nominal, $threshold, 2) === 1;

                if ($isOverThreshold && isset($roleTransitions['approve_over_threshold'])) {
                    return $roleTransitions['approve_over_threshold'];
                } elseif (! $isOverThreshold && isset($roleTransitions['approve_under_threshold'])) {
                    return $roleTransitions['approve_under_threshold'];
                }
            }

            if (isset($roleTransitions['approve'])) {
                return $roleTransitions['approve'];
            }
        }

        throw new Exception(
            "WORKFLOW UNMAPPED: Role {$currentRole} tidak punya transisi {$action} pada flow {$flowType}."
        );
    }

    public function validateWorkflowTransition(object $doc, string $currentRole): bool
    {
        if ($currentRole === 'ADMIN') {
            return false;
        }

        return $doc->posisi_saat_ini === $currentRole;
    }

    protected function getWorkflowType(string $kodeProject): string
    {
        $transitions = $this->config['transitions'] ?? [];

        if (empty($this->config['project_types'])) {
            return array_key_first($transitions) ?? 'PROJECT_FLOW';
        }

        foreach ($this->config['project_types'] as $flowType => $projectCodes) {
            if (in_array($kodeProject, $projectCodes, true)) {
                return $flowType;
            }
        }

        return array_key_first($transitions) ?? 'PROJECT_FLOW';
    }

    public function getWorkflowRoles(string $kodeProject): array
    {
        $flowType = $this->getWorkflowType($kodeProject);
        $transitions = $this->config['transitions'][$flowType] ?? [];

        return array_keys($transitions);
    }

    public function getApprovalThreshold(): string
    {
        return $this->config['director_approval_threshold'] ?? '50000000';
    }

    public function requiresDirectorApproval(string $nominal): bool
    {
        $threshold = $this->getApprovalThreshold();

        return bccomp($nominal, $threshold, 2) === 1;
    }
}
