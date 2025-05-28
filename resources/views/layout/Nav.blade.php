<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Atap Management System</title>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
     <style>
        :root {
            --primary-color: #7d8590; /* Lighter gray */
            --secondary-color: #e63946; /* Brighter, more visible red */
            --accent-color: #d62828; /* Darker red for accents */
            --light-color: #f2e9e4; /* Off-white cream background */
            --dark-color: #5d6266; /* Darker gray for contrast */
            --bg-color: #f0ebe5; /* Warm beige background */
        }

        body {
            background-color: var(--bg-color);
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 45px; /* Reduced from 50px */
            color: #333;
            font-size: 0.85rem; /* Smaller base font size */
        }

        /* Top Navbar */
        .navbar-top {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            position: fixed;
            z-index: 1030;
            top: 0;
            width: 100%;
            height: 45px; /* Reduced from 50px */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-weight: 600;
            color: var(--light-color) !important;
            font-size: 1.1rem; /* Reduced from 1.2rem */
            display: flex;
            align-items: center;
        }

        .brand-logo {
            color: var(--secondary-color);
            margin-right: 10px; /* Reduced from 12px */
            font-size: 1.3rem; /* Reduced from 1.4rem */
            filter: drop-shadow(0 0 2px rgba(255,255,255,0.3));
        }

        /* Side Navbar - Desktop */
        .side-navbar {
            background: var(--primary-color);
            position: fixed;
            z-index: 1020;
            width: 180px; /* Reduced from 200px */
            top: 45px; /* Reduced from 50px */
            height: calc(100% - 45px); /* Adjusted for new navbar height */
            padding-top: 8px; /* Reduced from 10px */
            transition: all 0.3s ease;
            box-shadow: 3px 0 15px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .side-navbar .navbar-nav {
            display: flex;
            flex-direction: column;
            padding: 0;
            width: 50%;
        }

        .side-navbar .nav-item {
            margin: 0px;
            border-radius: 5px; /* Reduced from 6px */
            overflow: hidden;
        }

        .nav-link {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            padding: 0;
            color: inherit;
            cursor: pointer;
        }

        .side-navbar .nav-link {
            display: flex;
            align-items: center;
            width: 100%;
            white-space: normal;
            padding: 6px 10px; /* Reduced from 8px 12px */
            color: var(--light-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            font-size: 0.85rem; /* Reduced from 0.9rem */
        }

        .side-navbar .nav-link:hover {
            background-color: rgba(230, 57, 70, 0.15);
            color: var(--light-color);
            border-left: 3px solid var(--secondary-color);
        }

        .side-navbar .nav-link i {
            margin-right: 8px; /* Reduced from 10px */
            min-width: 18px; /* Reduced from 20px */
            text-align: center;
            font-size: 0.95rem; /* Reduced from 1rem */
            color: var(--secondary-color);
        }

        .side-navbar .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
            border-left: 3px solid var(--light-color);
        }

        .side-navbar .nav-link.active i {
            color: white;
        }
        
        .side-navbar .dropdown-menu {
            background-color: var(--primary-color);
            border: none;
            box-shadow: none;
            width: 100%;
            margin-top: 3px; /* Reduced from 5px */
            padding: 0;
        }

        .side-navbar .dropdown-menu .dropdown-item {
            display: flex;
            align-items: center;
            width: 100%;
            white-space: normal;
            padding: 6px 10px; /* Reduced from 8px 12px */
            color: var(--light-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
            background: none;
            font-size: 0.85rem; /* Reduced from 0.9rem */
        }

        .side-navbar .dropdown-menu .dropdown-item:hover {
            background-color: rgba(230, 57, 70, 0.15);
            color: var(--light-color);
            border-left: 3px solid var(--secondary-color);
        }

        .side-navbar .dropdown-menu .dropdown-item.active {
            background-color: var(--secondary-color);
            color: white;
            border-left: 3px solid var(--light-color);
        }

        /* Main Container */
        .main-container {
            margin-left: 180px; /* Reduced from 200px */
            padding: 12px; /* Reduced from 15px */
            transition: margin 0.3s ease;
            background-color: var(--bg-color);
        }

        /* Card and other UI elements */
        .card {
            margin-bottom: 12px; /* Reduced from 15px */
            border-radius: 5px; /* Reduced from 6px */
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.05); /* Reduced shadow */
            border: none;
            background-color: var(--light-color);
        }

        .card-header {
            background-color: var(--primary-color);
            color: var(--light-color);
            font-weight: 600;
            border-top-left-radius: 5px !important; /* Reduced from 6px */
            border-top-right-radius: 5px !important; /* Reduced from 6px */
            padding: 8px 12px; /* Reduced from 10px 15px */
            font-size: 0.9rem; /* Reduced from 0.95rem */
        }

        .card-body {
            padding: 10px; /* Reduced from 12px */
        }

        .title-box {
            background-color: var(--secondary-color);
            color: var(--light-color);
            text-align: center;
            margin-bottom: 12px; /* Reduced from 15px */
            padding: 8px; /* Reduced from 10px */
            border-radius: 5px; /* Reduced from 6px */
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1); /* Reduced shadow */
            font-size: 1.15rem; /* Add smaller font size for title */
        }

        /* Button styling */
        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-danger {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        /* Form elements - smaller */
        .form-control {
            padding: 0.3rem 0.45rem; /* Smaller padding */
            font-size: 0.85rem; /* Reduced from 0.9rem */
            height: calc(1.4em + 0.45rem + 2px); /* Smaller height */
        }

        /* Top navbar links */
        .navbar-top .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            padding: 6px 10px; /* Reduced from 8px 12px */
            border-radius: 3px; /* Reduced from 4px */
            margin: 0 4px;
            transition: all 0.3s ease;
            font-size: 0.85rem; /* Added to make text smaller */
        }

        .navbar-top .nav-link:hover {
            color: white !important;
            background-color: rgba(230, 57, 70, 0.3);
        }

        .navbar-top .nav-link i {
            color: var(--secondary-color);
            margin-right: 4px; /* Reduced from 5px */
        }

        /* Tables */
        .table {
            background-color: var(--light-color);
            border-radius: 5px; /* Reduced from 6px */
            overflow: hidden;
            border: 5px solid black; /* Keep existing border */
            border-collapse: collapse;
        }

        .table th, .table td {
            border: 1px solid black; /* Keep existing border */
            padding: 0.4rem; /* Reduced from 0.5rem */
            font-size: 0.8rem; /* Reduced from 0.85rem */
        }

        .table thead th {
            background-color: var(--primary-color);
            color: var(--light-color);
            border-bottom: none;
            font-size: 0.8rem; /* Specifically for header text */
        }

        /* Red accent border */
        .card {
            border-top: 2px solid var(--secondary-color); /* Reduced from 3px */
        }

        /* Buttons smaller */
        .btn {
            padding: 0.2rem 0.4rem; /* Smaller padding */
            font-size: 0.8rem; /* Reduced from 0.85rem */
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 12px; /* Reduced from 15px */
            list-style: none;
            padding: 0;
        }

        /* Pagination items */
        .pagination li {
            margin: 0 3px; /* Reduced from 4px */
        }

        /* Pagination links */
        .pagination li a, 
        .pagination li span {
            display: block;
            padding: 5px 8px; /* Reduced from 6px 10px */
            text-decoration: none;
            color: #333;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 3px; /* Reduced from 4px */
            transition: all 0.3s ease;
            font-size: 0.8rem; /* Reduced from 0.85rem */
        }

        /* Hover state */
        .pagination li a:hover {
            background-color: #f5f5f5;
            border-color: #ccc;
        }

        /* Active state */
        .pagination li.active span {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        /* Previous/Next buttons */
        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            padding: 5px 10px; /* Reduced from 6px 12px */
        }

        /* Disabled state */
        .pagination .disabled span {
            color: #888;
            background-color: #f8f8f8;
            border-color: #eee;
            cursor: not-allowed;
        }

        /* Add specific styling for the Transaksi Penjualan heading */
        h1, h2, h3, h4, h5, h6 {
            font-size: 1.2rem; /* Default size for headings */
        }

        /* Title box with icon */
        .title-box i {
            font-size: 1.1rem; /* Reduced icon size */
            margin-right: 5px;
        }

        /* Reduce the font size of specific title elements */
        .title-box .transaksi-title {
            font-size: 1.15rem; /* Smaller title font for Transaksi Penjualan */
            font-weight: 600;
        }

        /* Section headers */
        .section-header {
            background-color: var(--primary-color);
            color: var(--light-color);
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Responsive styles */
        @media (max-width: 1000px) {
            body {
                padding-top: 0;
            }

            .navbar-top {
                position: relative;
                height: auto;
            }

            .navbar-brand {
                padding: 10px 0; /* Reduced from 12px */
                font-size: 1rem; /* Reduced size */
            }

            .side-navbar {
                width: 100%;
                position: relative;
                top: 0;
                height: auto;
                display: none;
                box-shadow: none;
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            }

            .side-navbar.show {
                display: block;
            }

            .side-navbar .nav-item {
                margin: 1px 0; /* Reduced from 2px */
                border-radius: 0;
            }

            .side-navbar .nav-link {
                padding: 10px; /* Reduced from 12px */
                border-left: none;
                border-left-width: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                font-size: 0.9rem; /* Reduced size */
            }

            .main-container {
                margin-left: 0;
                padding: 8px; /* Reduced from 10px */
            }

            /* Mobile-friendly table styles */
            .table-responsive {
                overflow-x: auto;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
                border-radius: 5px; /* Reduced from 6px */
            }

            .table {
                width: 100%;
                white-space: nowrap;
            }

            .table th, .table td {
                padding: 6px; /* Reduced from 8px */
                font-size: 0.8rem; /* Reduced from 0.85rem */
            }

            .btn {
                font-size: 0.8rem; /* Reduced from 0.85rem */
                padding: 4px 8px; /* Reduced from 5px 10px */
                border-radius: 3px; /* Reduced from 4px */
            }

            .navbar-top .nav-link {
                margin: 6px 0; /* Reduced from 8px */
                padding: 8px 10px; /* Reduced from 10px 12px */
                display: block;
                width: 100%;
                text-align: left;
                border-radius: 0;
                font-size: 0.85rem;
            }

            .navbar-top .nav-item {
                width: 100%;
            }

            .navbar-top form {
                margin: 6px 0; /* Reduced from 8px */
                padding: 0;
                width: 100%;
            }

            .navbar-top form .nav-link {
                display: block;
                width: 100%;
                text-align: left;
            }

            .side-navbar .nav-item {
                margin: 0;
            }

            .side-navbar .nav-link {
                padding: 10px; /* Reduced from 12px */
                font-size: 0.9rem; /* Reduced from 0.95rem */
                border-radius: 0;
            }
            
            /* Reduce title box size on mobile */
            .title-box {
                font-size: 1rem;
                padding: 6px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="fas fa-warehouse brand-logo"></i>
               Atap Management System
            </a>
            <button class="navbar-toggler" type="button" id="topNavToggle">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarTop">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <?php if(auth()->guard()->guest()): ?>
                            <a class="nav-link" href="/signin"><i class="fas fa-sign-in-alt mr-1"></i> Masuk</a>
                        <?php else: ?>
                            <form action="/logout" method="POST">
                                <?php echo csrf_field(); ?>
                                <button class="nav-link no-style-btn" type="submit" onclick="return confirm('Apakah anda yakin untuk log out?')">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Keluar
                                </button>
                            </form>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/account-maintenance"><i class="fas fa-user-cog mr-1"></i> Manage-Akun</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Side Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark side-navbar" id="sideNavbar">
        <div class="container-fluid p-0">
            <ul class="navbar-nav flex-column w-100">
                <!-- Dropdown Menu Master -->
                <li class='nav-item'>
                    <a class="nav-link" data-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false" aria-controls="masterMenu">
                        <i class="fas fa-list"></i> Master Data
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                    <div class="collapse bg-dark border-0" id="masterMenu">
                        <!-- Master menu items -->
                        <ul class="nav flex-column ml-3">
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('master.barang') }}"><i class="fas fa-layer-group"></i> Display Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('customers.index')); ?>"><i class="fas fa-users"></i> Customers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('stok_owner.index')); ?>"><i class="fas fa-database"></i>Sales</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('suppliers.index')); ?>"><i class="fas fa-people-carry-box"></i> Suppliers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('master.cara_bayar')); ?>"><i class="fas fa-rupiah-sign"></i> Cara Bayar</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('perusahaan.index')); ?>"><i class="bi bi-building"></i>Perusahaan</a>
                            </li>
                            
                            
                        </ul>
                    </div>
                </li>
                <!-- Dropdown Menu Transaksi -->
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#transaksiMenu" role="button" aria-expanded="false" aria-controls="masterMenu">
                        <i class="fas fa-arrow-right-arrow-left"></i> Transaksi
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                    <div class="collapse bg-dark border-0" id="transaksiMenu">
                        <ul class="nav flex-column ml-3">
                            <!-- Di sini Dropdown Transaksi Penjualan -->
                            <li class='nav-item'>
                                <a class="nav-link" data-toggle="collapse" href="#penjualan" role="button" aria-expanded="false" aria-controls="transaksipenjualan">
                                    <i class="fas fa-cash-register"></i> Penjualan
                                    <i class="fas fa-chevron-down ml-auto"></i>
                                </a>
                                <div class="collapse bg-dark border-0" id="penjualan">
                                    <ul class="nav flex-column ml-3">
                                        <!-- Add the new Panel Management menu item here -->
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo e(route('transaksi.penjualan')); ?>"><i class="fas fa-circle-plus"></i> Tambah Penjualan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('transaksi.penjualancustomer') }}"><i class="fas fa-user-tag mr-2"></i>Data Penjualan Per Customer</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo e(route('transaksi.index')); ?>"><i class="fas fa-envelope-open-text"></i> List Nota Penjualan</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo e(route('transaksi.purchaseorder')); ?>"><i class="bi bi-cash-stack me-2"></i>Purchase Order</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <!-- Di sini Dropdown Transaksi Pembelian -->
                            <li class='nav-item'>
                                <a class="nav-link" data-toggle="collapse" href="#pembelian" role="button" aria-expanded="false" aria-controls="pembelian">
                                    <i class="fas fa-cart-flatbed"></i> Pembelian
                                    <i class="fas fa-chevron-down ml-auto"></i>
                                </a>
                                <div class="collapse bg-dark border-0" id="pembelian">
                                    <ul class="nav flex-column ml-3">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('pembelian.index') }}"><i class="fas fa-circle-plus"></i> Tambah Pembelian</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('pembelian.nota.list') }}"><i class="fas fa-envelope-open-text"></i> List Nota Pembelian</a>
                                        </li>   
                                    </ul>
                                </div>
                            </li> 
                        </ul>
                    </div>
                </li>

                <li class='nav-item'>
                    <a class="nav-link" data-toggle="collapse" href="#suratjalan" role="button" aria-expanded="false" aria-controls="suratjalan">
                        <i class="fas fa-truck-fast"></i> Surat Jalan
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                    <div class="collapse bg-dark border-0" id="suratjalan">
                        <ul class="nav flex-column ml-3">
                            <!-- Add the new Panel Management menu item here -->
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('suratjalan.create') }}"><i class="fas fa-circle-plus"></i> Tambah Surat Jalan</a>
                            </li>
                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('suratjalan.history') }}"><i class="fas fa-clock-rotate-left"></i> Display Surat Jalan</a>
                            </li>
                        </ul>
                    </div> 
                </li>
                <li class='nav-item'>
                    <a class="nav-link" data-toggle="collapse" href="#barang" role="button" aria-expanded="false" aria-controls="suratjalan">
                        <i class="bi bi-boxes"></i> Barang
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                            <div class="collapse bg-dark border-0" id="barang">
                                    <ul class="nav flex-column ml-3">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('panels.repack') }}"><i class="fas fa-boxes"></i> Repack</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo e(route('stock.mutasi')); ?>"><i class="fas fa-exchange-alt"></i> Mutasi Stok Barang</a>
                                        </li> 
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?php echo e(route('stock.adjustment.index')); ?>"><i class="bi bi-arrow-repeat"></i> Stok adjustment</a>
                                        </li>
                                    </ul>
                                </div>
                </li>            

                <li class="nav-item">
                    <a class="nav-link" href="/viewKas"><i class="fas fa-money-bill-wave mr-2"></i>Kas</a>
                </li>

            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-container" id="mainContainer">
        <!-- Content will be loaded here -->
        <?php echo $__env->yieldContent('content'); ?>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const topNavToggle = document.getElementById('topNavToggle');
        const navbarTop = document.getElementById('navbarTop');
        const sideNavbar = document.getElementById('sideNavbar');
        const mainContainer = document.getElementById('mainContainer');

        // Function to check if we're on mobile view
        function isMobileView() {
            return window.innerWidth <= 1000;
        }

        // Function to adjust layout based on viewport size
        function adjustLayout() {
            if (isMobileView()) {
                // Mobile view layout adjustments
                mainContainer.style.marginLeft = '0';
                // Only hide the side navbar if it's not toggled to show
                if (!sideNavbar.classList.contains('show')) {
                    sideNavbar.style.display = 'none';
                }
                // Add body padding only for desktop
                document.body.style.paddingTop = '0';
            } else {
                // Desktop view layout adjustments
                mainContainer.style.marginLeft = '200px'; // Updated from 220px
                sideNavbar.style.display = 'block';
                document.body.style.paddingTop = '50px'; // Updated from 60px
            }
        }

        // Toggle navigation on mobile
        topNavToggle.addEventListener('click', function() {
            navbarTop.classList.toggle('show');

            // In mobile view, also toggle the side navbar
            if (isMobileView()) {
                sideNavbar.classList.toggle('show');
                sideNavbar.style.display = sideNavbar.classList.contains('show') ? 'block' : 'none';
            }
        });

        // Add active class to current page
        const currentLocation = window.location.pathname;
        document.querySelectorAll('.side-navbar .nav-link').forEach(link => {
            if (link.getAttribute('href') === currentLocation) {
                link.classList.add('active');
            }
        });

        // Adjust layout when window is resized
        window.addEventListener('resize', adjustLayout);

        // Initialize layout based on current viewport
        adjustLayout();

        // Additional listener for orientation changes (important for mobile devices)
        window.addEventListener('orientationchange', adjustLayout);
    });

    </script>

    <?php echo $__env->yieldContent('scripts'); ?>
    
</body>
</html>
<?php /**PATH C:\Work\AtapJerri\resources\views/layout/Nav.blade.php ENDPATH**/ ?>