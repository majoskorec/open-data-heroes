<?php

declare(strict_types=1);

namespace App\Model\Dto;

use App\Entity\AssetDeclaration;
use App\Entity\PublicOfficial;

final class AnnouncementParserDto
{
    public PublicOfficial $publicOfficial;
    public AssetDeclaration $assetDeclaration;
}
