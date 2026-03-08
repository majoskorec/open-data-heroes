<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssetDeclaration;
use App\Entity\PublicOfficial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PublicOfficialDetailController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(
        path: '/funkcionar/{id}',
        name: 'app_public_official_detail',
        requirements: ['id' => '\d+'],
    )]
    public function __invoke(PublicOfficial $publicOfficial): Response
    {
        $declarations = $this->entityManager->getRepository(AssetDeclaration::class)->findBy(
            criteria: ['publicOfficial' => $publicOfficial],
            orderBy: ['year' => 'DESC'],
        );

        return $this->render('public_official_detail/index.html.twig', [
            'publicOfficial' => $publicOfficial,
            'declarations' => $declarations,
        ]);
    }
}
