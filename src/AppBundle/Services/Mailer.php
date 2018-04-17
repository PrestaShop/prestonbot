<?php

namespace AppBundle\Services;

class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Builds and send a message to a recipient.
     *
     * @param string       $subject      The message subject
     * @param string|array $sender       The list of senders
     * @param string|array $recipient    The list of recipients
     * @param string       $templateName The template logical name
     * @param array        $params       The template variables
     * @param null|mixed   $bcc
     */
    public function send(
        $subject,
        $sender,
        $recipient,
        $templateName,
        array $params,
        $bcc = null
    ) {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($sender)
            ->setTo($recipient)
            ->setContentType('text/html')
            ->setBody($this->twig->render($templateName, $params))
        ;

        if (null !== $bcc) {
            $message->setBcc($bcc);
        }

        return $this->mailer->send($message);
    }
}
