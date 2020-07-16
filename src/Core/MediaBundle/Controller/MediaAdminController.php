<?php


namespace Core\MediaBundle\Controller;


use CoopTilleuls\Bundle\CKEditorSonataMediaBundle\Controller\MediaAdminController as BaseController;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Bridge\Twig\Command\DebugCommand;

class MediaAdminController extends \Sonata\MediaBundle\Controller\MediaAdminController
{
    private function getTemplate($name)
    {
        $key = '[configuration][templates]['.$name.']';
        $configuration = $this->container->getParameter('coop_tilleuls_ck_editor_sonata_media');
        $pa = new PropertyAccessor();

        if ($pa->isReadable($configuration, $key)) {
            return $pa->getValue($configuration, $key);
        } else {
            return null;
        }
    }

    public function uploadAction()
    {
        $mediaManager = $this->get('sonata.media.manager.media');

        $request = $this->getRequest();
        $provider = $request->get('provider');
        $file = $request->files->get('upload');

        if (!$request->isMethod('POST') || !$provider || null === $file) {
            throw $this->createNotFoundException();
        }

        $context = $request->get('context', $this->get('sonata.media.pool')->getDefaultContext());

        $tag = null;
        if (null !== ($tagName = $request->get('tag', null))) {
            $tagManager = $this->get('sonata.classification.manager.tag');
            $tag = $tagManager->findOneBy(array('name' => $tagName));
            if (null === $tag) {
                $tag = $tagManager->create();
                $tag->setName($tagName);
                $tag->setEnabled(true);
            }
        }

        /** @var Media $media */
        $media = $mediaManager->create();
        $media->setBinaryContent($file);
        $media->setEnabled(true);

        if (null !== $tag) {
            $media->getTags()->add($tag);
        }

        $mediaManager->save($media, $context, $provider);
        $this->admin->createObjectSecurity($media);

        return $this->render($this->getTemplate('upload'), array(
            'action' => 'list',
            'object' => $media
        ));
    }

    public function browserAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedHttpException();
        }

        if ($tagName = $this->getRequest()->get('tag', null)) {
            $tag = $this->get('sonata.classification.manager.tag')->findOneBy(array('name' => $tagName));
            if (null !== $tag) {
                $this->admin->getDatagrid()->setValue('tags', null, array($tag->getId()));
            }
        }

        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        $datagrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        // Store formats
        $formats = array();
        foreach ($datagrid->getResults() as $media) {
            $formats[$media->getId()] = $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext());
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->getTemplate('browser'), array(
            'action' => 'browser',
            'form' => $formView,
            'datagrid' => $datagrid,
            'formats' => $formats,
            'base_template' => 'CoreMediaBundle::iframe_layout.html.twig'
        ));
    }

    private function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $twig->getExtension(FormExtension::class)->renderer->setTheme($formView, $theme);

            return;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $twig->getRuntime(TwigRenderer::class)->setTheme($formView, $theme);

            return;
        }

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }
}
