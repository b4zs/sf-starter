<?php

namespace Application\UserBundle\Admin\Extension;

use Application\UserBundle\Entity\User;
use Core\UserBundle\Admin\Extension\UserAdminRequireOwnPasswordForPasswordChange;
use Core\UserBundle\Admin\UserAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * for UserAdmin
 * @see UserAdmin
 */
class UserAdminSecurityRulesAdminExtension extends AbstractAdminExtension
{
    public function configureFormFields(FormMapper $formMapper)
    {
    }

    public function configureListFields(ListMapper $listMapper)
    {
    }


    public function configureQuery(AdminInterface $admin, ProxyQueryInterface $query, $context = 'list')
    {

    }

    public function alterObject(AdminInterface $admin, $object)
    {
    }
}
