<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_liability')]
class DeclarationLiability
{
    #[Groups(['diff'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationLiabilities')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public AssetDeclaration $assetDeclaration;

    #[Groups(['diff'])]
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    public ?string $liabilityType = null;

    #[Groups(['diff'])]
    #[ORM\Embedded(class: OwnershipShare::class, columnPrefix: 'ownership_share_')]
    public ?OwnershipShare $ownershipShare = null;

    #[Groups(['diff'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    public ?DateTimeImmutable $originatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $rawText = null;
}
