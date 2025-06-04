<?php

namespace App\Traits\Models;

use Spatie\SchemaOrg\Schema;

trait SchemaOrg
{
    // * SETTERS

    /**
     * Return the schema of person (conceptor).
     *
     * @return \Spatie\SchemaOrg\Person
     */
    public function setPersonSchema(): \Spatie\SchemaOrg\Person
    {
        return Schema::person()
            ->setProperty('@id', config('app.url') . '#person')
            ->givenName(explode(" ", config('app.conceptor'))[0])
            ->familyName(explode(" ", config('app.conceptor'))[1]);
    }

    /**
     * Set the website schema.
     *
     * @return \Spatie\SchemaOrg\Website
     */
    public function setWebsiteSchema(): \Spatie\SchemaOrg\Website
    {
        return Schema::website()
            ->setProperty('@id', config('app.url') . '#website')
            ->name(config('app.name'))
            ->about(trans('fo_footer_details', [
                'conceptor' => config('app.conceptor'),
                'appName'   => config('app.name')
            ]))
            ->description(trans('fo_footer_details', [
                'conceptor' => config('app.conceptor'),
                'appName'   => config('app.name')
            ]))
            ->accessModeSufficient(
                Schema::itemList()
                    ->itemListElement([
                        "textual",
                        "visual",
                        "tactile",
                        "auditory"
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
            ->dateCreated("17/02/2022")
            ->datePublished("17/02/2022")
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
     *
     * @return \Spatie\SchemaOrg\Person
     */
    public function getPersonSchema(): \Spatie\SchemaOrg\Person
    {
        return Schema::person()->setProperty('@id', config('app.url') . '#person');
    }

    /**
     * Get the website schema.
     *
     * @return \Spatie\SchemaOrg\Website
     */
    public function getWebsiteSchema(): \Spatie\SchemaOrg\Website
    {
        return Schema::website()->setProperty('@id', config('app.url') . '#website');
    }
}
