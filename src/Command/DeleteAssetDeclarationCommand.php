<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AssetDeclaration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'z:app:assets:delete', description: 'Delete asset declaration')]
final class DeleteAssetDeclarationCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    public function __invoke(#[Argument] int $assetDeclarationId, OutputInterface $output): int
    {
        $assetDeclaration = $this->entityManager->getRepository(AssetDeclaration::class)->find($assetDeclarationId);
        if ($assetDeclaration === null) {
            $output->writeln(sprintf('Asset declaration with id %d not found', $assetDeclarationId));

            return Command::FAILURE;
        }

        $this->entityManager->remove($assetDeclaration);
        $this->entityManager->flush();

        $output->writeln(sprintf('Asset declaration with id %d deleted', $assetDeclarationId));

        return Command::SUCCESS;
    }
}
