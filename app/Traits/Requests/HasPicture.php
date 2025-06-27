<?php

namespace App\Traits\Requests;

use App\Lib\Helpers\ToolboxHelper;

trait HasPicture
{
    /**
     * Minimum width
     *
     * @var integer
     */
    private $minWidth = 100;

    /**
     * Maximum width
     *
     * @var integer
     */
    private $maxWidth = 3840;

    /**
     * Minimum height
     *
     * @var integer
     */
    private $minHeight = 100;

    /**
     * Maximum height
     *
     * @var integer
     */
    private $maxHeight = 2160;

    /**
     * Image rules.
     *
     * @param string       $name
     * @param boolean      $required
     * @param integer|null $minWidth
     * @param integer|null $minHeight
     * @param integer|null $maxWidth
     * @param integer|null $maxHeight
     * @return array
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
            'minWidth'  => $minWidth ?? $this->minWidth,
            'minHeight' => $minHeight ?? $this->minHeight,
            'maxWidth'  => $maxWidth ?? $this->maxWidth,
            'maxHeight' => $maxHeight ?? $this->maxHeight
        ];
        return [
            $name => [
                $required ? 'required' : 'nullable',
                function (string $attribute, $value, callable $fail) use ($sizes) {
                    ToolboxHelper::validatePicture($attribute, $value, $fail, $sizes);
                }
            ]
        ];
    }

    /**
     * Images rules.
     *
     * @param string       $name
     * @param boolean      $required
     * @param integer|null $limitMin
     * @param integer|null $limitMax
     * @param integer|null $minWidth
     * @param integer|null $minHeight
     * @param integer|null $maxWidth
     * @param integer|null $maxHeight
     * @return array
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
            'required'  => $required ?? $this->required,
            'limitMin'  => $limitMin ?? $this->limitMin,
            'limitMax'  => $limitMax ?? $this->limitMax,
            'minWidth'  => $minWidth ?? $this->minWidth,
            'minHeight' => $minHeight ?? $this->minHeight,
            'maxWidth'  => $maxWidth ?? $this->maxWidth,
            'maxHeight' => $maxHeight ?? $this->maxHeight
        ];
        return [
            "{$name}" => ($required ? 'required' : 'nullable') . "|array|between:$limitMin,$limitMax",
            "{$name}.*" => [
                ($required ? 'required' : 'nullable'),
                function (string $attribute, $value, callable $fail) use ($sizes) {
                    ToolboxHelper::validatePicture($attribute, $value, $fail, $sizes);
                }
            ]
        ];
    }

    /**
     * Merges picture field with request data.
     *
     * @param string $name
     * @return void
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
                )
            ]);
            $changedPicture = ltrim($decodedPath, '/');
            $this->merge([
                $name => $changedPicture
            ]);
        } //end if
    }

    /**
     * Merges pictures fields with request data.
     *
     * @param string $name
     * @return void
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
                    )
                ]);
                $changedPicture       = $this->{$name};
                $changedPicture[$key] = ltrim($decodedPath, '/');
                $this->merge([
                    $name => $changedPicture
                ]);
            }
        } //end if
    }
}
