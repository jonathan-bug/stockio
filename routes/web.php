<?php

use Illuminate\Support\Facades\Route;

// Dashboard - inicio
Route::middleware('auth')->group(function () {
  Route::livewire('/', 'pages::dashboard.index')->name('dashboard');
  Route::livewire('/profile', 'pages::profile.index')->name('profile');

  // Usuarios
  Route::livewire('/users', 'pages::users.index')->middleware('permission:users.index')->name('users.index');
  Route::livewire('/users/create', 'pages::users.create')->middleware('permission:users.create')->name('users.create');
  Route::livewire('/users/edit/{id}', 'pages::users.edit')->middleware('permission:users.edit')->name('users.edit');

  // Roles
  Route::livewire('/roles', 'pages::roles.index')->middleware('permission:roles.index')->name('roles.index');
  Route::livewire('/roles/create', 'pages::roles.create')->middleware('permission:roles.create')->name('roles.create');
  Route::livewire('/roles/edit/{role}', 'pages::roles.edit')->middleware('permission:roles.edit')->name('roles.edit');
  Route::livewire('/roles/permissions/{role}', 'pages::roles.permissions')->middleware('permission:roles.permissions')->name('roles.permissions');
});

// Seguridad
Route::livewire('/login', 'pages::auth.login')->name('login');
