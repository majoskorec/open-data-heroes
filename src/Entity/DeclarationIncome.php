<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_income')]
class DeclarationIncome
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\OneToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationIncome')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', unique: true)]
    public AssetDeclaration $assetDeclaration;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    public ?string $publicFunctionIncome = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 15, scale: 2, nullable: true)]
    public ?string $otherIncome = null;
}
