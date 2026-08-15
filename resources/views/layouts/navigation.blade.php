<ul class="sidebar-menu" id="sidebar-menu">
    <li>
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <i class="ri-dashboard-line menu-icon"></i>
            <span>{{ __('app.nav_dashboard') }}</span>
        </x-nav-link>
    </li>

    @canany(['leaves.create', 'missions.create'])
        <li class="sidebar-menu-group-title">{{ __('app.nav_hr') }}</li>

        <li class="dropdown {{ request()->routeIs('leave-requests.*', 'mission-requests.*', 'approvals.*') ? 'active' : '' }}">
            <a href="javascript:void(0)">
                <i class="ri-suitcase-line menu-icon"></i>
                <span>{{ __('app.nav_hr') }}</span>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('leave-requests.index') }}" class="{{ request()->routeIs('leave-requests.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> {{ __('leaves.nav_leaves') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('mission-requests.index') }}" class="{{ request()->routeIs('mission-requests.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> {{ __('missions.nav_missions') }}
                    </a>
                </li>
                @canany(['leaves.approve', 'missions.approve', 'leaves.approve_any', 'missions.approve_any'])
                    <li>
                        <a href="{{ route('approvals.index') }}" class="{{ request()->routeIs('approvals.*') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> {{ __('app.nav_approvals') }}
                        </a>
                    </li>
                @endcanany
            </ul>
        </li>
    @endcanany

    @canany(['orders.view', 'business-partners.view'])
        <li class="sidebar-menu-group-title">{{ __('app.nav_orders') }}</li>

        @can('orders.view')
            <li>
                <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                    <i class="ri-shopping-cart-2-line menu-icon"></i>
                    <span>{{ __('app.nav_orders') }}</span>
                </x-nav-link>
            </li>
        @endcan

        @can('business-partners.view')
            <li>
                <x-nav-link :href="route('admin.business-partners.index')" :active="request()->routeIs('admin.business-partners.*')">
                    <i class="ri-contacts-book-2-line menu-icon"></i>
                    <span>{{ __('app.nav_business_partners') }}</span>
                </x-nav-link>
            </li>
        @endcan
    @endcanany

    @canany(['products.view', 'warehouse.view', 'stock.view'])
        <li class="sidebar-menu-group-title">{{ __('app.nav_inventory') }}</li>

        <li class="dropdown {{ request()->routeIs('admin.products.*', 'admin.product-categories.*', 'admin.product-brands.*', 'admin.warehouses.*', 'admin.stock-movements.*') ? 'active' : '' }}">
            <a href="javascript:void(0)">
                <i class="ri-archive-2-line menu-icon"></i>
                <span>{{ __('app.nav_inventory') }}</span>
            </a>
            <ul class="sidebar-submenu">
                @can('products.view')
                    <li>
                        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> {{ __('products.title_index') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.product-categories.index') }}" class="{{ request()->routeIs('admin.product-categories.*') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> {{ __('products.title_categories') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.product-brands.index') }}" class="{{ request()->routeIs('admin.product-brands.*') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> {{ __('products.title_brands') }}
                        </a>
                    </li>
                @endcan
                @can('warehouse.view')
                    <li>
                        <a href="{{ route('admin.warehouses.index') }}" class="{{ request()->routeIs('admin.warehouses.*') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-success-main w-auto"></i> {{ __('warehouse.title_warehouses') }}
                        </a>
                    </li>
                @endcan
                @can('stock.view')
                    <li>
                        <a href="{{ route('admin.stock-movements.index') }}" class="{{ request()->routeIs('admin.stock-movements.index') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-cyan w-auto"></i> {{ __('warehouse.title_stock_movements') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.stock-movements.overview') }}" class="{{ request()->routeIs('admin.stock-movements.overview') ? 'active-page' : '' }}">
                            <i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> {{ __('warehouse.title_stock_overview') }}
                        </a>
                    </li>
                @endcan
            </ul>
        </li>
    @endcanany

    @can('tasks.create')
        <li class="sidebar-menu-group-title">{{ __('app.nav_tasks') }}</li>
        <li>
            <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')">
                <i class="ri-task-line menu-icon"></i>
                <span>{{ __('app.nav_tasks') }}</span>
            </x-nav-link>
        </li>
    @endcan

    @can('files.create')
        <li class="sidebar-menu-group-title">{{ __('app.nav_files') }}</li>
        <li>
            <x-nav-link :href="route('files.index')" :active="request()->routeIs('files.*')">
                <i class="ri-folder-3-line menu-icon"></i>
                <span>{{ __('app.nav_files') }}</span>
            </x-nav-link>
        </li>
    @endcan

    @canany(['users.view', 'organization.view', 'roles.manage', 'settings.manage', 'activitylog.view', 'face-device-events.view'])
        <li class="sidebar-menu-group-title">{{ __('app.nav_administration') }}</li>
    @endcanany

    @can('users.view')
        <li>
            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                <i class="ri-team-line menu-icon"></i>
                <span>{{ __('app.nav_users') }}</span>
            </x-nav-link>
        </li>
    @endcan

    @can('organization.view')
        <li class="dropdown {{ request()->routeIs('admin.companies.*', 'admin.branches.*', 'admin.departments.*', 'admin.units.*', 'admin.positions.*') ? 'active' : '' }}">
            <a href="javascript:void(0)">
                <i class="ri-git-branch-line menu-icon"></i>
                <span>{{ __('app.nav_organization') }}</span>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('admin.companies.index') }}" class="{{ request()->routeIs('admin.companies.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> {{ __('app.companies') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> {{ __('app.branches') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.departments.index') }}" class="{{ request()->routeIs('admin.departments.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> {{ __('app.departments') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.units.index') }}" class="{{ request()->routeIs('admin.units.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-success-main w-auto"></i> {{ __('app.units') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.positions.index') }}" class="{{ request()->routeIs('admin.positions.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-danger-main w-auto"></i> {{ __('app.positions') }}
                    </a>
                </li>
            </ul>
        </li>
    @endcan

    @can('roles.manage')
        <li>
            <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
                <i class="ri-shield-user-line menu-icon"></i>
                <span>{{ __('app.nav_roles') }}</span>
            </x-nav-link>
        </li>
    @endcan

    @can('settings.manage')
        <li class="dropdown {{ request()->routeIs('admin.settings.*', 'admin.lookup-types.*') ? 'active' : '' }}">
            <a href="javascript:void(0)">
                <i class="ri-settings-4-line menu-icon"></i>
                <span>{{ __('app.nav_settings') }}</span>
            </a>
            <ul class="sidebar-submenu">
                <li>
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> {{ __('app.nav_settings') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.lookup-types.index') }}" class="{{ request()->routeIs('admin.lookup-types.*') ? 'active-page' : '' }}">
                        <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> {{ __('app.lookup_values') }}
                    </a>
                </li>
            </ul>
        </li>
    @endcan

    @can('activitylog.view')
        <li>
            <x-nav-link :href="route('admin.activity-log.index')" :active="request()->routeIs('admin.activity-log.*')">
                <i class="ri-history-line menu-icon"></i>
                <span>{{ __('app.nav_activity_log') }}</span>
            </x-nav-link>
        </li>
    @endcan

    @can('face-device-events.view')
        <li>
            <x-nav-link :href="route('admin.face-device-events.index')" :active="request()->routeIs('admin.face-device-events.*')">
                <i class="ri-fingerprint-line menu-icon"></i>
                <span>{{ __('app.nav_face_device_events') }}</span>
            </x-nav-link>
        </li>
        <li>
            <x-nav-link :href="route('admin.attendance-report.index')" :active="request()->routeIs('admin.attendance-report.*')">
                <i class="ri-bar-chart-2-line menu-icon"></i>
                <span>{{ __('app.nav_attendance_report') }}</span>
            </x-nav-link>
        </li>
    @endcan

    @can('work-shifts.manage')
        <li>
            <x-nav-link :href="route('admin.work-shifts.index')" :active="request()->routeIs('admin.work-shifts.*')">
                <i class="ri-calendar-2-line menu-icon"></i>
                <span>{{ __('app.nav_work_shifts') }}</span>
            </x-nav-link>
        </li>
    @endcan
</ul>
