<?php

declare(strict_types=1);

namespace App\Traits\Models;

use Spatie\SchemaOrg\Person;
use Spatie\SchemaOrg\Schema;
use Spatie\SchemaOrg\WebSite;

trait SchemaOrg
{
    // * SETTERS
    /**
     * Return the schema of person (conceptor).
     */
    public function setPersonSchema(): Person
    {
        return Schema::person()
            ->setProperty('@id', config('app.url') . '#person')
            ->givenName(explode(' ', config('app.conceptor'))[0])
            ->familyName(explode(' ', config('app.conceptor'))[1]);
    }

    /**
     * Set the website schema.
     */
    public function setWebsiteSchema(): WebSite
    {
        return Schema::website()
            ->setProperty('@id', config('app.url') . '#website')
            ->name(config('app.name'))
            ->about(trans('fo_footer_details', [
                'conceptor' => config('app.conceptor'),
                'appName'   => config('app.name'),
            ]))
            ->description(trans('fo_footer_details', [
                'conceptor' => config('app.conceptor'),
                'appName'   => config('app.name'),
            ]))
            ->accessModeSufficient(
                Schema::itemList()
                    ->itemListElement([
                        'textual',
                        'visual',
                        'tactile',
                        'auditory',
                    ])
            )
            ->accessibilityControl([
                'fullKeyboardControl',
                'fullMouseControl',
                'fullTouchControl',
            ])
            ->accessibilityFeature([
                'annotations',
                'ARIA',
                'index',
                'structuralNavigation',
            ])
            ->genre(config('schema-org.website.genre'))
            ->dateCreated('17/02/2022')
            ->datePublished('17/02/2022')
            ->inLanguage(config('app.locales'))
            ->isAccessibleForFree(true)
            ->isFamilyFriendly(true)
            ->keywords(['images', 'gallery', 'games'])
            ->creator($this->getPersonSchema())
            ->contributor($this->getPersonSchema())
            ->funder($this->getPersonSchema())
            ->publisher($this->getPersonSchema())
            ->author($this->getPersonSchema())
            ->potentialAction(
                Schema::searchAction()
                    ->target(rtrim(config('app.url'), '/') . config('schema-org.website.search'))
                    ->query('required name=search_term_string')
            );
    }

    // * GETTERS
    /**
     * Get the person schema.
     */
    public function getPersonSchema(): Person
    {
        return Schema::person()->setProperty('@id', config('app.url') . '#person');
    }

    /**
     * Get the website schema.
     */
    public function getWebsiteSchema(): WebSite
    {
        return Schema::website()->setProperty('@id', config('app.url') . '#website');
    }
}
