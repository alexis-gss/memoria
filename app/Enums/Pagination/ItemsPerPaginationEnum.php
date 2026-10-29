<?php

declare(strict_types=1);

namespace App\Enums\Pagination;

use App\Traits\Enums\BaseEnum;

enum ItemsPerPaginationEnum: int
{
    use BaseEnum;

    case five       = 5;
    case twelve     = 12;
    case twentyfive = 25;
    case fifty      = 50;
    case onehundred = 100;
}
