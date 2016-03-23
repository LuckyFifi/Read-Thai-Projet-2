<?php
// src/Rw/UserBundle/Controller/SecurityController.php;

namespace Rw\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Rw\UserBundle\Entity\User;
use Rw\UserBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SecurityController extends Controller
{
	public function loginAction(Request $request)
	{
		// Si le visiteur est déjà identifié, on le redirige vers l'accueil
		if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
		  return $this->redirectToRoute('rwblog_home');
		}
		
		// Le service authentication_utils permet de récupérer le nom d'utilisateur
		// et l'erreur dans le cas où le formulaire a déjà été soumis mais était invalide
		// (mauvais mot de passe par exemple)
		$authenticationUtils = $this->get('security.authentication_utils');

		return $this->render('RwUserBundle:Security:login.html.twig', array(
		  'last_username' => $authenticationUtils->getLastUsername(),
		  'error'         => $authenticationUtils->getLastAuthenticationError(),
		));
	}
	public function addAction()
	{
		// On crée un objet User
		$User = new User();
		// champs préremplis
		//$User->setSalt('');	
		// On crée le formulaire
		$form = $this->createForm(new UserType(), $User);	
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
				$em->persist($User);
				$em->flush();
				// On redirige vers la page de visualisation du user nouvellement créé
				return $this->redirect($this->generateUrl('login'));
			}
		}
		// À ce stade :
		// - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
		// - Soit la requête est de type POST, mais le formulaire n'est pas valide, donc on l'affiche de nouveau
		return $this->render('RwUserBundle:Security:Adduser.html.twig', array(
		'form' => $form->createView(),
		));
	}
}