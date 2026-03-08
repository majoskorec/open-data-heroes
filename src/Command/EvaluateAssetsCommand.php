<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AssetDeclaration;
use App\Entity\MovableAssetValuation;
use App\Entity\RealEstateValuation;
use App\OpenDataAnalyzer\OpenDataAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'z:app:assets:evaluate', description: 'Evaluate assets')]
final class EvaluateAssetsCommand extends Command
{
    public function __construct(
        private readonly OpenDataAnalyzer $openDataAnalyzer,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    public function __invoke(#[Argument] int $assetDeclarationId, OutputInterface $output): int
    {
        $assetDeclaration = $this->entityManager->getRepository(AssetDeclaration::class)->find($assetDeclarationId);
        $output->writeln(sprintf('Evaluating asset declaration with id %d', $assetDeclarationId));
        $output->writeln(sprintf('DeclarationRealEstates count %d', $assetDeclaration->declarationRealEstate->count()));
        foreach ($assetDeclaration->declarationRealEstate as $declarationRealEstate) {
            $output->writeln(sprintf('Evaluating DeclarationRealEstates with id %d', $declarationRealEstate->id));
            $input = $this->serializer->serialize($declarationRealEstate, 'json', [
                'groups' => ['evaluation'],
            ]);

            $valuationDate = new DatePoint(sprintf('%d-03-31 00:00:00', $assetDeclaration->year));

            $result = $this->openDataAnalyzer->evaluateRealEstate($input, $valuationDate);
            $realEstateValuation = $declarationRealEstate->valuation ?? new RealEstateValuation();
            $realEstateValuation = $this->serializer->deserialize($result, RealEstateValuation::class, 'json', [
                AbstractNormalizer::OBJECT_TO_POPULATE => $realEstateValuation
            ]);
            $realEstateValuation->realEstate = $declarationRealEstate;
            $this->entityManager->persist($realEstateValuation);
        }

        $output->writeln(sprintf('DeclarationMovableAsset count %d', $assetDeclaration->declarationMovableAssets->count()));
        foreach ($assetDeclaration->declarationMovableAssets as $declarationMovableAssets) {
            $output->writeln(sprintf('Evaluating DeclarationMovableAsset with id %d', $declarationMovableAssets->id));
            $input = $this->serializer->serialize($declarationMovableAssets, 'json', [
                'groups' => ['evaluation'],
            ]);

            $valuationDate = new DatePoint(sprintf('%d-03-31 00:00:00', $assetDeclaration->year));

            $result = $this->openDataAnalyzer->evaluateMovableAsset($input, $valuationDate);
            $movableAssetValuation = $declarationMovableAssets->valuation ?? new MovableAssetValuation();
            $movableAssetValuation = $this->serializer->deserialize($result, MovableAssetValuation::class, 'json', [
                AbstractNormalizer::OBJECT_TO_POPULATE => $movableAssetValuation
            ]);
            $movableAssetValuation->movableAsset = $declarationMovableAssets;
            $this->entityManager->persist($movableAssetValuation);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
