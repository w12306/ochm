<?php

namespace App\Http\Middleware\Admin;

use Closure;


class BeforeMiddleware {

    public function handle($request, Closure $next)
    {
        // Perform action
        //echo time();
        return  ;
    }
}
