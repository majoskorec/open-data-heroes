<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'real_estate_valuation')]
class RealEstateValuation extends AssetValuation
{
    #[ORM\OneToOne(targetEntity: DeclarationRealEstate::class, inversedBy: 'valuation')]
    #[ORM\JoinColumn(name: 'real_estate_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public DeclarationRealEstate $realEstate;
}
