<?php

namespace App\Traits;

trait HasRolePermissions
{
    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is admin (any admin role)
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin', 'educational-director', 'department-head']);
    }

    /**
     * Check if user is facilitator
     */
    public function isFacilitator(): bool
    {
        return $this->hasRole('facilitator') || $this->isAdmin();
    }

    /**
     * Check if user is content creator
     */
    public function isContentCreator(): bool
    {
        return $this->hasRole('content-creator') || $this->isFacilitator();
    }

    /**
     * Check if user is participant
     */
    public function isParticipant(): bool
    {
        return $this->hasRole('participant');
    }

    /**
     * Check if user can manage workshops
     */
    public function canManageWorkshops(): bool
    {
        return $this->can('create-workshops') || $this->can('edit-workshops');
    }

    /**
     * Check if user can manage assignments
     */
    public function canManageAssignments(): bool
    {
        return $this->can('create-assignments') || $this->can('edit-assignments');
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->can('create-users') || $this->can('edit-users') || $this->can('delete-users');
    }

    /**
     * Check if user can view analytics
     */
    public function canViewAnalytics(): bool
    {
        return $this->can('view-dashboard-analytics') || $this->can('view-workshop-analytics');
    }

    /**
     * Check if user can manage system settings
     */
    public function canManageSystem(): bool
    {
        return $this->can('manage-system-settings') || $this->isSuperAdmin();
    }

    /**
     * Get user's primary role name
     */
    public function getPrimaryRole(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }
        
        if ($this->hasRole('admin')) {
            return 'Admin';
        }
        
        if ($this->hasRole('educational-director')) {
            return 'Educational Director';
        }
        
        if ($this->hasRole('department-head')) {
            return 'Department Head';
        }
        
        if ($this->hasRole('facilitator')) {
            return 'Facilitator';
        }
        
        if ($this->hasRole('content-creator')) {
            return 'Content Creator';
        }
        
        if ($this->hasRole('participant')) {
            return 'Participant';
        }
        
        return 'Unknown';
    }

    /**
     * Get user's capabilities summary
     */
    public function getCapabilities(): array
    {
        return [
            'can_manage_users' => $this->canManageUsers(),
            'can_manage_workshops' => $this->canManageWorkshops(),
            'can_manage_assignments' => $this->canManageAssignments(),
            'can_view_analytics' => $this->canViewAnalytics(),
            'can_manage_system' => $this->canManageSystem(),
            'is_admin' => $this->isAdmin(),
            'is_facilitator' => $this->isFacilitator(),
            'is_content_creator' => $this->isContentCreator(),
            'is_participant' => $this->isParticipant(),
            'primary_role' => $this->getPrimaryRole(),
        ];
    }
}
