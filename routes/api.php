<?php

use Illuminate\Http\Request;
use App\Article;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', 'Auth\RegisterController@register');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout');

Route::group(['middleware' => 'auth:api'], function() {
    Route::get('articles', 'ArticleController@index');
    Route::get('articles/{article}', 'ArticleController@show');
    Route::post('articles', 'ArticleController@store');
    Route::put('articles/{article}', 'ArticleController@update');
    Route::delete('articles/{article}', 'ArticleController@delete');
});

Route::post('/ldap-login', 'LdapController@login')->name('ldap-login');
Route::post('/shoutout/add', 'ShoutoutController@addShoutout')->name('add-shoutout');
Route::post('/shoutout/edit', 'ShoutoutController@editShoutout')->name('edit-shoutout');
Route::post('/shoutout/removeImage', 'ShoutoutController@remove_image')->name('shoutout-remove-image');
Route::post('/shoutout/getList', 'ShoutoutController@getList')->name('shoutout-list');
Route::post('/shoutout/getComments', 'ShoutoutController@getComments')->name('shoutout-comments');
Route::post('/shoutout/getNotifications', 'ShoutoutController@getNotifications')->name('shoutout-notifications');
Route::post('/reportedShoutouts', 'ShoutoutController@reportedShoutouts')->name('reported-shoutouts');
Route::post('/reportedShoutout/comments', 'ShoutoutController@reportedShoutoutComments')->name('reported-shoutout-comments');

