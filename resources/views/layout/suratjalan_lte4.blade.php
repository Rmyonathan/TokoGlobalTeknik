<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Surat Jalan') · AdminLTE 4</title>

    <!-- AdminLTE 4 + Bootstrap 5 (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @stack('styles')
</head>
<body class="layout-fixed sidebar-mini" style="--lte-sidebar-width: 220px;">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-0 shadow-sm">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ url('/') }}" class="nav-link">Home</a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <span class="nav-link">Logged in as: {{ auth()->user()->name ?? 'Guest' }}</span>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container (minimal for module scope) -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('suratjalan.history') }}" class="brand-link text-decoration-none">
            <span class="brand-text font-weight-light">Surat Jalan</span>
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('suratjalan.create') ?? '#' }}" class="nav-link {{ request()->routeIs('suratjalan.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-truck"></i>
                            <p>Buat Surat Jalan</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suratjalan.rekap') }}" class="nav-link {{ request()->routeIs('suratjalan.rekap') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Rekap</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suratjalan.history') }}" class="nav-link {{ request()->routeIs('suratjalan.history') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>History</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="background-color:#f4f6f9;">
        <section class="content pt-3 pb-4">
            <div class="container-fluid px-3">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer small">
        <div class="float-end d-none d-sm-inline">AdminLTE 4</div>
        <strong>&copy; {{ date('Y') }}.</strong> All rights reserved.
    </footer>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>


