<?php

declare(strict_types=1);

namespace App\Model\Dto;

final class AssetDeclarationDiffDto
{
    /**
     * @var array<DiffDto>
     */
    public array $realEstateDiffs = [];

    /**
     * @var array<DiffDto>
     */
    public array $movableAssetDiffs = [];
}
