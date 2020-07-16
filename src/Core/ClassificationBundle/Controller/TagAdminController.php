<?php


namespace Core\ClassificationBundle\Controller;


use Sonata\AdminBundle\Controller\CRUDController;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Response;

class TagAdminController extends CRUDController
{
	public function getTagsAction()
	{
		$request = $this->container->get('request');
		$search = $request->get('search');

		$skillsQuery = $this->container
			->get('doctrine')
			->getRepository('CoreClassificationBundle:Tag')
			->createQueryBuilder('tag')
			->andWhere('tag.name LIKE :search')
			->setParameter('search', sprintf('%%%s%%', $search));

		$pagination = $this->container
			->get('knp_paginator')
			->paginate(
				$skillsQuery,
				$request->get('page', 1),
				$request->get('limit', 10)
			);

		$skills = array();

		/** @var Skill $skill */
		foreach ($pagination as $skill) {
			$skills[] = $skill->getName();
		}

		$paginationData = $pagination->getPaginationData();

		return new Response(
			json_encode(array(
				'results'   => $skills,
				'total'     => (int)$paginationData['totalCount'],
			)),
			200,
			array(
				'Content-type' => 'application/json',
			)
		);
	}

} 