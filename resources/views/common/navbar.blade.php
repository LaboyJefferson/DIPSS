<style>
    /* Sidebar container styling */
    #sidebar {
        width: 250px;
        height: 100vh;
        background-color: #565656; /* Dark background for better readability */
        color: #ecf0f1; /* Light text for contrast */
        position: fixed;
        top: 0;
        right: 0; /* Sidebar on the right */
        transition: all 0.3s;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        overflow-y: auto;
        font-size: 1rem; /* Base font size */
        z-index: 1000; /* Ensure sidebar is above other elements */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5); 
    }

    /* Sidebar header */
    .sidebar-header {
        background-color: #3a8f66; /* Darker header for distinction */
        padding: 20px;
        text-align: center;
        color: #fff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-header img {
        width: 80px;
        height: 80px;
        padding: 10px;
    }

    .sidebar-header h6 {
        margin: 0;
        font-weight: 600;
    }

    /* Sidebar navigation */
    #sidebar .components {
        padding: 0;
        margin: 0;
    }

    #sidebar .components li {
        list-style: none;
        padding: 15px 20px;
        margin: 1px 0;
        transition: all 0.3s;
    }

    /* Active link styling */
    #sidebar .components li.active a {
        background-color: #3a8f66; /* Soft green for active link */
        color: #fff;
        border-radius: 5px;
        font-weight: bold; /* Optional: make it bold for emphasis */
    }

    #sidebar .components li a:hover {
        background-color: #3a8f66; /* Soft green on hover/active */
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: -1em;
    }

    #sidebar .components li a {
        color: #ecf0f1;
        padding: 10px; /* Updated padding for better touch targets */
        display: block;
        transition: color 0.3s;
        text-decoration: none;
        list-style: none;
    }

    #sidebar .components li a i {
        margin-right: 10px;
    }

    #sidebar .components li a:hover {
        text-decoration: none; /* Ensure underline remains removed on hover */
    }

    

    /* Responsive behavior */
    @media (max-width: 768px) {
        #sidebar {
            width: 0; /* Initially hide sidebar on mobile */
            overflow: hidden;
        }

        #sidebar.collapsed {
            width: 250px; /* Expand sidebar when toggled */
        }

        .toggle-btn {
            display: block; /* Show toggle button on small screens */
            position: absolute;
            top: 15px;
            left: 15px;
            font-size: 30px;
            color: #ecf0f1; /* Hamburger icon color */
            cursor: pointer;
            z-index: 999; /* Ensure it's above other elements */
        }

        .content {
            margin-right: 0; /* Adjust content on smaller screens */
        }
    }

    /* Small hover effect for links */
    #sidebar .components li a:hover {
        padding-left: 25px;
        transition: padding-left 0.2s ease-in-out;
    }
</style>


@php
    $userImage = auth()->user()->image_url;
    $userRole = auth()->user()->role;
    $userName = auth()->user()->first_name . ' ' . auth()->user()->last_name;
@endphp

<!-- Sidebar Navigation -->
@if(Auth::user()->role == "Administrator")
<nav id="sidebar" class="vh-100 navbar-expand-lg">
    <button class="navbar-toggler toggle-btn" type="button" onclick="toggleSidebar()">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="sidebar-header d-flex flex-row align-items-center py-3">
        @if($userImage)
            <img class="image rounded-circle" src="{{ asset('storage/userImage/' . $userImage) }}">
        @else
            <i class="fa-solid fa-circle-user fa-3x me-3"></i>
        @endif

        <div class="d-flex flex-column">
            <h6 style="font-size: 0.99rem; font-weight: 300;">{{ $userRole }}</h6>
            <h6 style="font-size: 1.00rem; font-weight: bold;">{{ $userName }}</h6>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="{{ Request::routeIs('accounts_table') ? 'active' : '' }}">
            <a href="{{ route('accounts_table') }}"><i class="fa-solid fa-user-shield"></i> ACCOUNT</a>
        </li>
        <li class="{{ Request::routeIs('show_profile') ? 'active' : '' }}">
            <a href="{{ route('show_profile') }}"><i class="fa-solid fa-user-shield"></i> PROFILE</a>
        </li>
        <li class="{{ Request::routeIs('inventory_table') ? 'active' : '' }}">
            <a href="{{ route('inventory_table') }}"><i class="fa-solid fa-warehouse"></i> INVENTORY</a>
        </li><li class="{{ Request::routeIs('inventory.audit.logs') ? 'active' : '' }}">
            <a href="{{ route('inventory.audit.logs') }}"><i class="fa-solid fa-warehouse"></i> PRODUCT DISCREPANCIES</a>
        </li>
        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i> LOGOUT
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
@endif

<!-- Sidebar Navigation -->
@if(Auth::user()->role == "Purchase Manager")
<nav id="sidebar" class="vh-100 navbar-expand-lg">
    <button class="navbar-toggler toggle-btn" type="button" onclick="toggleSidebar()">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="sidebar-header d-flex flex-row align-items-center py-3">
        @if($userImage)
            <img class="image rounded-circle" src="{{ asset('storage/userImage/' . $userImage) }}">
        @else
            <i class="fa-solid fa-circle-user fa-3x me-3"></i>
        @endif

        <div class="d-flex flex-column">
            <h6>{{ $userRole }}</h6>
            <h6>{{ $userName }}</h6>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}"><i class="fa-solid fa-user-shield"></i> DASHBOARD</a>
        </li>
        <li class="{{ Request::routeIs('show_profile') ? 'active' : '' }}">
            <a href="{{ route('show_profile') }}"><i class="fa-solid fa-user-shield"></i> PROFILE</a>
        </li>
        <li class="{{ Request::routeIs('purchase_table') ? 'active' : '' }}">
            <a href="{{ route('purchase_table') }}"><i class="fa-solid fa-money-bill"></i> PRODUCTS</a>
        </li>
        <li class="{{ Request::routeIs('purchase_order') ? 'active' : '' }}">
            <a href="{{ route('purchase_order') }}"><i class="fa-solid fa-cart-shopping"></i> ORDERS</a>
        </li>
        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i> LOGOUT
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
@endif

