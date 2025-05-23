<aside class="page-sidebar">
    <div class="page-logo">
        <a href="#" class="page-logo-link press-scale-down d-flex align-items-center position-relative" data-toggle="modal" data-target="#modal-shortcut">
            <img src="{{asset ('template/img/logo.png') }}" alt="SmartAdmin WebApp" aria-roledescription="logo">
            <span class="page-logo-text mr-1">PHONE SHOP</span>
            <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
        </a>
    </div>
    <!-- BEGIN PRIMARY NAVIGATION -->
    <nav id="js-primary-nav" class="primary-nav" role="navigation">
        <div class="nav-filter">
            <div class="position-relative">
                <input type="text" id="nav_filter_input" placeholder="Filter menu" class="form-control" tabindex="0">
                <a href="#" onclick="return false;" class="btn-primary btn-search-close js-waves-off" data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar">
                    <i class="fal fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="info-card">
            <img src="{{asset ('template/img/demo/avatars/avatar-admin.png') }}" class="profile-image rounded-circle" alt="{{ auth()->user()->name}}">
            <div class="info-card-text">
                <a href="#" class="d-flex align-items-center text-white">
                    <span class="text-truncate text-truncate-sm d-inline-block">
                        {{ auth()->user()->name}}
                    </span>
                </a>
                <span class="d-inline-block text-truncate text-truncate-sm">{{ auth()->user()->email}}</span>
            </div>
            <img src="{{asset ('template/img/card-backgrounds/cover-2-lg.png') }}" class="cover" alt="cover">
            <a href="#" onclick="return false;" class="pull-trigger-btn" data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar" data-focus="nav_filter_input">
                <i class="fal fa-angle-down"></i>
            </a>
        </div>
        <!--
        TIP: The menu items are not auto translated. You must have a residing lang file associated with the menu saved inside dist/media/data with reference to each 'data-i18n' attribute.
        -->
        <ul id="js-nav-menu" class="nav-menu">
            @php
                $dashboardActive = request()->is('/');
            @endphp
            <li class="{{ $dashboardActive ? 'active' : '' }}">
                <a href="{{ url('/') }}" title="Dashboard" data-filter-tags="blank page">
                    <i class="fal fa-chart-pie"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>

            @php
                $ProductActive = request()->is('products*') 
                || request()->is('brands*')
                || request()->is('categories*');
            @endphp
            <li class="{{ $ProductActive ? 'active open' : '' }}">
                <a href="#" title="Product Management" data-filter-tags="product management">
                    <i class="fal fa-box"></i>
                    <span class="nav-link-text">Product Management</span>
                </a>
                <ul>
                    <li class="{{ request()->is('products*') ? 'active' : '' }}">
                        <a href="{{ route('products.index') }}" title="Product" data-filter-tags="products">
                            <span class="nav-link-text">Products List</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('categories*') ? 'active' : '' }}">
                        <a href="{{ route('categories.index') }}" title="Category" data-filter-tags="categories">
                            <span class="nav-link-text">Categories</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('brands*') ? 'active' : '' }}">
                        <a href="{{ route('brands.index') }}" title="Brand" data-filter-tags="brands">
                            <span class="nav-link-text">Brands</span>
                        </a>
                    </li>
                </ul>
            </li>

            @php
                $userManagementActive = request()->is('roles*') || request()->is('permissions*');
            @endphp
            <li class="nav-title">User Management</li>
            <li class="{{ $userManagementActive ? 'active open' : '' }}">
                <a href="#" title="User Management" data-filter-tags="user management">
                    <i class="fal fa-users-cog"></i>
                    <span class="nav-link-text">User Management</span>
                </a>
                <ul>
                    <li class="{{ request()->is('roles*') ? 'active' : '' }}">
                        <a href="{{ route('roles.index') }}" title="Roles" data-filter-tags="roles">
                            <span class="nav-link-text">Roles</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('permissions*') ? 'active' : '' }}">
                        <a href="{{ route('permissions.index') }}" title="Permissions" data-filter-tags="permissions">
                            <span class="nav-link-text">Permissions</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('users*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" title="Users" data-filter-tags="users">
                            <span class="nav-link-text">Users</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <div class="filter-message js-filter-message bg-success-600"></div>
    </nav>
    <!-- END PRIMARY NAVIGATION -->
</aside>
