<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\controllers\AuthController;
use App\Http\controllers\CompanyController;
use App\Http\controllers\EmployeeController;
use App\Http\controllers\ChatController;
use App\Http\controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/login',function (){
    return response()->json(['error'=>true, 'message'=> 'please login first!']);
})->name('login');
Route::post('/login',[AuthController::class,'login']);
Route::post('/company/register',[AuthController::class,'registerCompany']);
Route::post('/employee/register',[AuthController::class,'registerEmployee']);

/* End Authentication  Section */

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('CheckSuspended')->group(function () {
        Route::post('/company/profile',[CompanyController::class,'store']);
        Route::get('/company/profile',[CompanyController::class,'index']);
        Route::post('/company/addPost',[CompanyController::class,'addPost']);
        Route::get('/company/getPosts',[CompanyController::class,'getPosts']);
        Route::post('/company/status',[CompanyController::class,'editStatus']);
        Route::post('/company/addRating',[CompanyController::class,'addRating']); // new
        Route::get('/company/ourEmployees',[CompanyController::class,'getAllEmployees']); // في قصة باخر الشي

        Route::middleware('checkApplicationsOwner')->group(function () {
            Route::get('/company/applications/{id}',[CompanyController::class,'getApplications']); // في قصة باخر الشي    
        });

        Route::post('/employee/addRating/{id}',[EmployeeController::class,'addRating']); // new
        Route::post('/employee/profile',[EmployeeController::class,'store']);
        Route::get('/employee/profile',[EmployeeController::class,'index']);
        Route::post('/employee/addOrder',[EmployeeController::class,'addOrder']);
        Route::get('/employee/getPosts',[EmployeeController::class,'getPosts']);
        Route::get('/employee/getPosts/{id}',[EmployeeController::class,'getPostsOneCompany']);
        Route::get('/employee/getMyApplications',[EmployeeController::class,'getMyApplications']);
        Route::get('/employee/allCompanies',[EmployeeController::class,'getAllCompanies']); 
        Route::get('/employee/profileOneCompany/{id}',[EmployeeController::class,'getProfileOneCompany']);
        Route::get('/employee/notifications',[EmployeeController::class,'getNotification']);

        
        Route::post('/chat',[ChatController::class,'store']);
        Route::delete('/chat/{id}', [ChatController::class, 'deleteMessage']);
        Route::get('/chat/messages/{id}', [ChatController::class, 'getMessages']);
 
        Route::get('/admin/suspended/{id}', [AdminController::class, 'edit']);

        Route::get('/admin/makeAdmin/{id}', [AdminController::class, 'makeAdmin']);

        Route::get('/admin/statistics', [AdminController::class, 'getStatistics']);
    });
});

/*
قصة البحث
قصة نسيت كلمة المرور
قصة تعديل البروفايل عند التنين مشان الباسوورد 
عرض كل الطلبات يلي انا قدمتها انا موظف مع حالتها ///
قصة عرض البوستات عند الموظف والبوستات هاي لشركة واحدة انا بختارها ///
عرض الموظفين لشركتي هاي انا سويتها ///

*/