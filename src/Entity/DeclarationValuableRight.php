<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_valuable_right')]
class DeclarationValuableRight
{
    #[Groups(['diff'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationValuableRight')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public AssetDeclaration $assetDeclaration;

    #[Groups(['diff'])]
    #[ORM\Column(type: Types::STRING, length: 150, nullable: true)]
    public ?string $assetType = null;

    #[Groups(['diff'])]
    #[ORM\Embedded(class: OwnershipShare::class, columnPrefix: 'ownership_share_')]
    public ?OwnershipShare $ownershipShare = null;

    #[Groups(['diff'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $rawText = null;

    #[ORM\OneToOne(targetEntity: ValuableRightValuation::class, mappedBy: 'valuableRight', orphanRemoval: true)]
    public ?ValuableRightValuation $valuation = null;
}
