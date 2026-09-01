<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function logout()
    {
        // Cerrar sesión
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        // Login
        $this->redirectRoute('login');
    }
};
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary border">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">Stockio</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Menu -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active': '' }}" aria-current="page" href="{{ route('dashboard')  }}" wire:navigate>
                        <i class="fa fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" aria-current="page" href="{{ route('profile') }}" wire:navigate>
                        <i class="fa fa-user"></i>
                        <span>Perfil</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" wire:navigate>
                        <i class="fa fa-users"></i>
                        <span>Usuarios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.index') ? 'active' : '' }}" wire:navigate>
                        <i class="fa fa-user-tie"></i>
                        <span>Roles</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.index') ? 'active' : '' }}" wire:navigate>
                        <i class="fa fa-cube"></i>
                        <span>Categorias</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}" wire:navigate>
                        <i class="fa fa-cubes"></i>
                        <span>Products</span>
                    </a>
                </li>
            </ul>

            <!-- Logout -->
            <form class="d-flex" wire:submit="logout">
                <button class="btn">
                    <i class="fa fa-sign-out"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </div>
</nav>