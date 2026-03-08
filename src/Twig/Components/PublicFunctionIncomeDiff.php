<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DeclarationIncome;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class PublicFunctionIncomeDiff
{
    public ?DeclarationIncome $from = null;
    public ?DeclarationIncome $to = null;

    public function getDiffValue(): ?float
    {
        $fromPublicFunctionIncome = $this->from?->publicFunctionIncome;
        $toPublicFunctionIncome = $this->to?->publicFunctionIncome;
        if ($fromPublicFunctionIncome === null || $toPublicFunctionIncome === null) {
            return null;
        }

        return (float) $toPublicFunctionIncome - (float) $fromPublicFunctionIncome;
    }
}
