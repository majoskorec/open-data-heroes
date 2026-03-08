<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\ChangeStatusEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_movable_asset')]
class DeclarationMovableAsset
{
    #[Groups(['diff'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationMovableAssets')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public AssetDeclaration $assetDeclaration;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    public ?string $assetType = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public ?string $brand = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    public ?int $manufactureYear = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Embedded(class: OwnershipShare::class, columnPrefix: 'ownership_share_')]
    public ?OwnershipShare $ownershipShare = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $rawText = null;

    #[ORM\OneToOne(targetEntity: MovableAssetValuation::class, mappedBy: 'movableAsset', orphanRemoval: true)]
    public ?MovableAssetValuation $valuation = null;

    #[ORM\OneToOne(targetEntity: DeclarationMovableAssetDiff::class, mappedBy: 'toMovableAsset', orphanRemoval: true)]
    public ?DeclarationMovableAssetDiff $ancestor = null;

    #[ORM\OneToOne(targetEntity: DeclarationMovableAssetDiff::class, mappedBy: 'fromMovableAsset', orphanRemoval: true)]
    public ?DeclarationMovableAssetDiff $descendant = null;

    public function changeStatus(): ChangeStatusEnum
    {
        return $this->ancestor?->getChangeStatus();
    }
}
