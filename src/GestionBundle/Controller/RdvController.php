<?php

namespace GestionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class RdvController extends Controller
{

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Liste paginée des rappels
     * @param $page
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request, $page)
    {
        $page = (1 > $page) ? 1 : $page;


        $search = null;
        $session = $this->get('session');
        // Taille de la pagination depuis les configs
        $nbPerPage = 20;

        $repository = $this->getDoctrine()->getRepository('AppBundle:Contact');
        $datas = $repository->getPaginatedResult($page, $nbPerPage, null);


        if ($request->get('reset')) {
            $session->set('search_rappel', null);
            return $this->redirectToRoute('app_rappel_list');
        }
        // Filtre les cibles suivant les criteres de recherche sinon récupére toutes les cible
        if ($request->get('search_rappel') != null) {
            $search = $request->get('search_rappel');
            $session->set('search_rappel', $search);
            $datas = $repository->getPaginatedResult($page, $nbPerPage, $search);
            $results = $repository->getNbResult($search);
        } elseif ($session->get('search_rappel') != null) {
            $search = $session->get('search_rappel');
            $datas = $repository->getPaginatedResult($page, $nbPerPage, $search);
            $results = $repository->getNbResult($search);
        } else {
            $datas = $repository->getPaginatedResult($page, $nbPerPage, null);
            $results = $repository->getNbResult(null);
        }

        $nbPages = ceil(count($datas) / $nbPerPage);
        $nbResults = count($results);


        // Si la page n'existe pas, on retourne sur la première page
        if (0 !== (int)$nbPages && $page > $nbPages)
        {
            return $this->redirect($this->generateUrl('app_rappel_list', ['page' => 1]));
        }

        if($search != null){

            if($search['origineRappel'] == ''){
                unset($search['origineRappel']);
            }

            if(isset($search['dateRappel']) && $search['dateRappel'] != ''){
                $search['dateRappel'] = date_create_from_format('Y-m-d H:i:s', $search['dateRappel'] . ' 00:00:00');
            }else{
                unset($search['dateRappel']);
            }

            if(isset($search['dateCreation']) && $search['dateCreation'] != ''){
                $search['dateCreation'] = date_create_from_format('Y-m-d H:i:s', $search['dateCreation'] . ' 00:00:00');
            }else{
                unset($search['dateCreation']);
            }

        }

        $form = $this->get('form.factory')->create(SearchRdvType::class, $search);

        return $this->render('AppBundle:Rdv:list.html.twig', [
          'datas'   => $datas,
          'form'    => $form->createView(),
          'nbPages' => $nbPages,
          'page'    => $page,
          'nbResults' => $nbResults
        ]);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function detailAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $data = $em->getRepository('AppBundle:Contact')->find($request->get('data_id'));
        if (null === $data) {
            throw new NotFoundHttpException("Le rappel n'existe pas.");
        }

        $form = $this->get('form.factory')->create(RdvType::class, $data);

        if ($form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $data->setStatutRappel($em->getRepository('AppBundle:ContactStatut')->find(2));
            $em->persist($data);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le rappel a été mis à jour');

            return $this->redirectToRoute('app_rappel_list');
        }

        return $this->render('AppBundle:Rappel:rappel.html.twig', array(
            'rappel' => $data,
            'form'   => $form->createView()
        ));
    }


    // -----------------------------------------------------------------------------------------------------------------

    public function updateRdvAction(Request $request)
    {
        date_default_timezone_set('Europe/Paris');
        setlocale(LC_TIME, 'fr_FR.utf8','fra');
        $session = $this->get('session');
        if($session->get('coach')=='')
            $session->set('coach',$request->get('coach_id'));

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
        $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
        $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
        $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
        $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
        $statement = $em->getConnection()->prepare('SELECT rdv_id FROM t_rendezvous_rdv WHERE rdv_token_coach = :code');
        $statement->bindValue('code', $request->get('coach_id'));
        $statement->execute();
        $sql_rdv = $statement->fetchAll();
        if (count($sql_rdv)==0) {
            return $this->render('GestionBundle:Rdv:update.rien.html.twig');
        }
        $rdv = $this->getDoctrine()->getRepository('AppBundle:Rendezvous')->find($sql_rdv[0]['rdv_id']);
        if (null === $rdv) {
            return $this->render('GestionBundle:Rdv:update.rien.html.twig');
        }
        else{
            date_default_timezone_set('Europe/Paris');
            setlocale(LC_TIME, 'fr_FR.utf8','fra');
            $date_rdv_format = ucfirst(strftime("%A %d %B %Y",$rdv->getDateRdv()->getTimestamp()));
            $array_day = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
            $array_jour = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi'];
            $array_month = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $array_mois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre'];
            $date_rdv_format=str_replace($array_month,$array_mois,str_replace($array_day,$array_jour,$date_rdv_format));

            $h_debut = strftime("%Hh%M",$rdv->getDateRdv()->getTimestamp());
            $h_fin = strftime("%Hh%M",$rdv->getDateRdv()->getTimestamp()+($rdv->getDuree()*60));
            if ($this->getParameter('statut_rappel')['traite'] === $rdv->getStatutRdv()->getId()) {
                return $this->render('GestionBundle:Rdv:update.traite.html.twig', array(
                  'rdv' => $rdv,
                  'date_rdv_format' => $date_rdv_format,
                  'h_debut' => $h_debut,
                  'h_fin' => $h_fin,
                  'id_rdv' => $rdv->getId()
                ));
            }else{
                return $this->render('GestionBundle:Rdv:update.html.twig', array(
                  'rdv' => $rdv,
                  'date_rdv_format' => $date_rdv_format,
                  'h_debut' => $h_debut,
                  'h_fin' => $h_fin,
                  'id_rdv' => $rdv->getId()
                ));
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function updateRdvConfirmAction(Request $request)
    {
        $session = $this->get('session');
        $em = $this->getDoctrine()->getManager();
        if($session->get('coach')){
            $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
            $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
            $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
            $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
            $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
            $statement = $em->getConnection()->prepare('SELECT rdv_id FROM t_rendezvous_rdv WHERE rdv_token_coach = :code');
            $statement->bindValue('code', $session->get('coach'));
            $statement->execute();
            $sql_rdv = $statement->fetchAll();
            if (count($sql_rdv)>=0) {
                $rdv = $this->getDoctrine()->getRepository('AppBundle:Rendezvous')->find($sql_rdv[0]['rdv_id']);
                if(!$rdv || $rdv->getStatutRdv()->getId() != $this->getParameter('statut_rappel')['en_cours']){
                    $message = "Le statut de ce RDV a déjà été déclaré. Vous ne pouvez plus intervenir sur celui-ci.";
                    $output = [
                      'has_error' => true,
                      'message' => $message
                    ];
                }else{
                    $action = $request->get('option');

                    if($action == 'del'){
                        $statut = $this->getDoctrine()->getRepository('AppBundle:Statut')->find($this->getParameter('statut_rappel')['annule']);
                    }else{
                        $statut = $this->getDoctrine()->getRepository('AppBundle:Statut')->find($this->getParameter('statut_rappel')['traite']);

                        $env = $this->container->get('kernel')->getEnvironment();
                        $statement = $em->getConnection()->prepare('SELECT rdv_token_satisfaction FROM t_rendezvous_rdv WHERE rdv_id = :id');
                        $statement->bindValue('id', $rdv->getId());
                        $statement->execute();
                        $sql_rdv = $statement->fetchAll();
                        $url_satisfaction = 'http://' . $this->container->getParameter('hosts')[$env].$this->generateUrl('app_satisfaction',array('token'=>$sql_rdv[0]['rdv_token_satisfaction']));
                        $url_home = 'http://' . $this->container->getParameter('hosts')[$env].$this->generateUrl('homepage');
                        $message2 = \Swift_Message::newInstance()
                            ->setSubject('Suite à votre RDV avec votre diététicien')
                            ->setFrom($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name'))
                            ->setTo($rdv->getEmailParticipant())
                            ->setContentType('text/html')
                            ->setBody($this->renderView('GestionBundle:Rdv:send_info_rdv_realise.html.twig', [
                                'url_home'=>$url_home,
                                'url_satisfaction'=>$url_satisfaction
                            ]))
                        ;
                        $this->get('mailer')->send($message2);
                        $rdv->setDateSatisfaction(new \DateTime());
                    }
                    $rdv->setStatutRdv($statut);
                    $rdv->setDateCloture(new \DateTime());
                    $em->persist($rdv);
                    $em->flush();

                    $cod = $rdv->getCodeAcces();
                    $cod->setCodeUtilise(1);
                    $em->persist($cod);
                    $em->flush();

                    $message = "Le statut final de ce rendez-vous est enregistré.<br/><br/>Merci pour votre retour.<br/><br/>
                    <span style=\"font - size:0.8em;color:grey;\"><i>Vous pouvez désormais fermer cette page.</i></span>";
                    $output = [
                      'has_error' => false,
                      'message' => $message
                    ];
                }
            }else {
                $message = "Le statut de ce RDV a déjà été déclaré. Vous ne pouvez plus intervenir sur celui-ci.";
                $output = [
                  'has_error' => true,
                  'message' => $message
                ];
            }
        }else{
            $message = "Un incident technique a été rencontré et nous ne pouvons enregistrer votre demande de modification.<br/><br/>
            Merci de contacter le service client Dietycoach";
            $output = [
              'has_error' => true,
              'message' => $message
            ];
        }



        $session->set('coach','');
        return new JsonResponse($output);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function satisfactionRdvAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->prepare('SET ANSI_NULLS ON')->execute();
        $em->getConnection()->prepare('SET ANSI_WARNINGS ON')->execute();
        $em->getConnection()->prepare('SET CONCAT_NULL_YIELDS_NULL ON')->execute();
        $em->getConnection()->prepare('SET ANSI_PADDING ON')->execute();
        $em->getConnection()->prepare('SET QUOTED_IDENTIFIER ON')->execute();
        $statement = $em->getConnection()->prepare('SELECT rdv_id FROM t_rendezvous_rdv WHERE rdv_token_satisfaction = :code');
        $statement->bindValue('code', $request->get('token'));
        $statement->execute();
        $sql_rdv = $statement->fetchAll();
        if (count($sql_rdv)==0) {
            return $this->render('GestionBundle:Rdv:satisfaction.rien.html.twig');
        }
        $rdv = $this->getDoctrine()->getRepository('AppBundle:Rendezvous')->find($sql_rdv[0]['rdv_id']);
        if (null === $rdv) {
            return $this->render('GestionBundle:Rdv:satisfaction.rien.html.twig');
        }
        else{

            $datas = $request->get('form');

            if ($datas){

                $sql = "INSERT INTO t_satisfaction_sas ([sas_rdv_id],[sas_date],[sas_prise_en_charge],[sas_session],[sas_plateforme],[sas_recommendation],[sas_amelioration])
                VALUES (:id_rdv,GETDATE(),:question_1,:question_2,:question_3,:question_4,:question_5);";
                $statement = $em->getConnection()->prepare($sql);
                $statement->bindValue('id_rdv', $sql_rdv[0]['rdv_id']);
                $statement->bindValue('question_1', $datas['question_q1']);
                $statement->bindValue('question_2', $datas['question_q2']);
                $statement->bindValue('question_3', $datas['question_q3']);
                $statement->bindValue('question_4', $datas['question_q4']);
                $statement->bindValue('question_5', $datas['question_q5']);
                $statement->execute();

                return $this->render('GestionBundle:Rdv:satisfaction.remerciements.html.twig');

            }

            return $this->render('GestionBundle:Rdv:satisfaction.html.twig');
        }
    }

}
