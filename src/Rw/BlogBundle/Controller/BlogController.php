<?php

// src/Rw/BlogBundle/Controller/BlogController.php

namespace Rw\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Rw\BlogBundle\Entity\Billet;
use Rw\BlogBundle\Form\BilletType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class BlogController extends Controller
{
	public function indexAction($page)
	{
		if ($page < 1) {
			// On déclenche une exception NotFoundHttpException, cela va afficher
			// une page d'erreur 404 (qu'on pourra personnaliser plus tard d'ailleurs)
			throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
		}
		$repository = $this->getDoctrine()
						->getManager()
						->getRepository('RwBlogBundle:Billet');
						
		$billets = $repository->findBy(array('author' => 'LuckyFifi'),
                                     array('date' => 'desc'),
                                     3,
                                     0);
		
		return $this->render('RwBlogBundle:Blog:index.html.twig', array(
		'billets' => $billets
		));
	}
	
	public function listAction()
	{
		$repository = $this->getDoctrine()
						->getManager()
						->getRepository('RwBlogBundle:Billet');

		$billets = $repository->findAll();
		
		return $this->render('RwBlogBundle:Blog:list.html.twig', array(
		'billets' => $billets
		));
	}
	
	public function viewAction($id)
	{
		// On récupère le repository
		$repository = $this->getDoctrine()
						->getManager()
						->getRepository('RwBlogBundle:Billet');
		// On récupère l'entité correspondant à l'id $id
		$billet = $repository->find($id);
		// $billet est une instance de Rw\BlogBundle\Entity\Billet
		// Ou null si aucun billet n'a été trouvé avec l'id $id
		if($billet === null)
		{
			throw $this->createNotFoundException('Billet[id='.$id.'] inexistant.');
		}
		return $this->render('RwBlogBundle:Blog:view.html.twig', array(
			'billet' => $billet
		));
	}
	public function addAction()
	{
		// On crée un objet Billet
		$billet = new Billet();
		// champs préremplis
		$billet->setAuthor('LuckyFifi');	
		// On crée le formulaire
		$form = $this->createForm(new BilletType, $billet);	
		// On récupère la requête
		$request = $this->get('request');
		// On vérifie qu'elle est de type POST
		if ($request->getMethod() == 'POST') {
			// On fait le lien Requête <-> Formulaire
			// À partir de maintenant, la variable $billet contient les valeurs entrées dans le formulaire par le visiteur
			$form->bind($request);
			// On vérifie que les valeurs entrées sont correctes
			// (Nous verrons la validation des objets en détail dans le prochain chapitre)
			if ($form->isValid()) {
				// On l'enregistre notre objet $billet dans la base de données
				$em = $this->getDoctrine()->getManager();
				$em->persist($billet);
				$em->flush();
				// On redirige vers la page de visualisation du billet nouvellement créé
				return $this->redirect($this->generateUrl('rwblog_view', array('id' => $billet->getId())));
			}
		}
		// À ce stade :
		// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - Soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
		return $this->render('RwBlogBundle:Blog:add.html.twig', array(
		'form' => $form->createView(),
		));
	}
}