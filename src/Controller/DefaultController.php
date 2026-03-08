<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PublicOfficial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DefaultController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(
        path: '/',
        name: 'app_default_index',
    )]
    public function index(): Response
    {
        $publicOfficials = $this->entityManager->getRepository(PublicOfficial::class)->findAll();

        return $this->render('default/index.html.twig', [
            'publicOfficials' => $publicOfficials,
        ]);
    }
}
