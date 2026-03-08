<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_public_function')]
class DeclarationPublicFunction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: AssetDeclaration::class, inversedBy: 'declarationPublicFunction')]
    #[ORM\JoinColumn(name: 'asset_declaration_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public AssetDeclaration $assetDeclaration;

    #[ORM\Column(type: Types::STRING, length: 255)]
    public string $name;
}
