<?php

namespace App\Policies;

use App\Models\MailDocument;
use App\Models\User;

class MailDocumentPolicy
{
    public function view(User $user, MailDocument $mailDocument): bool
    {
        return $mailDocument->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageMails();
    }

    public function update(User $user, MailDocument $mailDocument): bool
    {
        return $user->canManageMails();
    }

    public function delete(User $user, MailDocument $mailDocument): bool
    {
        return $user->canManageMails();
    }

    /**
     * Summary reports cover all correspondence, so only roles that see everything get them.
     */
    public function viewReports(User $user): bool
    {
        return $user->canViewAllMails();
    }
}
