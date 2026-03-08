<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AssetDeclaration;
use App\Entity\PublicOfficial;
use App\Model\Dto\AnnouncementParserDto;
use App\OpenDataAnalyzer\OpenDataAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\UnexpectedValueException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AnnouncementParserController extends AbstractController
{
    public function __construct(
        private readonly OpenDataAnalyzer $openDataAnalyzer,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/announcement-parser', name: 'app_announcement_parser')]
    public function __invoke(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('input', TextareaType::class)
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $input = $form->getData()['input'];
            $output = $this->openDataAnalyzer->parseRawInputToAnnouncementParserDtoJson($input);
            $dto = $this->serializer->deserialize($output, AnnouncementParserDto::class, 'json');
            $publicOfficial = $this->entityManager->getRepository(PublicOfficial::class)->findOneBy([
                'firstName' => $dto->publicOfficial->firstName,
                'lastName' => $dto->publicOfficial->lastName,
            ]);
            if ($publicOfficial === null) {
                $publicOfficial = $dto->publicOfficial;
                $this->entityManager->persist($publicOfficial);
                $this->entityManager->flush();
            }
            $assetDeclaration = $dto->assetDeclaration;
            $assetDeclaration->publicOfficial = $publicOfficial;

            $this->entityManager->persist($assetDeclaration);
            $this->entityManager->flush();

            return $this->render('announcement_parser/index.html.twig', [
                'form' => $form->createView(),
                'lastDeclarations' => $this->getLastAssetDeclarations(),
            ]);
        }

        return $this->render('announcement_parser/index.html.twig', [
            'form' => $form->createView(),
            'lastDeclarations' => $this->getLastAssetDeclarations(),
        ]);
    }

    /**
     * @return array<AssetDeclaration>
     */
    private function getLastAssetDeclarations(): array
    {
        return $this->entityManager->getRepository(AssetDeclaration::class)->findBy(
            criteria: [],
            orderBy: ['id' => 'DESC'],
            limit: 10,
        );
    }
}
