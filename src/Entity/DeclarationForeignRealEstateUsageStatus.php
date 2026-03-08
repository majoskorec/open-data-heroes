<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_foreign_real_estate_usage_status')]
class DeclarationForeignRealEstateUsageStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\OneToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationForeignRealEstateUsageStatus')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', unique: true)]
    public AssetDeclaration $assetDeclaration;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    public ?bool $declaredNone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $rawText = null;
}
