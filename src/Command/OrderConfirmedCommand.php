<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrderRepository;
use App\Service\MailService;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(
    name: 'app:order-confirmed',
    description: 'Send order confirmation mail when order is moved to inprogress by seller',
)]
class OrderConfirmedCommand extends Command
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
        $io = new SymfonyStyle($input, $output);
        $orders = $this->orderRepository->findConfirmedOrders();

        if (count($orders) == 0) {
            $io->info('No confirmed orders.');
            return Command::SUCCESS;
        }

        foreach ($orders as $order) {
            $user = $order->getUser();
            try {
                $this->mailService->sendMail([
                    'subject' => 'Order Confirmed',
                    'from' => 'noreply@coeusexpress.wip',
                    'to' => $user->getEmail(),
                    'context' => [
                        'user' => $user,
                        'cart' => $order
                    ],
                    'template' => 'email/order-confirmation.html.twig'
                ]);
                $order->setIsConfirmed(true);
                $this->entityManager->flush();
                $this->entityManager->clear();
            } catch (\Exception $e) {
                $io->error('Unable to send mail!');
                return Command::FAILURE;
            }
        }
        
        return Command::SUCCESS;
    }
}
