<?php

declare(strict_types=1);

namespace App\Traits\Enums;

use Error;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * BaseEnum is a trait that provides common functionality for enumerations in PHP.
 *
 * @method static array cases()
 */
trait BaseEnum
{
    // * GENERIC METHODS

    /**
     * Get enum value.
     */
    public function value(): string | int
    {
        return self::toValues()[$this->name];
    }

    /**
     * Get enum label.
     */
    public function label(): string
    {
        return self::toLabels()[$this->name];
    }

    /**
     * Get enum name.
     */
    public function name(): string
    {
        return $this->name;
    }

    // * STATIC FETCH METHODS

    /**
     * Get enum labels.
     *
     * @return array<string>
     */
    public static function toLabels(): array
    {
        if (empty(self::getCache('labels'))) {
            $labels = self::getLabelsConstant();
            self::setCache('labels', \collect(self::cases())
                ->mapWithKeys(function ($enum) use ($labels) {
                    if (! \is_null($labels) and ! isset($labels[$enum->name])) {
                        throw new Error('Missing definition label ' . self::class . " for {$enum->name}");
                    }

                    return [$enum->name => \trans(
                        ! \is_null($labels) ? $labels[$enum->name] : Str::of($enum->name)->ucfirst()->value()
                    )];
                })->all());
        }

        return self::getCache('labels');
    }

    /**
     * Get enum values.
     *
     * @return array<int|string>
     */
    public static function toValues(): array
    {
        if (empty(self::getCache('values'))) {
            self::setCache('values', \collect(self::cases())
                ->mapWithKeys(fn ($enum) => [$enum->name => $enum->value])->all());
        }

        return self::getCache('values');
    }

    /**
     * Get enums.
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Get enums.
     *
     * @return array<self>
     */
    public static function toArray(): array
    {
        if (empty(self::getCache('enums'))) {
            $definitions = self::getCustomDefinitions();
            self::setCache('enums', \collect(self::cases())
                ->mapWithKeys(function ($enum) use ($definitions) {
                    $baseArray = [
                        'name'  => $enum->name,
                        'label' => $enum->label(),
                        'value' => $enum->value,
                    ];
                    foreach ($definitions as $index => $elements) {
                        $index = Str::of($index)->lower()->singular()->value();
                        if (! isset($elements[$enum->name])) {
                            throw new Error("Missing definition {$index} " . self::class . " for {$enum->name}");
                        }
                        $baseArray[$index] = $elements[$enum->name];
                    }

                    return [$enum->name => (object) $baseArray];
                })->all());
        }

        return self::getCache('enums');
    }

    /**
     * Fetch enum using value.
     */
    public static function make(self | int | string $value, string $field = 'value'): ?self
    {
        if ($field === 'label') {
            $enumLabels = array_change_key_case(array_flip(self::toLabels()), CASE_LOWER);
            $value      = $enumLabels[strtolower($value)] ?? false;
            $field      = 'name';
        }

        return ! \is_object($value) ?
            \collect(self::cases())->keyBy($field)->get($value) : $value;
    }

    /**
     * Test if 2 enum are equals.
     *
     * @throws Error If provided element are not enums that extends BaseEnum.
     */
    public function equals(object ...$others): bool
    {
        foreach ($others as $other) {
            throw_unless(\in_array(BaseEnum::class, \class_uses_recursive($other)), Error::class, __METHOD__ .
                ' requires $enum to use ' . BaseEnum::class);
            if (
                $this::class    === $other::class
                && $this->value === $other->value
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Serialize to json.
     */
    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    // * CACHE

    /**
     * Get cache array.
     */
    private static function getCache(string $index): array
    {
        self::initCache();
        $GLOBALS[self::class]['props'][$index] ??= [];

        return $GLOBALS[self::class]['props'][$index];
    }

    /**
     * Set cache array.
     */
    private static function setCache(string $index, array $items): void
    {
        self::initCache();
        $GLOBALS[self::class]['props'][$index] ??= [];
        $GLOBALS[self::class]['props'][$index] = $items;
    }

    /**
     * Init cache if needed.
     */
    private static function initCache(): void
    {
        $GLOBALS[self::class] ??= [
            'props' => [
                'enums'  => [],
                'values' => [],
                'labels' => [],
            ],
            'customProps' => null,
        ];
    }

    // * PRIVATE

    /**
     * Fetch the LABELS constant if exists.
     */
    private static function getLabelsConstant(): ?array
    {
        $class_name = self::class;
        try {
            $constant_reflex = new ReflectionClassConstant($class_name, 'LABELS');

            return $constant_reflex->getValue();
        } catch (ReflectionException) {
            return null;
        }
    }

    /**
     * Get custom constants.
     */
    private static function getCustomDefinitions(): array
    {
        // ? Has custom props in cache ?
        if (! isset($GLOBALS[self::class]) or \is_null($GLOBALS[self::class]['customProps'])) {
            $names = \collect(self::cases())->keyBy('name');

            $GLOBALS[self::class]['customProps'] = \collect((new ReflectionClass(self::class))->getConstants())
                ->filter(function (mixed $constant, string $key) use ($names) {
                    if ($key === 'LABELS' or ! \is_array($constant)) {
                        return false;
                    }

                    return \collect($constant)->keys()
                        ->filter(fn (string $name) => $names->has($name))->count();
                })->all();
        }

        return $GLOBALS[self::class]['customProps'];
    }
}
