<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyListController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LogoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Public Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/send-reset-password-email', [PasswordResetController::class, 'send_reset_password_email']);
Route::post('/reset-password/{token}', [PasswordResetController::class, 'reset']);
Route::post('/addlogout', [LogoutController::class, 'addlogout']);
Route::get('/userlogout/{id}', [LogoutController::class, 'userlogout']);



// Private Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/loggeduser', [UserController::class, 'loggedUser']);

    Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::get('/get-profile/{user_id}', [ProfileController::class, 'getUserProfile']);

    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::patch('/users/{id}', [UserController::class, 'updateUserDetails']);

    //admin
    Route::patch('/admin-users/{id}', [AdminController::class, 'updateUserDetails']);
    Route::delete('/admin-users/{id}', [AdminController::class, 'deleteUser']);

    //Company
    Route::post('/companies', [CompanyController::class, 'createCompany']);
    Route::get('/companies', [CompanyController::class, 'getCompanies']);
    Route::get('/companies/search',  [CompanyController::class, 'search']);
    Route::get('/companies/{companyId}',  [CompanyController::class, 'getSingleCompany']);
    Route::patch('/companies/{companyId}',  [CompanyController::class, 'updateCompany']);
    Route::delete('/companies/{companyId}',  [CompanyController::class, 'deleteCompany']);
    Route::post('/companies/{companyId}/lists',  [CompanyController::class, 'addCompanyToList']);
    Route::delete('/companies/{companyId}/lists',  [CompanyController::class, 'deleteCompanyFromList']);

    // Lists

    Route::post('/mylists', [CompanyListController::class, 'createList']);
    Route::get('/mylists', [CompanyListController::class, 'getAllLists']);
    Route::get('/mylists/{listId}', [CompanyListController::class, 'getSingleList']);
    Route::patch('/mylists/{listId}', [CompanyListController::class, 'updateList']);
    Route::delete('/mylists/{listId}', [CompanyListController::class, 'deleteList']);
    Route::get('/userListsAndCompanies', [CompanyListController::class, 'getUserListsAndCompanies']);
    Route::get('/mylists/{listId}/clone', [CompanyListController::class, 'cloneList']);
    Route::post('/mylists/{listId}/transfer', [CompanyListController::class, 'transferList']);
    Route::get('/mylists-dashboard', [CompanyListController::class, 'getDashboardLists']);

    Route::get('/filter-lists/{critera}', [CompanyListController::class, 'filterLists']);
    Route::get('/search-lists',  [CompanyListController::class, 'searchLists']);
    Route::post('/upload-list', [CompanyListController::class, 'uploadList']);

    //Activities

    Route::post('/activities', [ActivityController::class, 'createActivity']);
    Route::get('/activities', [ActivityController::class, 'getActivities']);
    Route::get('/activities/{activityId}', [ActivityController::class, 'getSingleActivity']);
    Route::patch('/activities/{activityId}', [ActivityController::class, 'updateActivity']);
    Route::delete('/activities/{activityId}', [ActivityController::class, 'deleteActivity']);

    Route::post('/activities/{activityId}/addUpdateProduct', [ActivityController::class, 'addUpdateProduct']);
    Route::delete('/activities/{activityId}/deleteProduct', [ActivityController::class, 'deleteProduct']);
    Route::get('/activities/{activityId}/clone', [ActivityController::class, 'cloneActivity']);

    Route::post('/activities/{activityId}/transfer', [ActivityController::class, 'transferActivity']);
    Route::get('/activities-summary', [ActivityController::class, 'getActivitiesSummary']);
    Route::get('/filter-activities/{critera}', [ActivityController::class, 'filterActivities']);
    Route::get('/search-activities',  [ActivityController::class, 'searchActivities']);

    //Events

    Route::post('/events', [EventController::class, 'createEvent']);
    Route::get('/events', [EventController::class, 'getEvents']);
    Route::get('/events/{eventId}', [EventController::class, 'getSingleEvent']);
    Route::patch('/events/{eventId}', [EventController::class, 'updateEvent']);
    Route::delete('/events/{eventId}', [EventController::class, 'deleteEvent']);

    Route::get('/dashboardevents', [EventController::class, 'dashboardEvents']);

     //Products

     Route::post('/products', [ProductController::class, 'createProduct']);
     Route::get('/products', [ProductController::class, 'getProducts']);
     Route::get('/products/{productId}', [ProductController::class, 'getSingleProduct']);
     Route::patch('/products/{productId}', [ProductController::class, 'updateProduct']);
     Route::delete('/products/{productId}', [ProductController::class, 'deleteProduct']);
     Route::get('/products-all', [ProductController::class, 'getProductsNoPagination']);


    //Invoices

    Route::post('/invoices', [InvoiceController::class, 'createInvoice']);
    Route::get('/invoices', [InvoiceController::class, 'getInvoices']);
    Route::get('/invoices/{invoiceId}', [InvoiceController::class, 'getSingleInvoice']);
    Route::patch('/invoices/{invoiceId}', [InvoiceController::class, 'updateInvoice']);
    Route::delete('/invoices/{invoiceId}', [InvoiceController::class, 'deleteInvoice']);

    Route::post('/invoices/{invoiceId}/addUpdateProduct', [InvoiceController::class, 'addUpdateProduct']);
    Route::delete('/invoices/{invoiceId}/deleteProduct', [InvoiceController::class, 'deleteProduct']);
    Route::get('/filter-invoices/{critera}', [InvoiceController::class, 'filterInvoices']);

    Route::post('/create-order', [InvoiceController::class, 'saveStripeOrder']);
    Route::get('/stripe-orders', [InvoiceController::class, 'getStripeOrders']);

    // Meetings
    Route::post('/meetings', [MeetingController::class, 'createMeeting']);
    Route::get('/meetings', [MeetingController::class, 'getMeetings']);
    Route::patch('/meetings/{meetingId}', [MeetingController::class, 'updateMeeting']);
    Route::delete('/meetings/{meetingId}', [MeetingController::class, 'deleteMeeting']);
    Route::get('/meeting/join/{meetingId}', [MeetingController::class, 'getMeetingDetails']);
    Route::get('/notifications', [MeetingController::class, 'getNotifications']);

    //Messages

    Route::post('/messages', [MessageController::class, 'createMessage']);
    Route::get('/inboxmessages', [MessageController::class, 'getInboxMessages']);
    Route::get('/outboxmessages', [MessageController::class, 'getOutboxMessages']);
    Route::get('/messages/{messageId}', [MessageController::class, 'getSingleMessage']);
    Route::patch('/messages/{messageId}', [MessageController::class, 'updateMessage']);
    Route::delete('/messages/{messageId}', [MessageController::class, 'deleteMessage']);
    Route::patch('/messages/{messageId}/read', [MessageController::class, 'readMessage']);

    //Settings
    Route::patch('/settings', [SettingsController::class, 'updateSetting']);

    //Dashboard graphs
    Route::get('/dashboard-total-products/{owner}', [ActivityController::class, 'dashboardTotalProducts']);
    Route::get('/dashboard-total-sales-users', [ActivityController::class, 'dashboardTotalSalesUsers']);
    Route::get('/dashboard-total-sales-topproducts', [ActivityController::class, 'dashboardTotalSalesTopProducts']);
    
});
