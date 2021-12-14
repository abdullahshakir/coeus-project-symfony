<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderRepository;
use App\Service\MailService;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'app:review-collect',
    description: 'Collect review when order is delivered',
)]
class ReviewCollectCommand extends Command
{
    /**
     * @var EntityManagerInterface 
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var MailService
     */
    private $mailService;

    private $router;

    public function __construct(EntityManagerInterface $entityManager, OrderRepository $orderRepository, MailService $mailService, RouterInterface $router)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->mailService = $mailService;
        $this->router = $router;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $orders = $this->orderRepository->findNonNotifiedDeliveredOrders();

        $context = $this->router->getContext();
        $context->setHost('coeusexpress.wip');
        $context->setScheme('https');

        foreach ($orders as $order) {
            $user = $order->getUser();
            $this->mailService->sendMail([
                'subject' => 'Order Delivered',
                'from' => 'noreply@coeusexpress.wip',
                'to' => $user->getEmail(),
                'context' => [
                    'user' => $user,
                    'reviewUrl' => $this->router->generate('seller_review', ['token' => $order->getToken()])
                ],
                'template' => 'email/order-review.html.twig'
            ]);
            $order->setIsNotified(true);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
        
        return Command::SUCCESS;
    }
}
