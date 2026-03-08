<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'z:app:assets:process', description: 'Process asset declaration')]
final class ProcessAssetDeclaration extends Command
{
    public function __invoke(#[Argument] int $assetDeclarationId, OutputInterface $output): int
    {
        $application = $this->getApplication();

        $inputArray1 = new ArrayInput([
            'command' => 'z:app:assets:parse-declaration',
            'asset-declaration-id' => $assetDeclarationId,
        ]);
        $inputArray1->setInteractive(false);

        $result = $application->doRun($inputArray1, $output);
        if ($result !== Command::SUCCESS) {
            $output->writeln('<error>Failed to parse asset declaration</error>');

            return Command::FAILURE;
        }

        $inputArray2 = new ArrayInput([
            'command' => 'z:app:assets:evaluate',
            'asset-declaration-id' => $assetDeclarationId,
        ]);
        $inputArray2->setInteractive(false);

        return $application->doRun($inputArray2, $output);
    }
}
