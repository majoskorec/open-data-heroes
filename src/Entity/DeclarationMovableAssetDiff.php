<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\ChangeStatusEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'declaration_movable_asset_diff')]
class DeclarationMovableAssetDiff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    public ?int $id = null;

    #[ORM\OneToOne(targetEntity: DeclarationMovableAsset::class, inversedBy: 'descendant')]
    #[ORM\JoinColumn(name: 'from_movable_asset_id', referencedColumnName: 'id', nullable: true)]
    public ?DeclarationMovableAsset $fromMovableAsset = null;

    #[ORM\OneToOne(targetEntity: DeclarationMovableAsset::class, inversedBy: 'ancestor')]
    #[ORM\JoinColumn(name: 'to_movable_asset_id', referencedColumnName: 'id', nullable: true)]
    public ?DeclarationMovableAsset $toMovableAsset = null;

    #[ORM\Embedded(class: OwnershipShare::class, columnPrefix: 'ownership_share_diff_')]
    public ?OwnershipShare $ownershipShareDiff = null;

    public function getChangeStatus(): ChangeStatusEnum
    {
        if ($this->fromMovableAsset === null && $this->toMovableAsset === null) {
            return ChangeStatusEnum::None;
        }

        if ($this->fromMovableAsset === null) {
            return ChangeStatusEnum::Added;
        }

        if ($this->toMovableAsset === null) {
            return ChangeStatusEnum::Removed;
        }

        $value = $this->ownershipShareDiff?->getValue();
        if ($value === null) {
            return ChangeStatusEnum::None;
        }

        if ($value === 0.0) {
            return ChangeStatusEnum::Removed;
        }

        if ($value < 1.0) {
            return ChangeStatusEnum::Decreased;
        }

        if ($value === 1.0) {
            return ChangeStatusEnum::Unchanged;
        }

        // if ($value > 1.0) {
        return ChangeStatusEnum::Increased;
    }
}
