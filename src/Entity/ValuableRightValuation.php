<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'valuable_right_valuation')]
class ValuableRightValuation extends AssetValuation
{
    #[ORM\OneToOne(targetEntity: DeclarationValuableRight::class, inversedBy: 'valuation')]
    #[ORM\JoinColumn(name: 'valuable_right_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public DeclarationValuableRight $valuableRight;
}
