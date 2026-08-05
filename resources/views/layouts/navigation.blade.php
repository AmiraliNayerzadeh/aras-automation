<ul class="nav nav-pills flex-column gap-1 p-2">
    <li class="nav-item">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <i class="bi bi-speedometer2 me-2"></i>{{ __('app.nav_dashboard') }}
        </x-nav-link>
    </li>

    @can('users.view')
        <li class="nav-item">
            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                <i class="bi bi-people me-2"></i>{{ __('app.nav_users') }}
            </x-nav-link>
        </li>
    @endcan

    @can('organization.view')
        <li class="nav-item">
            <x-nav-link :href="route('admin.companies.index')" :active="request()->routeIs('admin.companies.*', 'admin.branches.*', 'admin.departments.*', 'admin.units.*', 'admin.positions.*')">
                <i class="bi bi-diagram-3 me-2"></i>{{ __('app.nav_organization') }}
            </x-nav-link>
        </li>
    @endcan

    @can('roles.manage')
        <li class="nav-item">
            <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
                <i class="bi bi-shield-lock me-2"></i>{{ __('app.nav_roles') }}
            </x-nav-link>
        </li>
    @endcan

    @can('settings.manage')
        <li class="nav-item">
            <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*', 'admin.lookup-types.*')">
                <i class="bi bi-gear me-2"></i>{{ __('app.nav_settings') }}
            </x-nav-link>
        </li>
    @endcan

    @can('activitylog.view')
        <li class="nav-item">
            <x-nav-link :href="route('admin.activity-log.index')" :active="request()->routeIs('admin.activity-log.*')">
                <i class="bi bi-clock-history me-2"></i>{{ __('app.nav_activity_log') }}
            </x-nav-link>
        </li>
    @endcan
</ul>
