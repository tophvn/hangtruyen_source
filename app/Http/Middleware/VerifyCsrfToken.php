<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'truyen-tranh/*/vote',
        'truyen-tranh/*/report',
        'truyen-tranh/*/follow',
        'truyen-tranh/*/comments',
        'truyen-tranh/*/comments/*/like',
        'tai-khoan',
        'tai-khoan/upload-avatar',
        'tai-khoan/clear-reading',
        'admin/*',
    ];
}
