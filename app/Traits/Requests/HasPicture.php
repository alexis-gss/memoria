<?php

declare(strict_types=1);

namespace App\Traits\Requests;

use App\Lib\Helpers\ToolboxHelper;

trait HasPicture
{
    /**
     * Required picture.
     *
     * @var bool
     */
    private $required = true;

    /**
     * Minimum limit.
     *
     * @var int
     */
    private $limitMin = 100;

    /**
     * Maximum limit.
     *
     * @var int
     */
    private $limitMax = 3840;

    /**
     * Minimum width.
     *
     * @var int
     */
    private $minWidth = 100;

    /**
     * Maximum width.
     *
     * @var int
     */
    private $maxWidth = 3840;

    /**
     * Minimum height.
     *
     * @var int
     */
    private $minHeight = 100;

    /**
     * Maximum height.
     *
     * @var int
     */
    private $maxHeight = 2160;

    /**
     * Image rules.
     */
    public function pictureRules(
        string $name = 'picture',
        bool $required = true,
        ?int $minWidth = null,
        ?int $minHeight = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null
    ): array {
        $sizes = (object) [
            'minWidth'  => $minWidth  ?? $this->minWidth,
            'minHeight' => $minHeight ?? $this->minHeight,
            'maxWidth'  => $maxWidth  ?? $this->maxWidth,
            'maxHeight' => $maxHeight ?? $this->maxHeight,
        ];

        return [
            $name => [
                $required ? 'required' : 'nullable',
                function (string $attribute, $value, callable $fail) use ($sizes): void {
                    ToolboxHelper::validatePicture($attribute, $value, $fail, $sizes);
                },
            ],
        ];
    }

    /**
     * Images rules.
     */
    public function picturesRules(
        string $name = 'pictures',
        ?bool $required = null,
        ?int $limitMin = null,
        ?int $limitMax = null,
        ?int $minWidth = null,
        ?int $minHeight = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null
    ): array {
        $sizes = (object) [
            'required'  => $required  ?? $this->required,
            'limitMin'  => $limitMin  ?? $this->limitMin,
            'limitMax'  => $limitMax  ?? $this->limitMax,
            'minWidth'  => $minWidth  ?? $this->minWidth,
            'minHeight' => $minHeight ?? $this->minHeight,
            'maxWidth'  => $maxWidth  ?? $this->maxWidth,
            'maxHeight' => $maxHeight ?? $this->maxHeight,
        ];

        return [
            "{$name}"   => ($required ? 'required' : 'nullable') . "|array|between:$limitMin,$limitMax",
            "{$name}.*" => [
                ($required ? 'required' : 'nullable'),
                function (string $attribute, $value, callable $fail) use ($sizes): void {
                    ToolboxHelper::validatePicture($attribute, $value, $fail, $sizes);
                },
            ],
        ];
    }

    /**
     * Merges picture field with request data.
     */
    protected function mergePicture(string $name = 'picture'): void
    {
        $picturePath = $this->{$name};
        if (is_string($picturePath)) {
            $this->merge(["{$name}Validate" => []]);
            // ! Empty picture.
            if ($picturePath === 'false') {
                // * Empty picture and exit
                $this->merge([$name => []]);

                return;
            }
            $decodedPath = \urldecode($picturePath);
            $this->merge([
                "{$name}Validate" => array_merge(
                    $this->{"{$name}Validate"},
                    [\public_path(ltrim($decodedPath, '/'))]
                ),
            ]);
            $changedPicture = ltrim($decodedPath, '/');
            $this->merge([
                $name => $changedPicture,
            ]);
        } // end if
    }

    /**
     * Merges pictures fields with request data.
     */
    protected function mergePictures(string $name = 'pictures'): void
    {
        $pictures = $this->{$name};
        if (is_array($pictures)) {
            $this->merge(["{$name}Validate" => []]);
            foreach ($pictures as $key => $picturePath) {
                // ! Empty picture.
                if ($picturePath === 'false') {
                    // * Empty picture and exit
                    $this->merge([$name => []]);

                    return;
                }
                $decodedPath = \urldecode($picturePath);
                $this->merge([
                    "{$name}Validate" => array_merge(
                        $this->{"{$name}Validate"},
                        [\public_path(ltrim($decodedPath, '/'))]
                    ),
                ]);
                $changedPicture       = $this->{$name};
                $changedPicture[$key] = ltrim($decodedPath, '/');
                $this->merge([
                    $name => $changedPicture,
                ]);
            }
        } // end if
    }
}
