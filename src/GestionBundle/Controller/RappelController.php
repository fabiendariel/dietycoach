<?php

namespace GestionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\AccessConcurrent;

use AppBundle\Form\RappelType;
use AppBundle\Form\SearchRappelType;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class RappelController extends Controller
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

        $form = $this->get('form.factory')->create(SearchRappelType::class, $search);

        return $this->render('GestionBundle:Rappel:list.html.twig', [
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

        if($data->getStatutRappel()->getId() == $this->container->getParameter('statut_rappel')['traite']){
            $this->get('session')->getFlashBag()->add('inline_error', 'Ce rappel a déjà été traité');
            return $this->redirect($this->generateUrl('app_rappel_list'));
        }elseif(count($data->getAccess()) > 0){
            foreach($data->getAccess() as $acces){
                if($acces->getUtilisateur() == $this->getUser()){
                    $acces->setDate(new \DateTime);
                    $em->flush();
                }else{
                    $this->get('session')->getFlashBag()->add('inline_error', 'Ce rappel est actuellement traité par un autre intervenant');
                    return $this->redirect($this->generateUrl('app_rappel_list'));
                }
            }
        }else{
            //Ajout access concurrent
            $access = new AccessConcurrent();
            $access->setRappel($data);
            $access->setUtilisateur($this->getUser());
            $access->setDate(new \DateTime);
            $em->persist($access);
            $em->flush();
        }

        $form = $this->get('form.factory')->create(RappelType::class, $data);

        if ($form->handleRequest($request)->isValid()){

            $em = $this->getDoctrine()->getManager();
            $data->setStatutRappel($em->getRepository('AppBundle:ContactStatut')->find(2));
            $em->persist($data);
            $em->flush();

            $request->getSession()->getFlashBag()->add('success', 'Le rappel a été mis à jour');

            return $this->redirectToRoute('app_rappel_list');
        }

        return $this->render('GestionBundle:Rappel:rappel.html.twig', array(
            'rappel' => $data,
            'form'   => $form->createView()
        ));
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function rappelAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $datas = $em->getRepository('AppBundle:Contact')->findBy(
            array(),
            array('id' => 'DESC')
        );

        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("liuggio")
            ->setLastModifiedBy("Covidom")
            ->setTitle("Extraction")
            ->setSubject("Extraction")
            ->setDescription("Extraction")
            ->setKeywords("Extraction")
            ->setCategory("Extraction")
        ;

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'DATE SAISIE')
            ->setCellValue('B1', 'NOM PATIENT')
            ->setCellValue('C1', 'PRENOM PATIENT')
            ->setCellValue('D1', 'DATE NAISSANCE')
            ->setCellValue('E1', 'SEXE')
            ->setCellValue('F1', 'TRAITANT?')
            ->setCellValue('G1', 'NOM TRAITANT')
            ->setCellValue('H1', 'PRENOM TRAITANT')
            ->setCellValue('I1', 'EMAIL TRAITANT')
            ->setCellValue('J1', 'NUMERO TRAITANT')
            ->setCellValue('K1', 'CODE POSTAL')
            ->setCellValue('L1', 'COMMENTAIRE')
            ->setCellValue('M1', 'DATE TRAITEMENT')
            ->setCellValue('N1', 'COMMENTAIRE TRAITEMENT')
        ;

        $row = 2;

        foreach($datas as $data){
            $phpExcelObject->getActiveSheet()
                ->setCellValue('A'.$row, $data->getDateSaisie()->format('d/m/Y H:i'))
                ->setCellValue('B'.$row, $data->getNom())
                ->setCellValue('C'.$row, $data->getPrenom())
                ->setCellValue('D'.$row, $data->getDateNaissance()->format('d/m/Y H:i'))
                ->setCellValue('E'.$row, $data->getSexe())
                ->setCellValue('F'.$row, $data->getTraitant() == 1 ? 'Oui' : 'Non')
                ->setCellValue('G'.$row, $data->getNomTraitant())
                ->setCellValue('H'.$row, $data->getPrenomTraitant())
                ->setCellValue('I'.$row, $data->getEmailTraitant())
                ->setCellValue('J'.$row, $data->getNumeroTraitant())
                ->setCellValue('K'.$row, $data->getCodePostalTraitant())
                ->setCellValue('L'.$row, $data->getCommentaire())
                ->setCellValue('M'.$row, $data->getDateTraitement() == null ? '' : $data->getDateTraitement()->format('d/m/Y') )
                ->setCellValue('N'.$row, $data->getCommentaireTraitement())
            ;
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

        $phpExcelObject->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);
        $phpExcelObject->getActiveSheet()->setTitle('Résultat');
        $phpExcelObject->setActiveSheetIndex(0);


        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Export_Connect_Covidom.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }


    // -----------------------------------------------------------------------------------------------------------------

    public function accessAction(Request $request)
    {

        if ($request->isXmlHttpRequest()) {
            $data = null;

            $rappel = $this
                ->getDoctrine()->getRepository('AppBundle:Contact')
                ->find($request->get('rappel_id'));

            if ($rappel) {
                $em = $this->getDoctrine()->getManager();
                $access = $this
                    ->getDoctrine()->getRepository('AppBundle:AccessConcurrent')
                    ->findBy([
                        'avis' => $rappel->getId(),
                        'utilisateur' => $this->getUser()->getId()
                    ]);

                foreach($access as $acces){
                    $acces->setDate(new \DateTime);
                    $em->flush();
                }
            }
        }

        return new JsonResponse($data);
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function deleteAccessAction(Request $request)
    {
        $data = null;

        if ($request->isXmlHttpRequest()) {
            $data = null;

            $rappel = $this
                ->getDoctrine()->getRepository('AppBundle:Contact')
                ->find($request->get('rappel_id'));

            if ($rappel) {
                $em = $this->getDoctrine()->getManager();
                $access = $this
                    ->getDoctrine()->getRepository('AppBundle:AccessConcurrent')
                    ->findBy([
                        'rappel' => $rappel->getId(),
                        'utilisateur' => $this->getUser()->getId()
                    ]);

                foreach($access as $acces){
                    $em->remove($acces);
                    $em->flush();
                }
            }
        }

        return new JsonResponse($data);
    }

}
