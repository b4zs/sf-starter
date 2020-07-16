<?php


namespace Core\UserBundle\Security;

use Sonata\UserBundle\Security\EditableRolesBuilder as BaseEditableRolesBuilder;

class EditableRolesBuilder extends BaseEditableRolesBuilder
{
    private $hideSuperAdmin = null;

    public function getRoles($domain = false, $expanded = true)
    {
        $result = parent::getRoles($domain, $expanded);

        foreach ($result as $roleName => $includedRoles) {
            if ($translatedName = $this->filterRoleList($roleName, $includedRoles)) {
                $result[$roleName] = $translatedName;
            } else {
                unset($result[$roleName]);
            }
        }

        return $result;
    }

    private function filterRoleList($role, $includedRoles)
    {
        $includedRoles = explode(': ', $includedRoles);
        $includedRoles = end($includedRoles);
        $includedRoles = explode(',', $includedRoles);


        if (1 === count($includedRoles) || in_array('HIDDEN', $includedRoles)) {
            return null;
        } elseif (preg_match('/^ROLE_SONATA/', $role)) {
            return null;
        } elseif (preg_match('/^ROLE_CORE/', $role)) {
            return null;
        } elseif (preg_match('/^ROLE_GROUP_/', $role)) {
            return null;
        } elseif (preg_match('/^ROLE_APPLICATION_/', $role)) {
            return null;
        } elseif (in_array($role, array('SONATA', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH', 'IS_AUTHENTICATED_ANONYMOUSLY'))) {
            return null;
        } elseif ($role === 'ROLE_SUPER_ADMIN' && $this->shouldHideSuperAdmin()) {
            return null;
        }

        return $this->buildLabel($role);
    }

    public function buildLabel($name): ?string
    {
        $name = str_replace('_ADMIN_', '_', $name);
        $name = strtr($name, array(
            'APPLICATION_' => '',
            'ROLE_' => '',
            'BUNDLE_' => '',
        ));
        $name = implode('_', array_unique(explode('_', $name)));

        if ($name !== ($tranlatedName = $this->pool->getContainer()->get('translator')->trans($name))) {
            $name = $tranlatedName;
        } else {
            $name = ucwords(strtr(strtolower($name), array('_' => ' ')));
        }

        return $this->pool->getContainer()->get('translator')->trans($name);
    }

    private function shouldHideSuperAdmin()
    {
        if (null === $this->hideSuperAdmin) {
            $this->hideSuperAdmin = $this->authorizationChecker->isGranted('ROLE_HIDE_REAL_SUPERADMIN');
        }

        return $this->hideSuperAdmin;
    }
}
