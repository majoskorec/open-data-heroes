<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Embeddable]
class OwnershipShare
{
    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(name: 'numerator', type: Types::INTEGER, nullable: true)]
    public ?int $numerator = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(name: 'denominator', type: Types::INTEGER, nullable: true)]
    public ?int $denominator = null;

    public static function createDiff(?self $from, ?self $to): ?self
    {
        if ($from === null && $to === null) {
            return null;
        }

        $shareDiff =  new self();
        if ($from === null) {
            $shareDiff->numerator = 1;
            $shareDiff->denominator = 1;

            return $shareDiff;
        }

        if ($to === null) {
            $shareDiff->numerator = 0;
            $shareDiff->denominator = 1;

            return $shareDiff;
        }

        $shareDiff->numerator = $from->denominator * $to->numerator;
        $shareDiff->denominator = $from->numerator * $to->denominator;

        return $shareDiff;
    }

    public function getValue(): ?float
    {
        if ($this->numerator === null || $this->denominator === null || $this->denominator === 0) {
            return null;
        }

        return $this->numerator / $this->denominator;
    }
}
