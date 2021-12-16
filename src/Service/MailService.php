<?php 

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMail($params)
    {
        $email = (new TemplatedEmail())
            ->subject($params['subject'] ?? 'Subject')
            ->from($params['from'] ?? 'noreply@coeusexpress.wip')
            ->to($params['to'])
            ->context($params['context'] ?? [])
            ->htmlTemplate($params['template']);
        
        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}