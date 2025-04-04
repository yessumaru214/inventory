        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <!-- User Info -->
            <div class="user-info">
                <div class="image">
                    <img src="{{ url('images/user.png') }}" width="60" height="60" alt="User" />
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ Auth::user()->name }}</div>
                    <div class="email">{{ Auth::user()->email  }}</div>
                    <div class="btn-group user-helper-dropdown">
                        <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                        <ul class="dropdown-menu pull-right">
                            <li><a href="{{ url('password-change') }}"><i class="material-icons">person</i>Perfil</a></li>
                            <li><a href="{{ url('logout') }}"><i class="material-icons">input</i>Desconectar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- #User Info -->
            <!-- Menu -->
            <!-- filepath: /C:/laragon/www/inventory/resources/views/include/sidebar.blade.php -->
<!-- #User Info -->
<!-- Menu -->
<!-- filepath: /C:/laragon/www/inventory/resources/views/include/sidebar.blade.php -->
<!-- Menu -->
<div class="menu">
    <ul class="list">
        <li class="header">NAVEGACIÓN PRINCIPAL</li>
        <li @if(Route::currentRouteName()=='' ) class="active" @endif>
            <a href="{{ url('/') }}">
                <i class="material-icons">dashboard</i>
                <span>Dashboard</span>
            </a>
        </li>

        @php
        $side_menu = Session::get('side_menu');
        @endphp

        @if($side_menu)
            @foreach($side_menu as $menu)
                @if(Route::has($menu->menu_url))
                    <li @if(Route::currentRouteName() == $menu->menu_url) class="active" @endif>
                        <a href="{{ route($menu->menu_url) }}">
                            <i class="material-icons">{{ $menu->icon }}</i>
                            <span>{{ $menu->menu_name }}</span> <!-- Usar 'menu_name' que ahora es 'name' -->
                        </a>
                    </li>
                @endif
            @endforeach
        @endif
    </ul>
</div>

        </aside>
        <!-- #END# Left Sidebar -->