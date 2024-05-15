<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Rendezvous;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\CodeAcces;
use AppBundle\Form\CodeAccesType;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class AgendaController extends Controller
{

    public function codeAction(Request $request)
    {
        $today = new \DateTime();
        $today->modify('-1 days');
        $session = $this->get('session');
        $app_cle_captcha = $this->getParameter('cle_captcha');
        if($session->get('code_acces')){
            $repository = $this->getDoctrine()->getRepository('AppBundle:CodeAcces');
            $datas = $repository->findBy(array('code'=>$session->get('code_acces')));
            if($datas && !$datas[0]->getCodeUtilise()){
                $countCodesGroupUtilises = $repository->findCodeGroupUtilise($datas[0]->getGroupId());
                if($countCodesGroupUtilises == 3 && $today < $datas[0]->getDatePeremptionDebut()
                  || $countCodesGroupUtilises < 3 && $today < $datas[0]->getDatePeremptionFin())
                    return $this->redirect($this->generateUrl('app_agenda'));
            }
        }


        $form = $this->get('form.factory')->create(CodeAccesType::class, null);
        $form->handleRequest($request);

        $session->set('code_acces', null);
        $output = [
          'has_error' => false
        ];
        $postedValues = $request->get('code_acces');
        if ($postedValues['code']) {
            $code = strtoupper($postedValues['code']);
            $repository = $this->getDoctrine()->getRepository('AppBundle:CodeAcces');
            $datas = $repository->findBy(array('code' => $code));
            if ($datas && !$datas[0]->getCodeUtilise()) {
                $countCodesGroupUtilises = $repository->findCodeGroupUtilise($datas[0]->getGroupId());
                if(($countCodesGroupUtilises == 3 && $today <= $datas[0]->getDatePeremptionDebut())
                  || ($countCodesGroupUtilises < 3 && $today <= $datas[0]->getDatePeremptionFin()) )
                {
                    $session->set('code_acces',$code);
                    $em = $this->getDoctrine()->getManager();

                    $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
                    $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
                    $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
                    $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
                    $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
                    $statement = $em->getConnection()->prepare('EXEC connectCode @code=:code');
                    $statement->bindValue('code', $code);
                    $statement->execute();

                    return $this->redirect($this->generateUrl('app_agenda'));
                }else{
                    $output = [
                      'has_error' => true,
                      'message' =>  'Ce code ne permet plus de réserver une session de coaching. Veuillez entrer un autre code encore valide'
                        //'Ce code est expiré !'
                    ];
                }
            }elseif ($datas && $datas[0]->getCodeUtilise()) {
                $output = [
                  'has_error' => true,
                  'message' => 'Ce code a déjà été utilisé. Veuillez entrer un autre code valide'
                    //'Ce code a déjà été utilisé !'
                ];
            }else {
                $output = [
                  'has_error' => true,
                  'message' => 'Ce code ne permet pas de réserver une session de coaching. Veuillez entrer un code valide'
                    //'Ce code ne correspond pas à un code existant. Veuillez vérifier votre saisie.'
                ];
            }
        }
        return $this->render('AppBundle:Agenda:code.html.twig', [
          'output'  => $output,
          'form'    => $form->createView(),
          'app_cle_captcha' => $app_cle_captcha
        ]);
    }

    public function cguAction()
    {
        return $this->render('AppBundle:Agenda:cgu.html.twig');
    }

    public function mentionsAction()
    {
        return $this->render('AppBundle:Agenda:mentions.html.twig');
    }
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des rappels
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
        $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
        $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
        $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
        $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();


        $session = $this->get('session');

        if(date('w', time())==1)
            $this_monday = time();
        else
            $this_monday = strtotime('last monday', time());
        if($request->get('startDate'))
            $startDate = date_create_from_format('Y-m-d',$request->get('startDate'));
        else{
            $startDate = new \DateTime();
            $startDate->setTimestamp($this_monday);
        }
        $startTest = new \DateTime();
        $startTest->setTimestamp($startDate->getTimestamp());
        $prev_startDate = new \DateTime();
        $prev_startDate = $prev_startDate->setTimestamp($startDate->getTimestamp())->modify('-1 week');

        if($prev_startDate->getTimestamp() < $this_monday)
            $prev_startDate = null;


        if($session->get('code_acces')){
            $repository = $this->getDoctrine()->getRepository('AppBundle:CodeAcces');
            $datas = $repository->findBy(array('code'=>$session->get('code_acces')));
            if(!$datas || $datas[0]->getCodeUtilise()) {
                return $this->redirect($this->generateUrl('homepage'));
            }else{
                $today = new \DateTime();
                $countCodesGroupUtilises = $repository->findCodeGroupUtilise($datas[0]->getGroupId());
                if($countCodesGroupUtilises == 3 && $today > $datas[0]->getDatePeremptionDebut()
                  || $countCodesGroupUtilises < 3 && $today > $datas[0]->getDatePeremptionFin())
                    return $this->redirect($this->generateUrl('homepage'));
                else
                    $coa = $datas[0];
            }
        }else{
            return $this->redirect($this->generateUrl('homepage'));
        }

        $code = $session->get('code_acces');
        $statut = $this->getDoctrine()->getRepository('AppBundle:Statut')->find($this->getParameter('statut_rappel')['en_cours']);
        $rdv_existe = $this->getDoctrine()->getRepository('AppBundle:Rendezvous')->findBy(array(
          'codeAcces'=>$coa,
          'statutRdv'=>$statut
        ));
        if($rdv_existe){
            $rdv = $rdv_existe[0];
        }else{
            $rdv = null;
        }

        /* Gestion de la pagination si on a plusieurs coachs
        $page = (1 > $page) ? 1 : $page;
        $nbPerPage = 20;
        $tab_coach = array();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Coach');
        $datas = $repository->getPaginatedResult($page, $nbPerPage, null);
        */

        $date1 = new \DateTime();
        $date2 = new \DateTime();
        $date3 = new \DateTime();
        $date4 = new \DateTime();
        $date5 = new \DateTime();
        $date6 = new \DateTime();

        $date1 = $date1->setTimestamp($startDate->getTimestamp());
        $date2 = $date2->setTimestamp($date1->getTimestamp())->modify('+1 days');
        $date3 = $date3->setTimestamp($date1->getTimestamp())->modify('+2 days');
        $date4 = $date4->setTimestamp($date1->getTimestamp())->modify('+3 days');
        $date5 = $date5->setTimestamp($date1->getTimestamp())->modify('+4 days');
        $date6 = $date6->setTimestamp($date1->getTimestamp())->modify('+5 days');
        $next_startDate = $startDate->modify('+1 week');
        $date_next_creneau = $monday_next_creneau = null;

        //On a qu'un coach pour le moment
        $coach = $this->getDoctrine()->getRepository('AppBundle:Coach')->find(1);
        $tab_creneaux=array();

        $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
        $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
        $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
        $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
        $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
        $statement = $em->getConnection()->prepare('EXEC getCreneauxCode @sortie=1, @code=:code, @coa_id=:coach_id');
        $statement->bindValue('code', $code);
        $statement->bindValue('coach_id', $coach->getId());
        $statement->execute();
        $creneaux = $statement->fetchAll();

        foreach($creneaux as $creneau){
            if($creneau['jour'] >= $startTest->format('Y-m-d')){
                if(!$date_next_creneau){
                    $date_next_creneau = date_create_from_format('Y-m-d',$creneau['jour']);
                    $monday_next_creneau = strtotime('last monday', $date_next_creneau->getTimestamp());
                }
                if($creneau['jour'] <= $date6->format('Y-m-d')) {
                    if (!isset($tab_creneaux[$creneau['jour']])) {
                        $tab_creneaux[$creneau['jour']] = array('date' => $creneau['jour'], 'slots' => array());
                    }
                    $tab_creneaux[$creneau['jour']]['slots'][] = $creneau['heure'];
                    sort($tab_creneaux[$creneau['jour']]['slots']);
                }
            }
        }

        $array_day = ['Mon','Tue','Wed','Thu','Fri','Sat'];
        $array_jour = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $array_month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $array_mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre'];
        $date1_lib = '<div class="date"><span>'.str_replace($array_day,$array_jour,$date1->format('D')).'</span> '.str_replace($array_month,$array_mois,$date1->format('j F')).'</div>';
        $date2_lib = '<div class="date"><span>'.str_replace($array_day,$array_jour,$date2->format('D')).'</span> '.str_replace($array_month,$array_mois,$date2->format('j F')).'</div>';
        $date3_lib = '<div class="date"><span>'.str_replace($array_day,$array_jour,$date3->format('D')).'</span> '.str_replace($array_month,$array_mois,$date3->format('j F')).'</div>';
        $date4_lib = '<div class="date"><span>'.str_replace($array_day,$array_jour,$date4->format('D')).'</span> '.str_replace($array_month,$array_mois,$date4->format('j F')).'</div>';
        $date5_lib = '<div class="date"><span>'.str_replace($array_day,$array_jour,$date5->format('D')).'</span> '.str_replace($array_month,$array_mois,$date5->format('j F')).'</div>';
        $date6_lib = '<div class="date"><span>'.str_replace($array_day,$array_jour,$date6->format('D')).'</span> '.str_replace($array_month,$array_mois,$date6->format('j F')).'</div>';

        return $this->render('AppBundle:Agenda:index.html.twig', [
          'listeCreneaux'  => $tab_creneaux,
          'coach'       => $coach,
          'date_next_creneau'   => $date_next_creneau,
          'monday_next_creneau' => $monday_next_creneau,
          'date1'   => $date1,
          'date2'   => $date2,
          'date3'   => $date3,
          'date4'   => $date4,
          'date5'   => $date5,
          'date6'   => $date6,
          'date1_lib'   => $date1_lib,
          'date2_lib'   => $date2_lib,
          'date3_lib'   => $date3_lib,
          'date4_lib'   => $date4_lib,
          'date5_lib'   => $date5_lib,
          'date6_lib'   => $date6_lib,
          'prev_startDate' => $prev_startDate,
          'next_startDate' => $next_startDate,
          'rdv'   => $rdv
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function createRdvAction(Request $request)
    {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        $env = $this->container->get('kernel')->getEnvironment();
        if($session->get('code_acces')){

            $repository = $this->getDoctrine()->getRepository('AppBundle:CodeAcces');
            $datas = $repository->findBy(array('code'=>$session->get('code_acces')));
            if(!$datas || $datas[0]->getCodeUtilise()){

                $session->set('code_acces','');
                $message = "Votre code a déjà été utilisé pour un autre rendez-vous.<br/><br/>
                <button type=\"button\" class=\"btn btn-lg btn-info\" onclick=\"location.href='".$this->generateUrl('homepage')."';\">
                Utiliser un autre code</button>";

                $output = [
                  'has_error' => true,
                  'message' => $message
                ];
                return new JsonResponse($output);
            }else{
                date_default_timezone_set('Europe/Paris');
                setlocale(LC_TIME, 'fr_FR.utf8','fra');
                $date_rdv = date_create_from_format('Ymd Hi',$request->get('date_rdv').' '.$request->get('heure_rdv'));
                $date_rdv_format = ucfirst(strftime("%A %d %B %Y à %Hh%M",$date_rdv->getTimestamp()));
                $array_day = ['Mon','Tue','Wed','Thu','Fri','Sat'];
                $array_jour = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
                $array_month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                $array_mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre'];
                $date_rdv_format = str_replace($array_day,$array_jour,str_replace($array_month,$array_mois,$date_rdv_format));

                $statut = $this->getDoctrine()->getRepository('AppBundle:Statut')->find($this->getParameter('statut_rappel')['en_cours']);

                $cod = $datas[0];

                $coa = $this->getDoctrine()->getRepository('AppBundle:Coach')->find($request->get('coach_id'));

                $rdv_existe = $this->getDoctrine()->getRepository('AppBundle:Rendezvous')->findBy(array(
                  'codeAcces'=>$coa,
                  'statutRdv'=>$statut
                ));
                if($rdv_existe){
                    $rdv = $rdv_existe[0];
                }else{
                    $rdv = new Rendezvous();
                }

                $req = $this->getDoctrine()->getRepository('AppBundle:CodeAcces')->findBy(
                  array(
                    'codeUtilise'=>1,
                    'groupId'=>$cod->getGroupId()
                  ));
                if(count($req)>0)
                    $rdv->setDuree(30);
                else
                    $rdv->setDuree(60);
                $rdv->setCoach($coa);
                $rdv->setCodeAcces($cod);
                $rdv->setDateRdv($date_rdv);
                $rdv->setStatutRdv($statut);
                $rdv->setEmailParticipant($request->get('emailParticipant'));
                $rdv->setTelephoneParticipant($request->get('telephoneParticipant'));
                $rdv->setCpMedecin($request->get('cpMedecin'));
                $rdv->setNomMedecin($request->get('nomMedecin'));
                $rdv->setDateEnvoiCoach(new \DateTime());
                $em->persist($rdv);
                $em->flush();

                $cod->setCodeUtilise(1);
                $em->persist($cod);
                $em->flush();

                $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
                $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
                $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
                $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
                $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
                $statement = $em->getConnection()->prepare('SELECT rdv_token_coach FROM t_rendezvous_rdv WHERE rdv_id = :code');
                $statement->bindValue('code', $rdv->getId());
                $statement->execute();
                $sql_rdv = $statement->fetchAll();

                $url_modif_rdv = 'http://' . $this->container->getParameter('hosts')[$env].$this->generateUrl('app_rdv_update',array('rdv'=>$sql_rdv[0]['rdv_token_coach']));
                $url_coach_rdv = 'http://' . $this->container->getParameter('hosts')[$env].$this->generateUrl('app_rdv_coach_update',array('coach_id'=>$sql_rdv[0]['rdv_token_coach']));
                $url_cgu = 'http://' . $this->container->getParameter('hosts')[$env].$this->generateUrl('app_cgu');
                $url_prpdp= 'http://' . $this->container->getParameter('hosts')[$env].$this->generateUrl('app_mentions_legales');

                $message = \Swift_Message::newInstance()
                  ->setSubject('RDV confirmé le '.$date_rdv_format)
                  ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
                  ->setTo($request->get('emailParticipant'))
                  ->setContentType('text/html')
                  ->setBody($this->renderView('AppBundle:Agenda:send_info_rdv.html.twig', [
                    'url_modif_rdv'=>$url_modif_rdv,
                    'date_rdv'=>$date_rdv_format,
                    'url_cgu'=>$url_cgu,
                    'url_prpdp'=>$url_prpdp
                  ]))
                ;
                $pdfFolder = $this->get('kernel')->getRootDir() . '/../web/pdf/';
                $message->attach(
                  \Swift_Attachment::fromPath($pdfFolder.'checklist.pdf')->setFilename('CheckList DietyCoach.pdf')
                );

                $message2 = \Swift_Message::newInstance()
                  ->setSubject('RDV confirmé le '.$date_rdv_format)
                  ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
                  ->setTo($coa->getEmail())
                  ->setCc($this->getParameter('mail_coach'))
                  ->setContentType('text/html')
                  ->setBody($this->renderView('AppBundle:Agenda:send_info_rdv_coach.html.twig', [
                    'url_coach_rdv'=>$url_coach_rdv,
                    'date_rdv'=>$date_rdv_format
                  ]))
                ;

                if ( $this->get('mailer')->send($message) && $this->get('mailer')->send($message2) ){

                    $message = "Votre rendez-vous est validé.<br/><br/>Vous recevrez un email de confirmation dans quelques instants.<br/><br/>
                    <span style=\"font-size:0.8em;color:grey;\"><i>(Pensez à vérifier votre boîte email, y compris vos courriers indésirables)</i></span>";

                    $output = [
                      'has_error' => false,
                      'message' => $message
                    ];
                    return new JsonResponse($output);
                }
            }

        }else{
            $message = "Votre code a déjà été utilisé pour un autre rendez-vous.<br/><br/>
            <button type=\"button\" class=\"btn btn-lg btn-info\" onclick=\"location.href='".$this->generateUrl('homepage')."';\">
            Utiliser un autre code</button>";

            $output = [
              'has_error' => true,
              'message' => $message
            ];
            return new JsonResponse($output);
        }


        $message = "Un incident technique a été rencontré et nous ne pouvons enregistrer votre demande de rendez-vous.<br/><br/>
        Merci de retentez l'opération dans quelques minutes";

        $output = [
          'has_error' => true,
          'message' => $message
        ];
        return new JsonResponse($output);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function prefRdvAction(Request $request)
    {
        $message = "Vos préférences ont bien été enregistrées.<br/><br/>Nous vous contacterons par email dès que possible en vous proposant de nouveaux créneaux";

        $output = [
          'has_error' => true,
          'message' => $message
        ];
        return new JsonResponse($output);
    }

    public function relanceEmailAction(Request $request)
    {
        $rdv = $this->getDoctrine()->getRepository('AppBundle:Rendezvous')->find($request->get('id_rdv'));
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
        $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
        $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
        $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
        $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
        $statement = $em->getConnection()->prepare('SELECT rdv_token_coach FROM t_rendezvous_rdv WHERE rdv_id = :code');
        $statement->bindValue('code', $rdv->getId());
        $statement->execute();
        $sql_rdv = $statement->fetchAll();

        $date_rdv_format = ucfirst(strftime("%A %d %B %Y à %Hh%M",$rdv->getDateRdv()->getTimestamp()));
        $array_day = ['Mon','Tue','Wed','Thu','Fri','Sat'];
        $array_jour = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $array_month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $array_mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre'];
        $date_rdv_format = str_replace($array_day,$array_jour,str_replace($array_month,$array_mois,$date_rdv_format));

        $env = $this->container->get('kernel')->getEnvironment();
        $url_modif_rdv = 'http://' . $this->container->getParameter('hosts')[$env] . $this->generateUrl('app_rdv_update', array('rdv' => $sql_rdv[0]['rdv_token_coach']));
        $url_coach_rdv = 'http://' . $this->container->getParameter('hosts')[$env] . $this->generateUrl('app_rdv_coach_update', array('coach_id' => $sql_rdv[0]['rdv_token_coach']));
        $url_cgu = 'http://' . $this->container->getParameter('hosts')[$env] . $this->generateUrl('app_cgu');
        $url_prpdp = 'http://' . $this->container->getParameter('hosts')[$env] . $this->generateUrl('app_mentions_legales');

        $pdfFolder = $this->get('kernel')->getRootDir() . '/../web/pdf/';
        $message = \Swift_Message::newInstance()
          ->setSubject('RDV confirmé le ' . $date_rdv_format)
          ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
          ->setTo($rdv->getEmailParticipant())
          ->setContentType('text/html')
          ->setBody($this->renderView('AppBundle:Agenda:send_info_rdv.html.twig', [
            'url_modif_rdv' => $url_modif_rdv,
            'date_rdv' => $date_rdv_format,
            'url_cgu' => $url_cgu,
            'url_prpdp' => $url_prpdp
          ]));
        $message->attach(
          \Swift_Attachment::fromPath($pdfFolder.'checklist.pdf')->setFilename('CheckList DietyCoach.pdf')
        );
        $output = [
          'has_error' => true,
          'message' => ''
        ];
        if ( $this->get('mailer')->send($message) ){

            $message = "Votre rendez-vous est validé.<br/><br/>Vous recevrez un email de confirmation dans quelques instants.<br/><br/>
			<span style=\"font-size:0.8em;color:grey;\"><i>(Pensez à vérifier votre boîte email, y compris vos courriers indésirables)</i></span>";

            $output = [
              'has_error' => false,
              'message' => $message
            ];

        }
        return new JsonResponse($output);
    }
}
