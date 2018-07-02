<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Formation;
use AppBundle\Entity\Category;
use AppBundle\Entity\Rating;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\Mailer;
use AppBundle\Form\ContactTeacherType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class FrontController extends controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        return $this->render('Front/index.html.twig', array(
            'categories' => $categories,
        ));
    }


    /**
     * @Route("/search", name="search")
     * @Method({"GET", "POST"})
     */
    public function searchPageAction(Request $request)
    {

        $user = $this->getUser();
        $formations = null;
        $searchs = '';


        if ($request->query->get('category_id')) {

            $id = $request->query->get('category_id');
            $query = $this->getDoctrine()->getRepository(Formation::class)->findBy(['category' => $id]);

            $paginator = $this->get('knp_paginator');

            $formations = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                4
            );
        }


        if ($request->query->get('search')) {

            $searchs = explode(' ', trim($request->query->get('search')));

            $repository = $this->getDoctrine()->getRepository(Formation::class);
            $formations = $repository->findFormation($searchs);

            $paginator = $this->get('knp_paginator');
            $formations = $paginator->paginate(
                $formations,
                $request->query->getInt('page', 1),
                3
            );

        }

        return $this->render('Front/search.html.twig', array(
            'formations' => $formations,
            'user' => $user,
            'search' => $searchs,
        ));

    }


    /**
     * @Route("/contact", name="contact")
     *
     */
    public function contactAction(Request $request, Mailer $mailer)
    {
        $form = $this->createForm('AppBundle\Form\ContactType');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $firstname = $data['firstname'];
            $name = $data['name'];
            $email = $data['email'];
            $message = $data['message'];

            $mailer->sendContactMail($message, $email);

            $this->addFlash('success', 'Formulaire envoyé !');

            return $this->redirectToRoute('contact');
        }


        return $this->render('Front/contact.html.twig', array(
            'form' => $form->createView()
        ));
    }


    /**
     * @Route("/favorite", name="favorite")
     * @Method({"GET", "POST"})
     */
    public function favoriteAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($request->query->get('formationId')) {
            $formationId = $request->query->get('formationId');
            $favorited = $request->query->get('favorited');
            $formation = $em->getRepository('AppBundle:Formation')->find(['id' => $formationId]);
            if (!$favorited) {
                $user->addFavoriteFormation($formation);
            } elseif ($favorited) {
                $user->removeFavoriteFormation($formation);
            }
        }

        $em->flush();
        return new Response();
    }

    /**
     * @Route("/formateur", name="formateur")
     */
    public function landingFormateurAction()
    {
        return $this->render('Front/formateur.html.twig');
    }


    /**
     * Finds and displays a formation entity.
     *
     * @Route("/show/{id}", name="landing_formation")
     * @Method({"GET", "POST"})
     */
    public function landingAction(Request $request, Formation $formation, Mailer $mailer)
    {

        $form = $this->createForm(ContactTeacherType::class);
        $form->handleRequest($request);
        $shortText = $formation->shortText(250);

        //Affichage de la note moyenne
        $em = $this->getDoctrine()->getManager();
        $averageRateArray = $em->getRepository('AppBundle:Rating')->findBy(
            ['formation' => $formation],
            ['rate' => 'ASC']
        );
        $averagerate = 0;
        $arrayrate = [];

        for ($i = 0; $i < count($averageRateArray); $i++) {
            $arrayrate[] = $averageRateArray[$i]->getRate();
        }

        if (count($arrayrate)) {
            $arrayrate = array_filter($arrayrate);
            $averagerate = array_sum($arrayrate) / count($arrayrate);
        }
        //Fin d'affichage de la note moyenne

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $email = $data['email'];
            $subject = $data['objet'];
            $message = $data['message'];
            $to = $formation->getAuthor()->getEmail();


            $mailer->sendTeacherMail('romain.poilpret@gmail.com', $message, $subject, $email);

            return $this->redirectToRoute('landing_formation', array(
                'id' => $formation->getId(),

            ));
        }

        return $this->render('Formation/landing_formation.html.twig', array(
            'formation' => $formation,
            'form' => $form->createView(),
            'shortText' => $shortText,
            'average' => $averagerate,

        ));
    }


    /**
     * Rates a formation.
     *
     * @Route("/formation/{formation_id}/rating/{rate}", name="rating", requirements={"formation_id": "\d+"})
     * @Security("has_role('ROLE_USER')")
     *
     * @ParamConverter("formation", options={"mapping": {"formation_id": "id"}})
     *
     * @Method({"GET", "POST"})
     */
    public function ratingAction(Formation $formation, $rate, Mailer $mailer)
    {
        $user = $this->getUser();
        $userAlreadyRate = $this->getDoctrine()
            ->getRepository(Rating:: class)
            ->findOneBy([
                'user' => $user,
                'formation' => $formation,
            ]);

        if (!$userAlreadyRate) {

            $rating = new Rating();
            $rating->setUser($user);
            $rating->setFormation($formation);
            $rating->setRate($rate);

            $em = $this->getDoctrine()->getManager();
            $formation = $em->getRepository('AppBundle:Formation')->find($formation);
            $em->persist($rating);
            $em->flush();

            //send Formateur if badRate
            $subject = 'Un utilisateur a attribué une note inférieure à 3 pour votre formation';
            $to = $formation->getAuthor()->getEmail();
            $userWhoRates = $this->getUser();
                if ($rate and $rate <3){
                    $mailer->sendBadRanking($to, $subject, $userWhoRates, $formation);
                }

            $message = 'Vous avez attribué la note de ' .  $rate . ' / 5 à cette formation. Merci de votre contribution';
            $this->addFlash('alert', $message);
            return $this->redirectToRoute('landing_formation', array(
                'id' => $formation->getId(),
            ));
        } else {
            $this->addFlash('danger', 'Vous avez déjà noté cette formation');
        } return $this->redirectToRoute('landing_formation', array(
        'id' => $formation->getId(),
    ));
    }


}