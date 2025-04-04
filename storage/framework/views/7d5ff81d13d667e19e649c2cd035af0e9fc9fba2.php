<!DOCTYPE html>
<html lang="en">

<head>

    <title><?php echo $__env->yieldContent('title','Inventory'); ?></title>

    <?php echo $__env->make('include.header', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <style>
        .select2 {

            width: 100% !important;
        }
    </style>
    <link rel="stylesheet" href="<?php echo e(asset('css/machine_learning_2.css')); ?>">
</head>

<body class="theme-teal" onload="loaded()">
    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="preloader">
                <div class="spinner-layer pl-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
            <p>Por favor espera...</p>
        </div>
    </div>
    <!-- #END# Page Loader -->
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="<?php echo e(url('/')); ?>" title=""><img class="img-fluid" src="<?php echo e(url('images/logo.png')); ?>" alt=" logo" style="height: 40px;"></a>
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->
    <section>

        <?php echo $__env->make('include.sidebar', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>


        <section class="content">
            <div id="inventory" class="container-fluid">
                <div class="block-header">
                    <h2><?php echo $__env->yieldContent('page-title'); ?></h2>
                </div>

                <!-- Widgets -->

                <?php echo $__env->yieldContent('content'); ?>

            </div>
        </section>

        <?php echo $__env->make('include.footer', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>

        <script type="text/javascript">
            var base_url = "<?php echo e(url('/').'/'); ?>";
        </script>

        <script type="text/javascript">
            function loaded() {
                var segment3 = '<?php echo e(Request::segment(1)); ?>';
                var current_url = base_url + segment3;
                $('a[href="' + current_url + '"]').parents('.ml-menu').siblings('a').addClass('toggled');
                $('a[href="' + current_url + '"]').parents('.ml-menu').css('display', 'block');
            }
        </script>


        <?php echo $__env->yieldPushContent('script'); ?>


</body>

</html>