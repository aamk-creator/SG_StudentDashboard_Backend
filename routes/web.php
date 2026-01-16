<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about',function(){
    return "about page";
});


Route::get('/user/contact',function(){
    return "user contact page";
});
 

Route::get('/user/{name?}/{age?}',function($name="Aung",$age='22')
{
    return "name is" . $name.  "age is" .$age;

});