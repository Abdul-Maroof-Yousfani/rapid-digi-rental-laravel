<?php

use Illuminate\Support\Facades\Auth;



if(! function_exists('role_base_url')){
    function role_base_url($path){
        $role= Auth::user()->getRoleNames()->first();
        return url("$role/$path");
    }
}

if(! function_exists('role_base_route')){
    function role_base_route($route, $parameters = []){
        $role= Auth::user()->getRoleNames()->first();
        $routeName = "{$role}.{$route}";
        return route($routeName, $parameters);
    }
}

?>