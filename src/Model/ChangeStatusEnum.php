<?php

declare(strict_types=1);

namespace App\Model;

enum ChangeStatusEnum: string
{
    case Unchanged = 'Unchanged';
    case Added = 'Added';
    case Removed = 'Removed';
    case Decreased = 'Decreased';
    case Increased = 'Increased';
    case None = 'None';
}
