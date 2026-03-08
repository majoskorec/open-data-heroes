<?php

declare(strict_types=1);

namespace App\Model\Dto;

use App\Entity\DeclarationBusinessStatus;
use App\Entity\DeclarationEmploymentStatus;
use App\Entity\DeclarationForeignMovableUsageStatus;
use App\Entity\DeclarationForeignRealEstateUsageStatus;
use App\Entity\DeclarationGiftStatus;
use App\Entity\DeclarationIncome;
use App\Entity\DeclarationLiability;
use App\Entity\DeclarationMovableAsset;
use App\Entity\DeclarationOtherFunctionStatus;
use App\Entity\DeclarationPublicFunction;
use App\Entity\DeclarationRealEstate;
use App\Entity\DeclarationValuableRight;

final class ParsedAssetDeclarationDto
{
    /** @var list<DeclarationPublicFunction> */
    public array $publicFunctions = [];

    public ?DeclarationIncome $income = null;

    public ?DeclarationEmploymentStatus $employmentStatus = null;

    public ?DeclarationBusinessStatus $businessStatus = null;

    public ?DeclarationOtherFunctionStatus $otherFunctionStatus = null;

    /** @var list<DeclarationRealEstate> */
    public array $realEstates = [];

    /** @var list<DeclarationMovableAsset> */
    public array $movableAssets = [];

    /** @var list<DeclarationValuableRight> */
    public array $valuableRights = [];

    /** @var list<DeclarationLiability> */
    public array $liabilities = [];

    public ?DeclarationForeignRealEstateUsageStatus $foreignRealEstateUsageStatus = null;

    public ?DeclarationForeignMovableUsageStatus $foreignMovableUsageStatus = null;

    public ?DeclarationGiftStatus $giftStatus = null;
}
