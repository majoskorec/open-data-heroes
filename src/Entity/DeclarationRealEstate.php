<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_real_estate')]
class DeclarationRealEstate
{
    #[Groups(['diff'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationRealEstate')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public AssetDeclaration $assetDeclaration;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    public ?string $assetType = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public ?string $cadastralArea = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    public ?string $lvNumber = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Embedded(class: OwnershipShare::class, columnPrefix: 'ownership_share_')]
    public ?OwnershipShare $ownershipShare = null;

    #[Groups(['evaluation', 'diff'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $rawText = null;

    #[ORM\OneToOne(targetEntity: RealEstateValuation::class, mappedBy: 'realEstate', orphanRemoval: true)]
    public ?RealEstateValuation $valuation = null;

    #[ORM\OneToOne(targetEntity: DeclarationRealEstateDiff::class, mappedBy: 'toRealEstate', orphanRemoval: true)]
    public ?DeclarationRealEstateDiff $ancestor = null;

    #[ORM\OneToOne(targetEntity: DeclarationRealEstateDiff::class, mappedBy: 'fromRealEstate', orphanRemoval: true)]
    public ?DeclarationRealEstateDiff $descendant = null;
}
