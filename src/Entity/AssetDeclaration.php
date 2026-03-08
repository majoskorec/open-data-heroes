<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'asset_declaration')]
class AssetDeclaration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PublicOfficial::class)]
    #[ORM\JoinColumn(name: 'public_official_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    public ?PublicOfficial $publicOfficial = null;

    #[ORM\Column(name: 'raw_input', type: Types::TEXT, nullable: false)]
    public string $rawInput;

    #[ORM\Column(name: 'year', type: Types::INTEGER, nullable: true)]
    public ?int $year;

    #[ORM\Column(name: 'external_id', type: Types::INTEGER, nullable: true)]
    public ?int $externalId = null;

    #[ORM\OneToOne(targetEntity: DeclarationBusinessStatus::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationBusinessStatus $declarationBusinessStatus = null;

    #[ORM\OneToOne(targetEntity: DeclarationEmploymentStatus::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationEmploymentStatus $declarationEmploymentStatus = null;

    #[ORM\OneToOne(targetEntity: DeclarationForeignMovableUsageStatus::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationForeignMovableUsageStatus $declarationForeignMovableUsageStatus = null;

    #[ORM\OneToOne(targetEntity: DeclarationForeignRealEstateUsageStatus::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationForeignRealEstateUsageStatus $declarationForeignRealEstateUsageStatus = null;

    #[ORM\OneToOne(targetEntity: DeclarationGiftStatus::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationGiftStatus $declarationGiftStatus = null;

    #[ORM\OneToOne(targetEntity: DeclarationIncome::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationIncome $declarationIncome = null;

    /**
     * @var Collection<array-key, DeclarationLiability>
     */
//    #[Groups(['diff'])]
    #[ORM\OneToMany(targetEntity: DeclarationLiability::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $declarationLiabilities;

    /**
     * @var Collection<array-key, DeclarationMovableAsset>
     */
    #[Groups(['diff'])]
    #[ORM\OneToMany(targetEntity: DeclarationMovableAsset::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true, indexBy: 'id')]
    public Collection $declarationMovableAssets;

    #[ORM\OneToOne(targetEntity: DeclarationOtherFunctionStatus::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public ?DeclarationOtherFunctionStatus $declarationOtherFunctionStatus = null;

    /**
     * @var Collection<array-key, DeclarationValuableRight>
     */
    #[ORM\OneToMany(targetEntity: DeclarationPublicFunction::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $declarationPublicFunction;

    /**
     * @var Collection<array-key, DeclarationRealEstate>
     */
    #[Groups(['diff'])]
    #[ORM\OneToMany(targetEntity: DeclarationRealEstate::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true, indexBy: 'id')]
    public Collection $declarationRealEstate;

    /**
     * @var Collection<array-key, DeclarationValuableRight>
     */
//    #[Groups(['diff'])]
    #[ORM\OneToMany(targetEntity: DeclarationValuableRight::class, mappedBy: 'assetDeclaration', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $declarationValuableRight;

    public function __construct()
    {
        $this->declarationMovableAssets = new ArrayCollection();
        $this->declarationPublicFunction = new ArrayCollection();
        $this->declarationRealEstate = new ArrayCollection();
        $this->declarationValuableRight = new ArrayCollection();
        $this->declarationLiabilities = new ArrayCollection();
    }
}
