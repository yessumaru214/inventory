<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
|PUT AND DELETE method not work in some windows server that's why laiter we use 
 get and post mehod for update and delete
|
*/
use App\Http\Controllers\MachineLearningController_2;
Route::group(['middleware'=> ['auth','check.permission']],function(){

// dashaboard 
Route::get('/','DashboardController@index');
Route::get('info-box','DashboardController@InfoBox');
// vendor 

Route::resource('supplier','VendorController');
Route::get('supplier/delete/{id}','VendorController@destroy');
Route::post('supplier/update/{id}','VendorController@update');
Route::get('vendor-list','VendorController@Vendor');

// product category 
Route::resource('category','CategoryController');
// category delete 
Route::get('category/delete/{id}','CategoryController@destroy');
//category update
Route::post('category/update/{id}','CategoryController@update');
Route::get('category-list','CategoryController@CategoryList');
Route::get('all-category','CategoryController@AllCategory');

// product 
Route::resource('product','ProductController');
Route::get('product/delete/{id}','ProductController@destroy');
Route::post('product/update/{id}','ProductController@update');
Route::get('product-list','ProductController@ProductList');
Route::get('category/product/{id}','ProductController@productByCategory');

// customer 
Route::resource('customer','CustomerController');
Route::get('customer/delete/{id}','CustomerController@destroy');
Route::post('customer/update/{id}','CustomerController@update');
Route::get('customer-list','CustomerController@CustomerList');

//Stock
Route::resource('stock','StockController');
Route::get('stock/delete/{id}','StockController@destroy');
Route::post('stock/update/{id}','StockController@update');
Route::get('stock-list','StockController@StockList');
Route::get('chalan-list/chalan/{id}','StockController@ChalanList');
Route::get('stock-asset','StockController@StockAsset');
Route::post('stock-update','StockController@StockUpdate');

// invoice 
Route::resource('invoice','InvoiceController');
Route::get('invoice/delete/{id}','InvoiceController@destroy');
Route::post('invoice/update/{id}','InvoiceController@update');
Route::get('invoice-list','InvoiceController@InvoiceList');
Route::get('get/invoice/number','InvoiceController@getLastInvoice');

// payment 
Route::resource('payment','PaymentController');
Route::get('payment/delete/{id}','PaymentController@destroy');

// Report 
Route::resource('role','RoleController');
Route::get('role/delete/{id}','RoleController@destroy');
Route::post('role/update/{id}','RoleController@update');
Route::get('role-list','RoleController@RoleList');
Route::post('permission','RoleController@Permission');
Route::get('report',['as'=>'report.index','uses'=>'ReportingController@index']);
Route::get('get-report',['as'=>'report.store','uses'=>'ReportingController@store']);
Route::get('print-report',['as'=>'report.print','uses'=>'ReportingController@Print']);

/* Prediction and Training
Route::get('prediction', 'PredictionController@index')->name('prediction.index');
Route::post('/prediction/train', 'PredictionController@train')->name('prediction.train');

Route::get('extra-trees', 'ExtraTreesController@index')->name('extra_trees.index');
Route::post('extra-trees/train', 'ExtraTreesController@train')->name('extra_trees.train');

Route::get('machine-learning', 'MachineLearningController@index')->name('machine_learning.index');
Route::post('machine-learning/train', 'MachineLearningController@train')->name('machine_learning.train');
*/
// Rutas para MachineLearningController_2
Route::get('machine-learning_2', 'MachineLearningController_2@index')->name('machine_learning_2.index');
Route::post('machine-learning_2/train', 'MachineLearningController_2@train')->name('machine_learning_2.train');
/*
Route::get('quarterly-predict', 'QuarterlyPredictionController@index')->name('quarterly_predict.index');
Route::post('quarterly-predict', 'QuarterlyPredictionController@predict')->name('quarterly_predict');

Route::get('/predict', 'PredictionController@index')->name('predict.index');
Route::post('/predict', 'PredictionController@predict')->name('predict')


Route::get('/predict', 'PredictionController@index')->name('predict.index');

Route::post('/predict', 'PredictionController@predict')->name('predict');
;*/
// user management 
Route::resource('user','UserManageController');
Route::get('user/delete/{id}','UserManageController@destroy');
Route::post('user/update/{id}','UserManageController@update');
Route::get('user-list','UserManageController@UserList');
Route::get('comapany-setting',['as'=>'company.index','uses'=>'CompanyController@index']);
Route::post('comapany-setting',['as'=>'company.store','uses'=>'CompanyController@store']);
Route::get('password-change',['as'=>'password.index','uses'=>'SettingController@index']);
Route::post('password-change',['as'=>'password.store','uses'=>'SettingController@store']);
Route::get('user-role','RoleController@userRole');
Route::get('logout','UserController@logout');
});
Auth::routes();
Route::get('/home', 'HomeController@index')->name('home');