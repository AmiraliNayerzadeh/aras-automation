<?php

namespace App\Contracts;

use App\Enums\ApprovalStepRole;
use App\Models\User;

interface Approvable
{
    /**
     * @return array<int, ApprovalStepRole>
     */
    public function approvalStepRoles(): array;

    public function requester(): User;

    /**
     * Permission namespace for this request type, e.g. 'leaves' or 'missions'.
     */
    public function permissionPrefix(): string;
}
