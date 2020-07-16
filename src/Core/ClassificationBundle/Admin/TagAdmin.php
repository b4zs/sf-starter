<?php


namespace Core\ClassificationBundle\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\ClassificationBundle\Admin\TagAdmin as BaseTagAdmin;

class TagAdmin extends BaseTagAdmin
{

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);
        $listMapper->remove('slug');
    }

    public function configure()
    {
        parent::configure();

        $this->datagridValues['_sort_by']    = 'id';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

	protected function configureRoutes(RouteCollection $collection)
	{
		parent::configureRoutes($collection);
		$collection->add('get_tags');
	}


}