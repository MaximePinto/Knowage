<?php
namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Entity\Formation;

class Mailer
{
    protected $mailer;
    protected $templating;
    private $from = 'romain.poilpret@gmail.com';
    private $to = 'romain.poilpret@gmail.com';
    private $toAdmin = 'pellecuer.david@gmail.com';



    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    protected function sendMail($subject, $body, $to, $reply)
    {
        $mail = \Swift_Message::newInstance();

        $mail
            ->setFrom($this->from)
            ->setTo($to)
            ->setSubject($subject)
            ->setReplyTo($reply)
            ->setBody($body)
            ->setContentType('text/html');

        $this->mailer->send($mail);
    }

    public function sendContactMail($message, $reply)
    {

        $to = $this->to;
        $subject = 'Demande de contact';
        $body = $this->templating->render('Mail/contactMail.html.twig', array(
            'message' => $message
        ));
        $this->sendMail($subject, $body, $to, $reply);
    }


}