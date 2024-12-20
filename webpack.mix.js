let mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js("resources/assets/js/app.js", "public/js").sass("resources/assets/sass/app.scss", "public/css");
mix.js("resources/assets/js/vendor.js", "public/js").sass("resources/assets/sass/vendor.scss", "public/css");
mix.js("resources/assets/js/product.js", "public/js").sass("resources/assets/sass/product.scss", "public/css");
mix.js("resources/assets/js/stock.js", "public/js").sass("resources/assets/sass/stock.scss", "public/css");
mix.js("resources/assets/js/category.js", "public/js").sass("resources/assets/sass/category.scss", "public/css");
mix.js("resources/assets/js/invoice.js", "public/js").sass("resources/assets/sass/invoice.scss", "public/css");
mix.js("resources/assets/js/report.js", "public/js").sass("resources/assets/sass/report.scss", "public/css");
mix.js("resources/assets/js/role.js", "public/js").sass("resources/assets/sass/role.scss", "public/css");
mix.js("resources/assets/js/user.js", "public/js").sass("resources/assets/sass/user.scss", "public/css");
mix.js("resources/assets/js/customer.js", "public/js").sass("resources/assets/sass/customer.scss", "public/css");
mix.js("resources/assets/js/dashboard.js", "public/js").sass("resources/assets/sass/dashboard.scss", "public/css");
//mix.sass("resources/assets/sass/app.scss", "public/css");
