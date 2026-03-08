<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AssetDeclaration;
use App\Model\Dto\ParsedAssetDeclarationDto;
use App\OpenDataAnalyzer\OpenDataAnalyzer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Serializer\SerializerInterface;

#[AsCommand(name: 'z:app:assets:parse-declaration', description: 'Parse asset declaration')]
final class ParsedAssetDeclarationCommand extends Command
{
    public function __construct(
        private readonly OpenDataAnalyzer $openDataAnalyzer,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    public function __invoke(#[Argument] int $assetDeclarationId): int
    {
        $assetDeclaration = $this->entityManager->getRepository(AssetDeclaration::class)->find($assetDeclarationId);

        $output = $this->openDataAnalyzer->parseRawInputToParsedAssetDeclarationDtoJson($assetDeclaration->rawInput);

        $dto = $this->serializer->deserialize($output, ParsedAssetDeclarationDto::class, 'json');

        $this->removeOldData($assetDeclaration);

        foreach ($dto->publicFunctions as $publicFunction) {
            $publicFunction->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($publicFunction);
        }

        $income = $dto->income;
        if ($income !== null) {
            $income->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($income);
        }

        $employmentStatus = $dto->employmentStatus;
        if ($employmentStatus !== null) {
            $employmentStatus->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($employmentStatus);
        }

        $businessStatus = $dto->businessStatus;
        if ($businessStatus !== null) {
            $businessStatus->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($businessStatus);
        }

        $otherFunctionStatus = $dto->otherFunctionStatus;
        if ($otherFunctionStatus !== null) {
            $otherFunctionStatus->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($otherFunctionStatus);
        }

        foreach ($dto->realEstates as $realEstate) {
            $realEstate->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($realEstate);
        }

        foreach ($dto->movableAssets as $movableAsset) {
            $movableAsset->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($movableAsset);
        }

        foreach ($dto->valuableRights as $valuableRight) {
            $valuableRight->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($valuableRight);
        }

        foreach ($dto->liabilities as $liability) {
            $liability->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($liability);
        }

        $foreignRealEstateUsageStatus = $dto->foreignRealEstateUsageStatus;
        if ($foreignRealEstateUsageStatus !== null) {
            $foreignRealEstateUsageStatus->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($foreignRealEstateUsageStatus);
        }

        $foreignMovableUsageStatus = $dto->foreignMovableUsageStatus;
        if ($foreignMovableUsageStatus !== null) {
            $foreignMovableUsageStatus->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($foreignMovableUsageStatus);
        }

        $giftStatus = $dto->giftStatus;
        if ($giftStatus !== null) {
            $giftStatus->assetDeclaration = $assetDeclaration;
            $this->entityManager->persist($giftStatus);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function removeOldData(AssetDeclaration $assetDeclaration): void
    {
        $assetDeclaration->declarationBusinessStatus = null;
//        if ($assetDeclaration->declarationBusinessStatus !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationBusinessStatus);
//        }

        $assetDeclaration->declarationEmploymentStatus = null;
//        if ($assetDeclaration->declarationEmploymentStatus !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationEmploymentStatus);
//        }

        $assetDeclaration->declarationForeignMovableUsageStatus = null;
//        if ($assetDeclaration->declarationForeignMovableUsageStatus !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationForeignMovableUsageStatus);
//        }

        $assetDeclaration->declarationForeignRealEstateUsageStatus = null;
//        if ($assetDeclaration->declarationForeignRealEstateUsageStatus !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationForeignRealEstateUsageStatus);
//        }

        $assetDeclaration->declarationGiftStatus = null;
//        if ($assetDeclaration->declarationGiftStatus !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationGiftStatus);
//        }

        $assetDeclaration->declarationIncome = null;
//        if ($assetDeclaration->declarationIncome !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationIncome);
//        }

        $assetDeclaration->declarationLiabilities = new ArrayCollection();
//        foreach ($assetDeclaration->declarationLiabilities as $entity) {
//            $this->entityManager->remove($entity);
//        }

        $assetDeclaration->declarationMovableAssets = new ArrayCollection();
//        foreach ($assetDeclaration->declarationMovableAssets as $entity) {
//            $this->entityManager->remove($entity);
//        }

        $assetDeclaration->declarationOtherFunctionStatus = null;
//        if ($assetDeclaration->declarationOtherFunctionStatus !== null) {
//            $this->entityManager->remove($assetDeclaration->declarationOtherFunctionStatus);
//        }

        $assetDeclaration->declarationPublicFunction = new ArrayCollection();
//        foreach ($assetDeclaration->declarationPublicFunction as $entity) {
//            $this->entityManager->remove($entity);
//        }

        $assetDeclaration->declarationRealEstate = new ArrayCollection();
//        foreach ($assetDeclaration->declarationRealEstate as $entity) {
//            $this->entityManager->remove($entity);
//        }

        $assetDeclaration->declarationValuableRight = new ArrayCollection();
//        foreach ($assetDeclaration->declarationValuableRight as $entity) {
//            $this->entityManager->remove($entity);
//        }
        $this->entityManager->flush();
    }
}
