        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <!-- User Info -->
            <div class="user-info">
                <div class="image">
                    <img src="<?php echo e(url('images/user.png')); ?>" width="60" height="60" alt="User" />
                </div>
                <div class="info-container">
                    <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo e(Auth::user()->name); ?></div>
                    <div class="email"><?php echo e(Auth::user()->email); ?></div>
                    <div class="btn-group user-helper-dropdown">
                        <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                        <ul class="dropdown-menu pull-right">
                            <li><a href="<?php echo e(url('password-change')); ?>"><i class="material-icons">person</i>Perfil</a></li>
                            <li><a href="<?php echo e(url('logout')); ?>"><i class="material-icons">input</i>Desconectar</a></li>
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
        <li <?php if(Route::currentRouteName()=='' ): ?> class="active" <?php endif; ?>>
            <a href="<?php echo e(url('/')); ?>">
                <i class="material-icons">dashboard</i>
                <span>Dashboard</span>
            </a>
        </li>

        <?php
        $side_menu = Session::get('side_menu');
        ?>

        <?php if($side_menu): ?>
            <?php $__currentLoopData = $side_menu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(Route::has($menu->menu_url)): ?>
                    <li <?php if(Route::currentRouteName() == $menu->menu_url): ?> class="active" <?php endif; ?>>
                        <a href="<?php echo e(route($menu->menu_url)); ?>">
                            <i class="material-icons"><?php echo e($menu->icon); ?></i>
                            <span><?php echo e($menu->menu_name); ?></span> <!-- Usar 'menu_name' que ahora es 'name' -->
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </ul>
</div>

        </aside>
        <!-- #END# Left Sidebar -->