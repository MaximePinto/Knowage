<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\Mailer;


class FrontController extends controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function homepageAction(Request $request)
    {
        $form = $this->createForm('AppBundle\Form\SearchFormationType');
        $form->handleRequest($request);

        return $this->render('Front/index.html.twig', array(
            'form'=>$form->createView()
        ));
    }

    /**
     * @Route("/search", name="search")
     */
    public function searchPageAction()
    {
        $searchWord = 'author';
        $em = $this->getDoctrine()->getManager();
        $formations = $em->getRepository('AppBundle:Formation')->findBy([$searchWord => '3']);
        return $this->render('Front/search.html.twig', array(
        'formations' => $formations,
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

            return $this->redirectToRoute('search');
     }


        return $this->render('Front/contact.html.twig', array(
            'form'=>$form->createView()
        ));
    }

    /**
     * @Route("/teacher", name="landingformateur")
     */
    public function landingFormateurAction()
    {
        return $this->render('Front/landingFormateur.html.twig');

    }
}