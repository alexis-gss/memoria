<?php

declare(strict_types=1);

namespace App\Enums\ActivityLogs;

use App\Traits\Enums\BaseEnum;

enum ActivityLogsEventEnum: int
{
    use BaseEnum;

    case created    = 1;
    case updated    = 2;
    case duplicated = 3;
    case deleted    = 4;

    /**
     * Optionnal labels definition.
     *
     * @phpstan-ignore-next-line
     */
    private const LABELS = [
        self::created->name    => 'crud.actions.create',
        self::updated->name    => 'crud.actions.edit',
        self::duplicated->name => 'crud.actions.duplicate',
        self::deleted->name    => 'crud.actions.delete',
    ];

    /**
     * Bootstrap contextual classes definition.
     */
    private const BOOTSTRAP_CLASSES = [
        self::created->name    => 'success',
        self::updated->name    => 'warning',
        self::duplicated->name => 'info',
        self::deleted->name    => 'danger',
    ];

    /**
     * Get the Bootstrap contextual class.
     */
    public function bootstrapClass(): string
    {
        return self::BOOTSTRAP_CLASSES[$this->name];
    }
}