<!-- Sidebar Navigation -->
@if(Auth::user()->role == "Inventory Manager")
<nav id="sidebar" class="vh-100 navbar-expand-lg">
    <button class="navbar-toggler toggle-btn" type="button" onclick="toggleSidebar()">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="sidebar-header d-flex flex-row align-items-center py-3">
        @if($userImage)
            <img class="image rounded-circle" src="{{ asset('storage/userImage/' . $userImage) }}">
        @else
            <i class="fa-solid fa-circle-user fa-3x me-3"></i>
        @endif

        <div class="d-flex flex-column">
            <h6>{{ $userRole }}</h6>
            <h6>{{ $userName }}</h6>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}"><i class="fa-solid fa-user-shield"></i> DASHBOARD</a>
        </li>
        <li class="{{ Request::routeIs('show_profile') ? 'active' : '' }}">
            <a href="{{ route('show_profile') }}"><i class="fa-solid fa-user-shield"></i> PROFILE</a>
        </li>
        <li class="{{ Request::routeIs('inventory_products_table') ? 'active' : '' }}">
            <a href="{{ route('inventory_products_table') }}"><i class="fa-solid fa-warehouse"></i> PRODUCTS</a>
        </li>
        <li class="{{ Request::routeIs('inventory_table') ? 'active' : '' }}">
            <a href="{{ route('inventory_table') }}"><i class="fa-solid fa-warehouse"></i> INVENTORY</a>
        </li>
        <li class="{{ Request::routeIs('inventory.audit.logs') ? 'active' : '' }}">
            <a href="{{ route('inventory.audit.logs') }}"><i class="fa-solid fa-file"></i> PRODUCT DISCREPANCIES</a>
        </li>
        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i> LOGOUT
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
@endif

<!-- Sidebar Navigation -->
@if(Auth::user()->role == "Auditor")
<nav id="sidebar" class="vh-100 navbar-expand-lg">
    <button class="navbar-toggler toggle-btn" type="button" onclick="toggleSidebar()">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="sidebar-header d-flex flex-row align-items-center py-3">
        @if($userImage)
            <img class="image rounded-circle" src="{{ asset('storage/userImage/' . $userImage) }}">
        @else
            <i class="fa-solid fa-circle-user fa-3x me-3"></i>
        @endif

        <div class="d-flex flex-column">
            <h6>{{ $userRole }}</h6>
            <h6>{{ $userName }}</h6>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="{{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}"><i class="fa-solid fa-user-shield"></i> DASHBOARD</a>
        </li>
        <li class="{{ Request::routeIs('show_profile') ? 'active' : '' }}">
            <a href="{{ route('show_profile') }}"><i class="fa-solid fa-user-shield"></i> PROFILE</a>
        </li>
        <li class="{{ Request::routeIs('audit_inventory_table') ? 'active' : '' }}">
            <a href="{{ route('audit_inventory_table') }}"><i class="fa-solid fa-warehouse"></i>INVENTORY</a>
        </li>
        <li class="{{ Request::routeIs('inventory.audit.logs') ? 'active' : '' }}">
            <a href="{{ route('inventory.audit.logs') }}"><i class="fa-solid fa-file"></i> PRODUCT DISCREPANCIES</a>
        </li>
        {{-- <li class="{{ Request::routeIs('accounts_table') ? 'active' : '' }}">
            <a href="{{ route('show_profile') }}"><i class="fa-solid fa-file"></i> REPORT</a>
        </li> --}}
        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i> LOGOUT
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
@endif

<!-- Sidebar Navigation -->
@if(Auth::user()->role == "Salesperson")
<nav id="sidebar" class="vh-100 navbar-expand-lg">
    <button class="navbar-toggler toggle-btn" type="button" onclick="toggleSidebar()">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="sidebar-header d-flex flex-row align-items-center py-3">
        @if($userImage)
            <img class="image rounded-circle" src="{{ asset('storage/userImage/' . $userImage) }}">
        @else
            <i class="fa-solid fa-circle-user fa-3x me-3"></i>
        @endif

        <div class="d-flex flex-column">
            <h6>{{ $userRole }}</h6>
            <h6>{{ $userName }}</h6>
        </div>
    </div>
    <ul class="list-unstyled components">
        <li class="{{ Request::routeIs('show_profile') ? 'active' : '' }}">
            <a href="{{ route('show_profile') }}"><i class="fa-solid fa-user-shield"></i> PROFILE</a>
        </li>
        <li class="{{ Request::routeIs('product_sale_price_table') ? 'active' : '' }}">
            <a href="{{ route('product_sale_price_table') }}"><i class="fa-solid fa-tags"></i> PRODUCT PRICES</a>
        </li>
        <li class="{{ Request::routeIs('sales_table') ? 'active' : '' }}">
            <a href="{{ route('sales_table') }}"><i class="fa-solid fa-tags"></i> SALES</a>
        </li>
        <li class="{{ Request::routeIs('return_product_table') ? 'active' : '' }}">
            <a href="{{ route('return_product_table') }}" style="font-size: 0.9em"><i class="fa-solid fa-right-left"></i> RETURNED PRODUCTS</a>
        </li>
        <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-sign-out-alt"></i> LOGOUT
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
@endif


<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed'); // Toggle the collapsed class
    }
</script>



