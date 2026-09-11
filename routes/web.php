<?php

use Illuminate\Support\Facades\Route;

// Dashboard - inicio
Route::middleware('auth')->group(function () {
  Route::livewire('/', 'pages::dashboard.index')->name('dashboard');
  Route::livewire('/profile', 'pages::profile.index')->name('profile');

  // Usuarios
  Route::livewire('/users', 'pages::users.index')->middleware('permission:users.index')->name('users.index');
  Route::livewire('/users/create', 'pages::users.create')->middleware('permission:users.create')->name('users.create');
  Route::livewire('/users/edit/{user}', 'pages::users.edit')->middleware('permission:users.edit')->name('users.edit');

  // Roles
  Route::livewire('/roles', 'pages::roles.index')->middleware('permission:roles.index')->name('roles.index');
  Route::livewire('/roles/create', 'pages::roles.create')->middleware('permission:roles.create')->name('roles.create');
  Route::livewire('/roles/edit/{role}', 'pages::roles.edit')->middleware('permission:roles.edit')->name('roles.edit');
  Route::livewire('/roles/permissions/{role}', 'pages::roles.permissions')->middleware('permission:roles.permissions')->name('roles.permissions');

  // Categorias
  Route::livewire('/categories', 'pages::categories.index')->middleware('permission:categories.index')->name('categories.index');
  Route::livewire('/categories/create', 'pages::categories.create')->middleware('permission:categories.create')->name('categories.create');
  Route::livewire('/categories/edit/{category}', 'pages::categories.edit')->middleware('permission:categories.edit')->name('categories.edit');

  // Productos
  Route::livewire('/products', 'pages::products.index')->middleware('permission:products.index')->name('products.index');
  Route::livewire('/products/create', 'pages::products.create')->middleware('permission:products.create')->name('products.create');
  Route::livewire('/products/edit/{product}', 'pages::products.edit')->middleware('permission:products.edit')->name('products.edit');

  // Proveedores
  Route::livewire('/suppliers', 'pages::suppliers.index')->middleware('permission:suppliers.index')->name('suppliers.index');
  Route::livewire('/suppliers/create', 'pages::suppliers.create')->middleware('permission:suppliers.create')->name('suppliers.create');
  Route::livewire('/suppliers/edit/{supplier}', 'pages::suppliers.edit')->middleware('permission:suppliers.edit')->name('suppliers.edit');

  // Compras
  Route::livewire('/purchases', 'pages::purchases.index')->middleware('permission:purchases.index')->name('purchases.index');
  Route::livewire('/purchases/create', 'pages::purchases.create')->middleware('permission:purchases.create')->name('purchases.create');
  Route::livewire('/purchases/edit/{purchase}', 'pages::purchases.edit')->middleware('permission:purchases.edit')->name('purchases.edit');

  // Stock
  Route::livewire('/stock', 'pages::stock.index')->middleware('permission:stock.index')->name('stock.index');

  // Kardex
  Route::livewire('/kardex', 'pages::kardex.index')->middleware('permission:kardex.index')->name('kardex.index');

  // Ventas
  Route::livewire('/sales', 'pages::sales.index')->middleware('permission:sales.index')->name('sales.index');
  Route::livewire('/sales/create', 'pages::sales.create')->middleware('permission:sales.create')->name('sales.create');
  Route::livewire('/sales/show/{sale}', 'pages::sales.show')->middleware('permission:sales.show')->name('sales.show');

  // Ajustes de inventario
  Route::livewire('/inventory_adjustments', 'pages::inventory_adjustments.index')->middleware('permission:inventory_adjustments.index')->name('inventory_adjustments.index');
});

// Seguridad
Route::livewire('/login', 'pages::auth.login')->name('login');
