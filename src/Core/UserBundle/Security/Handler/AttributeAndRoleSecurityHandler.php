<?php


namespace Core\UserBundle\Security\Handler;


use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Security\Handler\RoleSecurityHandler;

class AttributeAndRoleSecurityHandler extends RoleSecurityHandler
{
    public function isGranted(AdminInterface $admin, $attributes, $object = null)
    {
        if (!\is_array($attributes)) {
            $attributes = [$attributes];
        }

        $prefixedAttributes = null;
        $allRole = null;

        if ((null === $object || 'CREATE' === $attributes[0]) || $object instanceof AdminInterface) {
            $allRole = sprintf($this->getBaseRole($admin), 'ALL');

            $prefixedAttributes = [];
            foreach ($attributes as $attribute) {
                $prefixedAttributes[] = sprintf($this->getBaseRole($admin), $attribute);
            }
        }

        try {
//            $a = $this->authorizationChecker->isGranted($this->superAdminRoles);
//            $b = $this->authorizationChecker->isGranted($attributes, $object);
//            $c = ($prefixedAttributes && $this->authorizationChecker->isGranted($prefixedAttributes, $object));
//            $d = ($allRole && $this->authorizationChecker->isGranted([$allRole], $object));
//            return $a || $b ||$c || $d;


            return $this->authorizationChecker->isGranted($this->superAdminRoles)
                ||  $this->authorizationChecker->isGranted($attributes, $object)
                || ($prefixedAttributes && $this->authorizationChecker->isGranted($prefixedAttributes, $object))
                || ($allRole && $this->authorizationChecker->isGranted([$allRole], $object))
            ;
        } catch (AuthenticationCredentialsNotFoundException $e) {
            return false;
        }
    }


}
