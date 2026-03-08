<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'movable_asset_valuation')]
class MovableAssetValuation extends AssetValuation
{
    #[ORM\OneToOne(targetEntity: DeclarationMovableAsset::class, inversedBy: 'valuation')]
    #[ORM\JoinColumn(name: 'movable_asset_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public DeclarationMovableAsset $movableAsset;
}
