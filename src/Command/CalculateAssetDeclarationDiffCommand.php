<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AssetDeclaration;
use App\Entity\DeclarationMovableAsset;
use App\Entity\DeclarationMovableAssetDiff;
use App\Entity\DeclarationRealEstate;
use App\Entity\DeclarationRealEstateDiff;
use App\Entity\OwnershipShare;
use App\Entity\PublicOfficial;
use App\Model\Dto\AssetDeclarationDiffDto;
use App\OpenDataAnalyzer\OpenDataAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'z:app:assets:diff', description: 'Calculate asset declaration diff')]
final class CalculateAssetDeclarationDiffCommand extends Command
{
    public function __construct(
        private readonly OpenDataAnalyzer $openDataAnalyzer,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    public function __invoke(#[Argument] int $publicOfficialId, OutputInterface $output): int
    {
        $publicOfficial = $this->entityManager->getRepository(PublicOfficial::class)->find($publicOfficialId);
        if ($publicOfficial === null) {
            $output->writeln('<error>Public official not found</error>');

            return Command::FAILURE;
        }
        /** @var AssetDeclaration[] $assetDeclarations */
        $assetDeclarations = $this->entityManager->getRepository(AssetDeclaration::class)->findBy(
            criteria: ['publicOfficial' => $publicOfficial],
            orderBy: ['year' => 'ASC'],
        );

        $descendant = null;
        foreach ($assetDeclarations as $assetDeclaration) {
            $ancestor = $descendant;
            $descendant = $assetDeclaration;
            if ($ancestor === null) {
                continue;
            }

            $this->calculate($ancestor, $descendant);
        }

        return Command::SUCCESS;
    }

    private function calculate(AssetDeclaration $ancestor, AssetDeclaration $descendant): void
    {
        $ancestorJson = $this->serializer->serialize($ancestor, 'json', [
            'groups' => ['diff'],
        ]);
        $descendantJson = $this->serializer->serialize($descendant, 'json', [
            'groups' => ['diff'],
        ]);

        $output = $this->openDataAnalyzer->calculateAssetDeclarationDiff($ancestorJson, $descendantJson);
        $this->removeDiffs($ancestor, $descendant);

        /** @var AssetDeclarationDiffDto $dto */
        $dto = $this->serializer->deserialize($output, AssetDeclarationDiffDto::class, 'json');

        foreach ($dto->realEstateDiffs as $diff) {
            $from = $this->getDeclarationRealEstate($diff->fromId, $ancestor);
            $to = $this->getDeclarationRealEstate($diff->toId, $descendant);

            $declarationRealEstateDiff = new DeclarationRealEstateDiff();
            $declarationRealEstateDiff->fromRealEstate = $from;
            $declarationRealEstateDiff->toRealEstate = $to;
            $declarationRealEstateDiff->ownershipShareDiff = OwnershipShare::createDiff($from?->ownershipShare, $to?->ownershipShare);
            $this->entityManager->persist($declarationRealEstateDiff);
        }

        foreach ($dto->movableAssetDiffs as $diff) {
            $from = $this->getDeclarationMovableAsset($diff->fromId, $ancestor);
            $to = $this->getDeclarationMovableAsset($diff->toId, $descendant);

            $declarationMovableAssets = new DeclarationMovableAssetDiff();
            $declarationMovableAssets->fromMovableAsset = $from;
            $declarationMovableAssets->toMovableAsset = $to;
            $declarationMovableAssets->ownershipShareDiff = OwnershipShare::createDiff($from?->ownershipShare, $to?->ownershipShare);
            $this->entityManager->persist($declarationMovableAssets);
        }

        $this->entityManager->flush();
    }

    private function getDeclarationMovableAsset(?int $id, AssetDeclaration $assetDeclaration): ?DeclarationMovableAsset
    {
        if ($id === null) {
            return null;
        }

        return $assetDeclaration->declarationMovableAssets->get($id)
            ?? throw new RuntimeException(sprintf(
                'DeclarationMovableAsset with id %d not found in asset declaration %d',
                $id,
                $assetDeclaration->id
            ));
    }

    private function getDeclarationRealEstate(?int $id, AssetDeclaration $assetDeclaration): ?DeclarationRealEstate
    {
        if ($id === null) {
            return null;
        }

        return $assetDeclaration->declarationRealEstate->get($id)
            ?? throw new RuntimeException(sprintf(
                'DeclarationRealEstate with id %d not found in asset declaration %d',
                $id,
                $assetDeclaration->id
            ));
    }

    private function removeDiffs(AssetDeclaration $ancestor, AssetDeclaration $descendant): void
    {
        foreach ($ancestor->declarationRealEstate as $realEstate) {
            $realEstate->descendant = null;
        }
        foreach ($ancestor->declarationMovableAssets as $movableAsset) {
            $movableAsset->descendant = null;
        }
        foreach ($descendant->declarationRealEstate as $realEstate) {
            $realEstate->ancestor = null;
        }
        foreach ($descendant->declarationMovableAssets as $movableAsset) {
            $movableAsset->ancestor = null;
        }
        $this->entityManager->flush();
    }
}
