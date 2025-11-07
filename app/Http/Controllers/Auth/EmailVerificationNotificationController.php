<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Minimal stub used so artisan commands that reflect on controller classes do not fail.
     */
    public function __invoke(Request $request)
    {
        // This is only a stub to satisfy route:list reflection. Do not use in production.
        abort(404);
    }
}
