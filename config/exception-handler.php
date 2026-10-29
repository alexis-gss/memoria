<?php

declare(strict_types=1);

return [
    /**
     * Send mail when app debug is turn off
     * so you can be warned if an unhandled exception occurs
     */
    'enableMaiLog' => ! config('app.debug'),

    /**
     * Put a developper secured mailbox in here
     * so you can receive email if things go mad
     */
    'contactsList' => [config('mail.contactsList.contactform')],
];
