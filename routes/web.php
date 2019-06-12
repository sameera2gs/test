<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/ldap', 'LdapController@index')->name('ldap');
Route::get('/ldap-login', 'LdapController@login')->name('ldap-login');
Route::get('/ldap-get-all-users', 'LdapController@get_all_users')->name('ldap-get-all-users');
