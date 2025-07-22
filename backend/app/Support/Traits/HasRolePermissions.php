<?php

namespace App\Support\Traits;

use App\Enums\UserTypeEnum;

trait HasRolePermissions
{
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is facilitator
     */
    public function isFacilitator(): bool
    {
        return $this->hasRole('facilitator');
    }

    /**
     * Check if user is participant
     */
    public function isParticipant(): bool
    {
        return $this->hasRole('participant');
    }

    /**
     * Get user's primary role name
     */
    public function getPrimaryRole(): string
    {
        if ($this->isAdmin()) {
            return 'Admin';
        }

        if ($this->isFacilitator()) {
            return 'Facilitator';
        }

        if ($this->isParticipant()) {
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
            'is_admin' => $this->isAdmin(),
            'is_facilitator' => $this->isFacilitator(),
            'is_participant' => $this->isParticipant(),
            'primary_role' => $this->getPrimaryRole(),
            'user_type' => $this->type,
        ];
    }
}
