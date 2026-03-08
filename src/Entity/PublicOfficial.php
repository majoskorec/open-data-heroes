<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'public_official')]
class PublicOfficial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\Column(name: 'title_before', type: 'string', length: 50, nullable: true)]
    public ?string $titleBefore = null;

    #[ORM\Column(name: 'first_name', type: 'string', length: 100, nullable: true)]
    public ?string $firstName = null;

    #[ORM\Column(name: 'last_name', type: 'string', length: 100, nullable: true)]
    public ?string $lastName = null;

    #[ORM\Column(name: 'title_after', type: 'string', length: 50, nullable: true)]
    public ?string $titleAfter = null;

    public function __toString(): string
    {
        return trim(str_replace('  ', '', sprintf('%s %s %s %s',
            $this->titleBefore ?? '',
            $this->firstName ?? '',
            $this->lastName ?? '',
            $this->titleAfter ?? '',
        )));
    }
}
