<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AssetValuation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    // manual | ai | external | declared
    #[ORM\Column(type: Types::STRING, length: 20)]
    public string $sourceType;

    #[ORM\Column(type: Types::STRING, length: 3)]
    public string $currency = 'EUR';

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    public ?string $exactValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    public ?string $estimatedMinValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    public ?string $estimatedLikelyValue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    public ?string $estimatedMaxValue = null;

    public function isExact(): bool
    {
        return $this->exactValue !== null;
    }

    public function isEstimated(): bool
    {
        return $this->exactValue === null
            && (
                $this->estimatedMinValue !== null
                || $this->estimatedLikelyValue !== null
                || $this->estimatedMaxValue !== null
            );
    }

    public function getBestValue(): ?string
    {
        return $this->exactValue ?? $this->estimatedLikelyValue;
    }
}
