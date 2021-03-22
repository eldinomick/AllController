<?php

namespace App\Controller;

use App\Repository\ApiRepository;
use App\Repository\ResultatsRepository;
use App\Repository\WordRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// $entities = [];
// $DirEntities = scandir('../src/Entity');
// foreach ($DirEntities as $entitie) {
//     if ($entitie != '.' && $entitie != '..' && substr($entitie, 0, 1) != '.') {
//         $realEntitie = substr($entitie, 0, - (strlen('.php')));
//         if (!in_array($realEntitie, explode(',', $_ENV['CONTROLLER_NOT_UNIVERSAL']))) {
//             $entities[] = $realEntitie;
//             $cname = "App\\Entity\\" . $realEntitie;
//             $$realEntitie = new $cname;
//         }
//     }
// }

/**
 * @Route("/all")
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
class AllController extends AbstractController
{
    protected $word, $api, $resultats;

    public function __construct(WordRepository $word, ApiRepository $api, TransactionRepository $transaction, ResultatsRepository $resultats)
    {
        $this->word = $word;
        $this->api = $api;
        $this->transaction = $transaction;
        $this->resultats = $resultats;
    }

    /**
     * @Route("/{part}", name="all_index", methods={"GET"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */

    public function index($part): Response
    {
        $alls = count(
            $this->$part->findAll()
        ) ? $this->$part->findAll() : '';
        return $this->render('all/index.html.twig', [
            'alls' => $alls,
            'part' => $part
        ]);
    }

    /**
     * @Route("/new/{part}", name="all_new", methods={"GET","POST"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function new(Request $request, $part): Response
    {
        $Call = 'App\\Entity\\' . ucfirst($part);
        $Call = new $Call();
        $form = $this->createForm('App\\Form\\' . ucfirst($part) . 'Type'::class, $Call);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($Call);
            $entityManager->flush();

            return $this->redirectToRoute('all_index', ['part' => $part]);
        }

        return $this->render('all/new.html.twig', [
            'part' => $part,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{part}", name="all_show", methods={"GET"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function show($id, $part): Response
    {

        return $this->render('all/show.html.twig', [
            'all' => $this->$part->findOneBy(['id' => $id]),
            'part' => $part
        ]);
    }


    /**
     * @Route("/edit/{id}/{part}", name="all_edit", methods={"GET","POST"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function edit(Request $request, $id, $part): Response
    {
        $Call = $this->$part->findOneBy(['id' => $id]);
        $form = $this->createForm('App\\Form\\' . ucfirst($part) . 'Type'::class, $Call);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('all_index', ['part' => $part]);
        }

        return $this->render('all/edit.html.twig', [
            'all' => $this->$part->findOneBy(['id' => $id]),
            'part' => $part,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="all_delete", methods={"DELETE"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function delete(Request $request, $id): Response
    {
        $part = $request->query->get('part');
        $Call = $this->$part->findOneBy(['id' => $id]);
        if ($this->isCsrfTokenValid('delete' . $Call->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($Call);
            $entityManager->flush();
        }

        return $this->redirectToRoute('all_index', ['part' => $part]);
    }
    public function &factory($className)
    {

        if (class_exists($className)) return new $className;
        die('Cannot create new "' . $className . '" class - includes not found or class unavailable.');
    }
}
