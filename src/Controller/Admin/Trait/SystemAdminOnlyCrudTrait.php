<?php

declare(strict_types=1);

namespace App\Controller\Admin\Trait;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;

/**
 * Restricts EasyAdmin CRUD actions to the system administrator role.
 */
trait SystemAdminOnlyCrudTrait
{
    protected function restrictActionsToSystemAdmin(Actions $actions): Actions
    {
        return $actions->setPermissions([
            Action::INDEX => 'ROLE_SYSTEM_ADMIN',
            Action::NEW => 'ROLE_SYSTEM_ADMIN',
            Action::EDIT => 'ROLE_SYSTEM_ADMIN',
            Action::DELETE => 'ROLE_SYSTEM_ADMIN',
            Action::DETAIL => 'ROLE_SYSTEM_ADMIN',
            Action::BATCH_DELETE => 'ROLE_SYSTEM_ADMIN',
        ]);
    }
}
