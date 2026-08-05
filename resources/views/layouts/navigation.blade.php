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

    @canany(['users.view', 'organization.view', 'roles.manage', 'settings.manage', 'activitylog.view'])
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
</ul>
