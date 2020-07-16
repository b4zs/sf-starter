<?php


namespace Core\ClassificationBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CategoryTreePathFixerCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('core:category:fix-path');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
		$repository = $entityManager->getRepository($this->getContainer()->getParameter('sonata.classification.manager.category.entity'));
		$allQuery = $repository
			->createQueryBuilder('c')
			->select('c.id')
			->orderBy('c.parent')
			->getQuery();

		foreach ($allQuery->getResult() as $allQueryRow) {
			$id = $allQueryRow['id'];
			$category = $repository->find($id);

			$output->writeln('Processing #'.$id);
			$name = $category->getName();
			$category->setName($name);

			$entityManager->flush($category);
		}
		$output->writeln('done');
	}


}