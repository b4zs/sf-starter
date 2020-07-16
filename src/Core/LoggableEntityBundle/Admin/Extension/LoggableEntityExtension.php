<?php
namespace Core\LoggableEntityBundle\Admin\Extension;

use Core\LoggableEntityBundle\Enum\LogNoteTypesEnum;
use Core\LoggableEntityBundle\Interfaces\EntityLogHistoryAdminInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Gedmo\Loggable\LoggableListener;
use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\Block;
use Core\LoggableEntityBundle\Entity\LogEntry;
use Core\LoggableEntityBundle\Model\LogExtraData;
use Core\LoggableEntityBundle\Model\LogExtraDataAware;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class LoggableEntityExtension
 * @package Core\LoggableEntityBundle\Admin\Extension
 */
class LoggableEntityExtension extends AdminExtension
{
	/** @var  EntityManager */
	protected $entityManager;

	/**
	 * @var TranslatorInterface
	 */
	protected $translator;

	/**
	 * az admin classok, amik ide bekerülnek, megkapják a blockot, akkor is ha nincs rajtuk az interface
	 *
	 * @var array
	 */
	protected $forceAdmins = array();

	/**
	 * @param EntityManager $entityManager
	 */
	public function setEntityManager($entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @param TranslatorInterface $translator
	 */
	public function setTranslator(TranslatorInterface $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param array $admin_classes
	 */
	public function setAdmins(array $admin_classes) {
		$this->forceAdmins = $admin_classes;
	}

	/**
	 * @param $admin_class
	 */
	public function addAdmin($admin_class) {
		$this->forceAdmins[] = $admin_class;
	}

	/**
	 * @param LoggableListener $loggableListener
	 */
	public function setLoggableListener($loggableListener)
	{
		$this->loggableListener = $loggableListener;
	}

	/**
	 * @var LoggableListener
	 */
	private $loggableListener;

	/**
	 * @param FormMapper $form
	 */
	public function configureFormFields(FormMapper $form)
	{
		if (
		    !$form->getAdmin()->getSubject() instanceof LogExtraDataAware
			|| !$this->shouldUseExtension($form->getAdmin())
			// || 'form' !== $form->getFormBuilder()->getType()->getName()
        ) {
			return;
		}

		$form->getAdmin()->getSubject()->setLogExtraData(new LogExtraData());

		if(1 !== count($form->getAdmin()->getFormTabs())) {
			$form->tab('core.loggable.note');
		}

		$form->with('Add note');

		$choices = LogNoteTypesEnum::getChoices();
		foreach($choices as $key =>$val) {
			$choices[$key] = $this->translator->trans($val, array(), 'CoreLoggableEntityBundle');
		}

		//TODO: get these values for the specific object type
		$form->add('log_custom_action', ChoiceType::class, array(
			'required' => false,
			'choices'  => $choices,
			// 'empty_value'   => $this->translator->trans('form.label.default_update', array(), 'CoreLoggableEntityBundle'),
			'expanded'      => true,
			'label'         => false,
			'property_path' => 'logExtraData.customAction',
			'attr'          => array('class' => 'list-inline'),
		));
		$form->add('log_extra_comment', TextareaType::class, array(
			'required'      => false,
			'label'         => false,
			'property_path' => 'logExtraData.comment',
		));

		$form->end();

		if(1 !== count($form->getAdmin()->getFormTabs())) {
			$form->end();
		}

		$form->getFormBuilder()->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event, $eventName, EventDispatcher $eventDispatcher){
			$object = $event->getData();
			if ($object instanceof LogExtraDataAware && $object->getLogExtraData() instanceof LogExtraData) {
				if ($object->getLogExtraData()->hasData()) {
					$object->setUpdatedAt(new \DateTime());
				}
			}
		});
	}

	/**
	 * @param AdminInterface $admin
	 * @return bool
	 */
	private function shouldUseExtension(AdminInterface $admin)
	{
		if(method_exists($admin, 'displayLoggableBlock')) {
			return $admin->displayLoggableBlock();
		}

		return $admin->getSubject()
			&& $admin instanceof EntityLogHistoryAdminInterface
		;
	}


	/**
	 * @param BlockEvent $event
	 */
	public function onAdminEditFormBottom(BlockEvent $event)
	{
		/** @var AdminInterface $admin */
		$admin = $event->getSetting('admin');

		$admin_class = get_class($admin);

		if (null === $admin->getSubject()->getId() || (!$this->shouldUseExtension($admin) && !in_array($admin_class, $this->forceAdmins))) {
			return;
		}

		$block = new Block();
		$block->setType('core.loggable_entity.block.entity_log');
		$block->setSetting('subject_class', get_class($admin->getSubject()));
		$block->setSetting('subject_id', $admin->getSubject()->getId());
		$event->addBlock($block);
	}
}
