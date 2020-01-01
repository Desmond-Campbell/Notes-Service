<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/notes/add', 'NotesController@store');
$router->post('/notes/delete', 'NotesController@delete');
$router->post('/notes/update', 'NotesController@store');
$router->post('/notes/browse', 'NotesController@browse');
$router->post('/notes/get', 'NotesController@get');

$router->post('/notes/get-folders', 'NotesController@getFolders');
$router->post('/notes/add-folder', 'NotesController@storeFolder');
$router->post('/notes/update-folder', 'NotesController@storeFolder');
$router->post('/notes/delete-folder', 'NotesController@deleteFolder');