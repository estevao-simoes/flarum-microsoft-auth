<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

use Flarum\Auth\Microsoft\MicrosoftAuthController;
use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    
    new Extend\Locales(__DIR__ . '/resources/locale'),
    
    (new Extend\Routes('forum'))
        ->get('/auth/microsoft', 'auth.microsoft', MicrosoftAuthController::class),
];
