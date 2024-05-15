<?php

namespace AdminBundle\Controller;


use AppBundle\Entity\CoachDisponibilite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use AppBundle\Entity\User;
use AppBundle\Entity\Coach;
use AppBundle\Entity\CodeAcces;

use AdminBundle\Form\AdministrationCodeAccesType;
use AdminBundle\Form\AdministrationCoachType;
use AdminBundle\Form\AdministrationCoachAddType;
use AdminBundle\Form\AdministrationCoachDisponibiliteType;
use AdminBundle\Form\AdministrationUserType;
use AppBundle\Form\AdministrationUserAddType;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class AdministrationController extends Controller
{

    public function indexAction(Request $request)
    {
        return $this->render('AdminBundle:Administration:index.html.twig', [
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des utilisateurs
     * @param $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function utilisateursAction($page)
    {

        $page = (1 > $page) ? 1 : $page;

        // Taille de la pagination depuis les configs
        $nbPerPage = 20;

        // Liste des utilisateurs
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');

        $users = $repository->getPaginatedUsers($page, $nbPerPage);

        // Calcul du nombre total de pages
        $nbPages = ceil(count($users) / $nbPerPage);

        // Si la page n'existe pas, on retourne sur la première page
        if (0 !== (int)$nbPages && $page > $nbPages)
        {
            return $this->redirect($this->generateUrl('app_administration_utilisateurs', ['page' => 1]));
        }

        return $this->render('AdminBundle:Administration:utilisateurs.html.twig', [
            'users'   => $users,
            'nbPages' => $nbPages,
            'page'    => $page
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Création d'un utilisateur
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function utilisateurAddAction(Request $request)
    {
        //Requete pour éviter l'erreur "Request Heterogeneous"
        $this->getDoctrine()->getManager()->getConnection()->exec("set quoted_identifier on;
        set ansi_warnings on;
        set ansi_nulls on;
        set CONCAT_NULL_YIELDS_NULL on;
        set ANSI_PADDING on");

        $user = new User;

        $form = $this->createForm(AdministrationUserAddType::class, $user);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {

            $user->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());

            $entityManager = $this->get('doctrine.orm.entity_manager');

            $encoder      = $this->get('security.encoder_factory')->getEncoder($user);
            $encoded_pass = $encoder->encodePassword($user->getPassword(), $user->getSalt());

            $user->setPassword($encoded_pass);
            $user->setEnabled(True);

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('administration_user');

            $user->addRole($this->getParameter('roles')['user']);

            $entityManager->persist($user);
            $entityManager->flush();

            $message = $this
                ->get('translator')
                ->trans("L'utilisateur a bien été enregistré.")
            ;

            $this->get('session')->getFlashBag()->set('success', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }

        return $this->render('AdminBundle:Administration:utilisateur.update.html.twig', [
            'form'               => $form->createView(),
            'title'              => 'Création de l\'utilisateur',
            'button'             => 'Enregistrer'
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Édition d'un utilisateur par son identifiant
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function utilisateurUpdateAction(Request $request)
    {
        //Requete pour éviter l'erreur "Request Heterogeneous"
        $this->getDoctrine()->getManager()->getConnection()->exec("set quoted_identifier on;
        set ansi_warnings on;
        set ansi_nulls on;
        set CONCAT_NULL_YIELDS_NULL on;
        set ANSI_PADDING on");

        
        // Recherche de l'utilisateur
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repository->getUserById($request->get('user_id'));

        // Si l'utilisateur n'existe pas
        if (null === $user)
        {
            $message = $this
                ->get('translator')
                ->trans("L'utilisateur %user_id% n'existe pas.", ['%user_id%' => $request->get('user_id')])
            ;

            $this->get('session')->getFlashBag()->add('error', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }

        // Enregistrement temporaire du mot de passe
        $tmp_password = $user->getPassword();

        $form = $this->createForm(AdministrationUserType::class, $user);
        $form->handleRequest($request);

        // Soumission du formulaire
        echo $form->isSubmitted().' && '.$form->isValid();
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('administration_user');

            // Update de mot de passe
            if (false === empty($postedValues['password']))
            {
                $encoder      = $this->get('security.encoder_factory')->getEncoder($user);
                $encoded_pass = $encoder->encodePassword($postedValues['password'], $user->getSalt());

                $user->setPassword($encoded_pass);
            }
            // Si les champs de mot de passe sont vides on conserve l'ancien
            else
            {
                $user->setPassword($tmp_password);
            }


            $user->addRole($this->getParameter('roles')['user']);

            $entityManager->persist($user);
            $entityManager->flush();

            $message = $this
                ->get('translator')
                ->trans("L'utilisateur a bien été mis à jour.")
            ;

            $this->get('session')->getFlashBag()->add('success', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }

        return $this->render('AdminBundle:Administration:utilisateur.update.html.twig', [
            'form'               => $form->createView(),
            'title'              => 'Édition de l\'utilisateur',
            'button'             => 'Modifier'
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Delete user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function utilisateurDeleteAction(Request $request)
    {
        //Requete pour éviter l'erreur "Request Heterogeneous"
        $this->getDoctrine()->getManager()->getConnection()->exec("set quoted_identifier on;
        set ansi_warnings on;
        set ansi_nulls on;
        set CONCAT_NULL_YIELDS_NULL on;
        set ANSI_PADDING on");


        // Recherche de l'utilisateur
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $user = $repository->getUserById($request->get('user_id'));

        // Si l'utilisateur n'existe pas
        if (null === $user)
        {
            $message = $this
              ->get('translator')
              ->trans("L'utilisateur %user_id% n'existe pas.", ['%user_id%' => $request->get('user_id')])
            ;

            $this->get('session')->getFlashBag()->add('error', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }else{
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->remove($user);
            $entityManager->flush();

            $message = $this
              ->get('translator')
              ->trans("L'utilisateur a été supprimé.")
            ;

            $this->get('session')->getFlashBag()->add('success', $message);

            return $this->redirect($this->generateUrl('app_administration_utilisateurs'));
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Suite à la confirmation reçue par email, l'utilisateur est invité à "Créer" son mot de passe
     * @param Request $request
     * @see https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/configuration_reference.rst
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function confirmationAction(Request $request)
    {
        $current_user = $this->getUser();

        // Recherche de l'utilisateur
        $user = $this
            ->get('app.repository.user')
            ->find($current_user->getId())
        ;

        // Si l'utilisateur n'existe pas
        if (null === $user)
        {
            $message = $this
                ->get('translator')
                ->trans("Une erreur technique est survenue, veuillez contacter l'administrateur du site.")
            ;

            $this->get('session')->getFlashBag()->set('error', $message);

            throw $this->createNotFoundException($message);
        }

        $form = $this->createForm(UserPasswordType::class, $user);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager = $this->get('doctrine.orm.entity_manager');

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('user_password');

            // Encodage de mot de passe
            $encoder      = $this->get('security.encoder_factory')->getEncoder($user);
            $encoded_pass = $encoder->encodePassword($postedValues['password'], $user->getSalt());

            $user->setPassword($encoded_pass);
            $entityManager->flush();

            $message = $this
                ->get('translator')
                ->trans('Votre mot de passe a bien été créé.')
            ;

            $this->get('session')->getFlashBag()->set('success', $message);

            return $this->redirect($this->generateUrl('app_accueil'));
        }

        return $this->render('AdminBundle:Activation:confirmation.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Redirection suite à un changement de mot de passe
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changePasswordConfirmAction()
    {
        $current_user = $this->getUser();

        // Recherche de l'utilisateur
        $user = $this
            ->get('app.repository.user')
            ->find($current_user->getId())
        ;

        if ($user)
        {
            $message = $this
                ->get('translator')
                ->trans('Votre mot de passe a bien été modifié.')
            ;

            $this->get('session')->getFlashBag()->set('success', $message);

            return $this->redirect($this->generateUrl('app_accueil'));
        }
        else
        {
            $message = $this
                ->get('translator')
                ->trans("Une erreur technique est survenue, veuillez contacter l'administrateur du site.")
            ;

            $this->get('session')->getFlashBag()->set('error', $message);

            throw $this->createNotFoundException($message);
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des coachs
     * @param $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function coachsAction($page)
    {

        $page = (1 > $page) ? 1 : $page;

        // Taille de la pagination depuis les configs
        $nbPerPage = 20;

        // Liste des coachs
        $repository = $this->getDoctrine()->getRepository('AppBundle:Coach');

        $coachs = $repository->getPaginatedResult($page, $nbPerPage);

        // Calcul du nombre total de pages
        $nbPages = ceil(count($coachs) / $nbPerPage);

        // Si la page n'existe pas, on retourne sur la première page
        if (0 !== (int)$nbPages && $page > $nbPages)
        {
            return $this->redirect($this->generateUrl('app_administration_coachs', ['page' => 1]));
        }

        return $this->render('AdminBundle:Administration:coachs.html.twig', [
          'coachs'   => $coachs,
          'nbPages' => $nbPages,
          'page'    => $page
        ]);
    }

    /**
     * Création d'un coach
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function coachAddAction(Request $request)
    {
        $coach = new Coach;

        $form = $this->createForm(AdministrationCoachAddType::class, $coach);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {

            // Récupération des valeurs du formulaire
            $postedValues = $request->request->get('administration_coach');

            $message = $this
              ->get('translator')
              ->trans("Le coach a bien été enregistré.")
            ;

            $this->get('session')->getFlashBag()->set('success', $message);

            return $this->redirect($this->generateUrl('app_administration_coachs'));
        }

        return $this->render('AdminBundle:Administration:coach.add.html.twig', [
          'form'               => $form->createView(),
          'title'              => 'Création d\'un coach',
          'button'             => 'Enregistrer'
        ]);
    }

    /**
     * Édition d'un coach par son identifiant
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function coachUpdateAction(Request $request)
    {
        // Recherche de l'utilisateur
        $repository = $this->getDoctrine()->getRepository('AppBundle:Coach');
        $coach = $repository->find($request->get('coach_id'));

        // Si le coach n'existe pas
        if (null === $coach)
        {
            $message = $this
              ->get('translator')
              ->trans("Le coach %coach_id% n'existe pas.", ['%coach_id%' => $request->get('coach_id')])
            ;

            $this->get('session')->getFlashBag()->add('error', $message);

            return $this->redirect($this->generateUrl('app_administration_coachs'));
        }

        $form = $this->createForm(AdministrationCoachType::class, $coach);
        $form->handleRequest($request);

        // Soumission du formulaire
        if ($form->isSubmitted() && $form->isValid())
        {
            $message = $this
              ->get('translator')
              ->trans("Le coach a bien été mis à jour.")
            ;

            $this->get('session')->getFlashBag()->add('success', $message);
        }

        $repository = $this->getDoctrine()->getRepository('AppBundle:CoachDisponibilite');
        $coachDispo = $repository->findBy(array('coach'=>$coach));
        $tab_dispoCoach = array();
        foreach($coachDispo as $cd){
            $tab_dispoCoach[] = array(
              'id'=>$cd->getId(),
              'coa_id'=>$coach->getId(),
              'start'=>$cd->getDateCreneauDebut()->format(\DateTime::ATOM),
              'end'=>$cd->getDateCreneauFin()->format(\DateTime::ATOM)
            );
        }

        return $this->render('AdminBundle:Administration:coach.update.html.twig', [
          'form'               => $form->createView(),
          'title'              => 'Édition d\'un coach',
          'button'             => 'Modifier',
          'dispoCoah'          => $tab_dispoCoach,
          'coach'              => $coach
        ]);
    }

    public function coachUpdateCreneauAction(Request $request)
    {
      // Recherche de l'utilisateur
      $repository = $this->getDoctrine()->getRepository('AppBundle:Coach');
      $postedValues = $request->get('administration_coach_disponibilite');


      if($postedValues){
        $coa_id = $postedValues['coa_id'];
        $dis_id = $postedValues['id'];
      }else{
        $dis_id = $request->get('id');
        $coa_id = $request->get('coa_id');
      }

      $coach = $repository->find($coa_id);
      $repository = $this->getDoctrine()->getRepository('AppBundle:CoachDisponibilite');
      $coachDispo = $repository->find($dis_id);
      if ($coachDispo) {
        $infos_dispo = array(
          'id' => $dis_id,
          'coa_id' => $coa_id,
          'title' => $request->get('title')?$request->get('title'):'',
          'btn_submit' => $request->get('btn_submit')?$request->get('btn_submit'):'Enregsitrer',
          'date' => $coachDispo->getDateCreneauDebut(),
          'heure_debut' => $coachDispo->getDateCreneauDebut(),
          'heure_fin' => $coachDispo->getDateCreneauFin()
        );
      }else {
        $infos_dispo = array(
          'id' => $dis_id,
          'coa_id' => $coa_id,
          'title' => $request->get('title')?$request->get('title'):'Enregistrer un créneau de disponibilité',
          'btn_submit' => $request->get('btn_submit')?$request->get('btn_submit'):'Enregsitrer',
          'date' => $request->get('start')?date_create_from_format('Y-m-d H:i',$request->get('start')):null,
          'heure_debut'=>$request->get('start')?date_create_from_format('Y-m-d H:i',$request->get('start')):null,
          'heure_fin'=>$request->get('end')?date_create_from_format('Y-m-d H:i',$request->get('end')):null,
        );
      }
      $form = $this->createForm(AdministrationCoachDisponibiliteType::class, null, ['datas_import'=>$infos_dispo]);
      $form->handleRequest($request);
      // Soumission du formulaire
      if ($form->isValid()) {
        $entityManager = $this->get('doctrine.orm.entity_manager');

        // Récupération des valeurs du formulaire
        if ($coachDispo) {
          $dsi = $coachDispo;
          $message = $this
            ->get('translator')
            ->trans("La période de disponibilité  a bien été mise à jour.");
        }else{
          $dsi = new CoachDisponibilite();
          $message = $this
            ->get('translator')
            ->trans("La période de disponibilité  a bien été ajoutée.");
        }
        $dsi->setCoach($coach);
        $date_debut =  $postedValues['date'].' '.$postedValues['heure_debut']['hour'].':'.($postedValues['heure_debut']['minute']=='0'?'00':'30');
        $datetime_debut = date_create_from_format('Y-m-d H:i',$date_debut);
        $dsi->setDateCreneauDebut($datetime_debut);
        $date_fin=  $postedValues['date'].' '.$postedValues['heure_fin']['hour'].':'.($postedValues['heure_fin']['minute']=='0'?'00':'30');
        $datetime_fin = date_create_from_format('Y-m-d H:i',$date_fin);
        $creneau_max = date_create_from_format('Y-m-d H:i',$datetime_fin->format('Y-m-d').' 23:00');
        if($datetime_fin > $creneau_max)
          $datetime_fin = $creneau_max;
        $dsi->setDateCreneauFin($datetime_fin);

        $entityManager->persist($dsi);
        $entityManager->flush();



        $this->get('session')->getFlashBag()->add('success', $message);

        $output = [
          'has_error' => false
        ];
        return new JsonResponse($output);
      }

      return $this->render('AdminBundle:Administration:form_creneau.html.twig', [
        'form' => $form->createView(),
        'infos_dispo' => $infos_dispo,
        'coach' => $coach
      ]);
    }

    public function coachMoveCreneauAction(Request $request)
    {
      $repository = $this->getDoctrine()->getRepository('AppBundle:CoachDisponibilite');
      $coachDispo = $repository->find($request->get('creneau_id'));
      $refresh = false;

      $creneau_start = date_create_from_format('Y-m-d H:i',$request->get('creneau_start'));
      $creneau_min = date_create_from_format('Y-m-d H:i',$creneau_start->format('Y-m-d').' 08:00');
      $creneau_end = date_create_from_format('Y-m-d H:i',$request->get('creneau_end'));
      $creneau_max = date_create_from_format('Y-m-d H:i',$creneau_end->format('Y-m-d').' 18:00');
      if($creneau_start < $creneau_min){
        $creneau_start = $creneau_min;
        $refresh = true;
      }
      if($creneau_end > $creneau_max){
        $creneau_end = $creneau_max;
        $refresh = true;
      }

      if ($coachDispo) {

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $coachDispo->setDateCreneauDebut($creneau_start);
        $coachDispo->setDateCreneauFin($creneau_end);
        $entityManager->persist($coachDispo);
        $entityManager->flush();

        $message = $this
          ->get('translator')
          ->trans("La période de disponibilité  a bien été mise à jour.");
        $this->get('session')->getFlashBag()->add('success', $message);
        $output = [
          'has_error' => false,
          'refresh' => $refresh
        ];
      }else {
        $message = $this
          ->get('translator')
          ->trans("Une erreur a empêchée la modification de cette période de disponibilité.");
        $this->get('session')->getFlashBag()->add('success', $message);
        $output = [
          'has_error' => true
        ];
      }

      return new JsonResponse($output);
    }

    public function coachTestCreneauAction(Request $request)
    {
      // Recherche de l'utilisateur
      $postedValues = $request->get('administration_coach_disponibilite');
      $output = [
        'has_error' => false
      ];
      if($postedValues) {
        $coa_id = $postedValues['coa_id'];
        $date_debut = $postedValues['date'] . ' ' . $postedValues['heure_debut']['hour'] . ':' . ($postedValues['heure_debut']['minute'] == '0' ? '00' : '30');
        $heure_debut = date_create_from_format('Y-m-d H:i', $date_debut);
        $date_fin = $postedValues['date'] . ' ' . $postedValues['heure_fin']['hour'] . ':' . ($postedValues['heure_fin']['minute'] == '0' ? '00' : '30');
        $heure_fin = date_create_from_format('Y-m-d H:i', $date_fin);

        $repository = $this->getDoctrine()->getRepository('AppBundle:CoachDisponibilite');
        $coachDispoSuperpose = $repository->findSuperposition($coa_id, $heure_debut, $heure_fin);
        if (count($coachDispoSuperpose) > 0) {
          $output = [
            'has_error' => true
          ];
        }
      }

      return new JsonResponse($output);
    }

    public function coachDeleteCreneauAction(Request $request)
    {
      // Recherche de l'utilisateur
      $repository = $this->getDoctrine()->getRepository('AppBundle:CoachDisponibilite');
      $entityManager = $this->get('doctrine.orm.entity_manager');
      $coachDispo = $repository->find($request->get('id_creneau_del'));
      if ($coachDispo) {
        $entityManager->remove($coachDispo);
        $entityManager->flush();

        $message = $this
          ->get('translator')
          ->trans("La période de disponibilité  a bien été supprimée.");

        $this->get('session')->getFlashBag()->add('success', $message);
        $output = [
          'has_error' => false
        ];
      }else{
        $output = [
          'has_error' => true
        ];
      }

      return new JsonResponse($output);

    }

    /**
     * Delete coach
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function coachDeleteAction(Request $request)
    {


        // Recherche de l'utilisateur
        $repository = $this->getDoctrine()->getRepository('AppBundle:Coach');
        $coach = $repository->find($request->get('coach_id'));

        // Si l'utilisateur n'existe pas
        if (null === $coach)
        {
            $message = $this
              ->get('translator')
              ->trans("Le coach %coach_id% n'existe pas.", ['%coach_id%' => $request->get('coach_id')])
            ;

            $this->get('session')->getFlashBag()->add('error', $message);

            return $this->redirect($this->generateUrl('app_administration_coachs'));
        }else{
            $entityManager = $this->get('doctrine.orm.entity_manager');
            $entityManager->remove($coach);
            $entityManager->flush();

            $message = $this
              ->get('translator')
              ->trans("Le coach a été supprimé.")
            ;

            $this->get('session')->getFlashBag()->add('success', $message);

            return $this->redirect($this->generateUrl('app_administration_coachs'));
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Formulaire de demande codes d'accès
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function codesAction(Request $request)
    {
        $form = $this->get('form.factory')->create(AdministrationCodeAccesType::class, null);
        $form->handleRequest($request);
        $lots_codes = null;
        $session = $this->get('session');
        $session->set('liste_groupes', null);

        if ($request->get("administration_code_acces")) {
            $datas = $request->get('administration_code_acces');
            $date_debut = \DateTime::createFromFormat('Y-m-d', $datas['datePeremptionDebut']);

            $date_fin = \DateTime::createFromFormat('Y-m-d', $datas['datePeremptionFin']);
            $nbLots = $datas['nbLots'];
            /*for($a=1;$a<=$nbLots;$a++){
                $lots_codes[$a] = array(
                  "date_debut" => $date_debut->format('d/m/Y'),
                  "date_fin" => $date_fin->format('d/m/Y'),
                  "detail" => $this->generateCodesAcces($date_debut,$date_fin)
                );
            }*/
            $session->set('liste_groupes', $lots_codes);
            $request->getSession()->getFlashBag()->add('success', $nbLots." lots de codes d'accès viennent d'être générés. Utilisez le bouton \"Extraire\" pour obtenir la version Excel du listing.");
        }


        return $this->render('AdminBundle:Administration:codes.html.twig', [
          'form' => $form->createView(),
          'liste_groupes' => $lots_codes
        ]);
    }

    private function generateCodesAcces(\DateTime $dateDebut, \DateTime $dateFin)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $repository = $this->getDoctrine()->getRepository('AppBundle:CodeAcces');

        $isOk = false;
        $isOk2 = false;
        $isOk3 = false;

        do{
            $code_test1 = $this->getCode();
            $codeAccesTest = $repository->findBy(array('code'=>$code_test1));
            if(!$codeAccesTest) {
                $isOk = true;
                $newCode = new CodeAcces();
                $newCode->setDateCreation(new \DateTime('now'));
                $newCode->setDatePeremptionDebut($dateDebut);
                $newCode->setDatePeremptionFin($dateFin);
                $newCode->setCodeUtilise(0);
                $newCode->setCode($code_test1);
                $entityManager->persist($newCode);
                $entityManager->flush();

                $id_group = $newCode->getId();
                $newCode->setGroupId($id_group);
                $entityManager->persist($newCode);
                $entityManager->flush();
            }
        }while(!$isOk);

        do{
            $code_test2 = $this->getCode();
            $codeAccesTest = $repository->findBy(array('code'=>$code_test2));
            if(!$codeAccesTest) {
                $isOk2 = true;
                $newCode2 = new CodeAcces();
                $newCode2->setDateCreation(new \DateTime('now'));
                $newCode2->setDatePeremptionDebut($dateDebut);
                $newCode2->setDatePeremptionFin($dateFin);
                $newCode2->setCodeUtilise(0);
                $newCode2->setCode($code_test2);
                $newCode2->setGroupId($id_group);
                $entityManager->persist($newCode2);
                $entityManager->flush();
            }
        }while(!$isOk2);

        do{
            $code_test3 = $this->getCode();
            $codeAccesTest = $repository->findBy(array('code'=>$code_test3));
            if(!$codeAccesTest) {
                $isOk3 = true;
                $newCode3 = new CodeAcces();
                $newCode3->setDateCreation(new \DateTime('now'));
                $newCode3->setDatePeremptionDebut($dateDebut);
                $newCode3->setDatePeremptionFin($dateFin);
                $newCode3->setCodeUtilise(0);
                $newCode3->setCode($code_test3);
                $newCode3->setGroupId($id_group);
                $entityManager->persist($newCode3);
                $entityManager->flush();
            }
        }while(!$isOk3);

        return array(
          'group_id'=>$id_group,
          'codes'=>array($code_test1,$code_test2,$code_test3)
        );
    }

    private function getCode(){
        $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key1 = $this->generate_string($permitted_chars, 4);
        $key2 = $this->generate_string($permitted_chars, 4);
        $key3 = $this->generate_string($permitted_chars, 4);

        return $key1.'-'.$key2.'-'.$key3;
    }

    private function generate_string($input, $strength = 16) {
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    /**
     * Génération de code d'accès
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function codesGenerateExcelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $liste_groupes = $this->get('session')->get('liste_groupes');

        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Dietycoach")
          ->setLastModifiedBy("Dietycoach")
          ->setTitle("ExtractionCodesDietycoach")
          ->setDescription("Extraction de code d'accès individuels pour l'outil de prise de rendez-vous Dietycoach");


        $phpExcelObject->setActiveSheetIndex(0)
          ->setCellValue('A1', 'DATE')
          ->setCellValue('B1', 'LOT')
          ->setCellValue('C1', 'CODE_LOT')
          ->setCellValue('D1', 'CODE_1')
          ->setCellValue('E1', 'CODE_2')
          ->setCellValue('F1', 'CODE_3')
          ->setCellValue('G1', 'DATE_EXPIRATION_1')
          ->setCellValue('H1', 'DATE_EXPIRATION_2')
        ;

        $row = 2;

        foreach($liste_groupes as $index=>$groupe){
            $phpExcelObject->getActiveSheet()
              ->setCellValue('A'.$row, date('d/m/Y'))
              ->setCellValue('B'.$row, $index)
              ->setCellValue('C'.$row, $groupe['detail']['group_id'])
              ->setCellValue('D'.$row, $groupe['detail']['codes'][0])
              ->setCellValue('E'.$row, $groupe['detail']['codes'][1])
              ->setCellValue('F'.$row, $groupe['detail']['codes'][2])
              ->setCellValue('G'.$row, $groupe['date_debut'])
              ->setCellValue('H'.$row, $groupe['date_fin']);
            $row++;
        }

        // Auto Size
        $sheet = $phpExcelObject->getActiveSheet();
        $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells( true );
        /** @var PHPExcel_Cell $cell */
        foreach( $cellIterator as $cell ) {
            $sheet->getColumnDimension( $cell->getColumn() )->setAutoSize( true );
        }

        //Bold
        $phpExcelObject->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);

        $phpExcelObject->getActiveSheet()->setTitle('Résultat');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          'ExtractionCodesDietycoach_'.date('YmdHis').'.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
