<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DeclarationIncome;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class OtherIncomeDiff
{
    public ?DeclarationIncome $from = null;
    public ?DeclarationIncome $to = null;

    public function getDiffValue(): ?float
    {
        $fromOtherIncome = $this->from?->otherIncome;
        $toOtherIncome = $this->to?->otherIncome;
        if ($fromOtherIncome === null || $toOtherIncome === null) {
            return null;
        }

        return (float) $toOtherIncome - (float) $fromOtherIncome;
    }
}
