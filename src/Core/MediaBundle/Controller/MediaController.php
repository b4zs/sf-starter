<?php


namespace Core\MediaBundle\Controller;


class MediaController extends \Sonata\MediaBundle\Controller\MediaController
{
	public function displayAction($id, $format = 'reference')
	{
		$response = parent::downloadAction($id, $format);

		$value = $response->headers->get('Content-disposition');
		$response->headers->set('Content-disposition', str_replace('attachment;', 'inline;', $value));

		return $response;
	}


}