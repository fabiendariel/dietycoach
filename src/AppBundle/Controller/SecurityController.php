<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     */
    public function loginAction()
    {
	
	
        $helper = $this->get('security.authentication_utils');
        $mdpo = true;
		if (isset($_SESSION["_sf2_attributes"]["_security.main.target_path"])) {
        $mdpo = explode('admin',$_SESSION["_sf2_attributes"]["_security.main.target_path"]);
			}
		else {
		$mdpo = false;
		}
        return $this->render('AppBundle:Security:login.html.twig', [
            'affiche_mdpo'=>count($mdpo)>1?true:false,
            'last_username' => $helper->getLastUsername(),
            'error' => $helper->getLastAuthenticationError(),
        ]);
		
		
		
    }
    
    /**
     * @Route("/login_check", name="security_login_check")
     */
    public function loginCheckAction()
    {
        
    }
    
    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {

    }
}