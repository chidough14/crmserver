<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminchatController;
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
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DraftController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\OfflineFollowersMessageController;
use App\Http\Controllers\UserschatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
Route::get('/events-within-hour', [EventController::class, 'getEventsWithinNextHour']);

Route::get('files/{filename}', function ($filename) {
    try {
        $path = storage_path('app/public/files/' . $filename);
        if (file_exists($path)) {
            return response()->file($path);
        } else {
            return response('File not found', 404);
        }
    } catch (Exception $e) {
        // Log the exception for debugging
        Log::error($e->getMessage());
        return response('Internal Server Error', 500);
    }
});

Route::get('/download-file/{filename}', [CommentController::class, 'download']);




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
    Route::post('/admin-users-bulk-delete', [AdminController::class, 'bulkDeleteUsers']);
    Route::post('/admin-users-bulk-update', [AdminController::class, 'bulkUpdateUsers']);

    //Company
    Route::post('/companies', [CompanyController::class, 'createCompany']);
    Route::get('/companies', [CompanyController::class, 'getCompanies']);
    Route::get('/companies/search',  [CompanyController::class, 'search']);
    Route::get('/companies/{companyId}',  [CompanyController::class, 'getSingleCompany']);
    Route::patch('/companies/{companyId}',  [CompanyController::class, 'updateCompany']);
    Route::delete('/companies/{companyId}',  [CompanyController::class, 'deleteCompany']);
    Route::post('/companies/{companyId}/lists',  [CompanyController::class, 'addCompanyToList']);
    Route::post('/companies-bulk-delete',  [CompanyController::class, 'bulkDeleteCompanies']);
    Route::post('/companies-add-bulk',  [CompanyController::class, 'bulkAddCompanies']);
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
    Route::post('/mylists/bulk-transfer', [CompanyListController::class, 'bulkTransferList']);
    Route::post('/mylists/bulk-delete', [CompanyListController::class, 'bulkDeleteLists']);
    Route::get('/mylists-with-trashed', [CompanyListController::class, 'getAllListsWithTrashed']);
    Route::get('/mylists-dashboard', [CompanyListController::class, 'getDashboardLists']);
    Route::get('/mylists-restore/{listId}', [CompanyListController::class, 'restoreList']);
    Route::delete('/mylists-force-delete/{listId}', [CompanyListController::class, 'forceDeleteList']);
    Route::post('/mylists-bulk-restore', [CompanyListController::class, 'bulkRestoreList']);
    Route::post('/mylists-bulk-force-delete', [CompanyListController::class, 'bulkForceDelete']);

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
    Route::get('/activities-with-trashed', [ActivityController::class, 'getActivitiesWithTrashed']);
    Route::post('/activities-bulk-transfer', [ActivityController::class, 'bulkTransfer']);
    Route::post('/activities-bulk-delete', [ActivityController::class, 'bulkDeleteActivities']);
    Route::post('/activities-bulk-restore', [ActivityController::class, 'bulkRestoreActivities']);
    Route::get('/activity-restore/{id}', [ActivityController::class, 'restoreActivity']);
    Route::delete('/activities-force-delete/{id}', [ActivityController::class, 'forceDeleteActivity']);
    Route::post('/activities-bulk-force-delete', [ActivityController::class, 'bulkForceDeleteActivities']);

    Route::post('/upload-files-and-save/{id}', [ActivityController::class, 'uploadFile']);

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
     Route::post('/products-bulk-delete',  [ProductController::class, 'bulkDeleteProducts']);
     Route::post('/products-add-bulk',  [ProductController::class, 'bulkAddProducts']);


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
    Route::post('/meetings-bulk-delete', [MeetingController::class, 'bulkDeleteMeeting']);

    //Messages

    Route::post('/messages', [MessageController::class, 'createMessage']);
    Route::get('/inboxmessages', [MessageController::class, 'getInboxMessages']);
    Route::get('/outboxmessages', [MessageController::class, 'getOutboxMessages']);
    Route::get('/messages/{messageId}', [MessageController::class, 'getSingleMessage']);
    Route::patch('/messages/{messageId}', [MessageController::class, 'updateMessage']);
    Route::delete('/messages/{messageId}', [MessageController::class, 'deleteMessage']);
    Route::patch('/messages/{messageId}/read', [MessageController::class, 'readMessage']);

    Route::post('/mass-delete-messages', [MessageController::class, 'massDeleteMessages']);
    Route::post('/mass-mark-as-read', [MessageController::class, 'massReadMessages']);

    //Settings
    Route::patch('/settings', [SettingsController::class, 'updateSetting']);

    //Dashboard graphs
    Route::get('/dashboard-total-products/{owner}', [ActivityController::class, 'dashboardTotalProducts']);
    Route::get('/dashboard-total-sales-users', [ActivityController::class, 'dashboardTotalSalesUsers']);
    Route::get('/dashboard-total-sales-topproducts', [ActivityController::class, 'dashboardTotalSalesTopProducts']);


    //admin announcements
    Route::get('/announcements', [AnnouncementController::class, 'getAnnouncements']);
    Route::post('/announcements', [AnnouncementController::class, 'addAnnouncement']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'getAnnouncement']);
    Route::patch('/announcements/{id}', [AnnouncementController::class, 'updateAnnouncement']);
    Route::delete('/announcements/{id}', [AnnouncementController::class, 'deleteAnnouncement']);

    Route::get('/dashboardannouncements', [AnnouncementController::class, 'dashboardAnnouncements']);

    Route::post('/filter-announcements', [AnnouncementController::class, 'filterAnnouncements']);
    Route::get('/search-announcements', [AnnouncementController::class, 'searchAnnouncements']);
    Route::get('/filter-announcements-by-date/{criteria}', [AnnouncementController::class, 'filterAnnouncementsByDate']);
    Route::post('/announcements-bulk-delete', [AnnouncementController::class, 'bulkDelete']);
    Route::post('/announcements-bulk-add', [AnnouncementController::class, 'bulkAdd']);

    // announcements categories
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::post('/categories', [CategoryController::class, 'addCategory']);
    Route::get('/categories/{id}', [CategoryController::class, 'getCategory']);
    Route::patch('/categories/{id}', [CategoryController::class, 'updateCategory']);
    Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);
    Route::post('/categories-bulk-add', [CategoryController::class, 'bulkAddCategory']);

    //Followers
    Route::post('/follow-user', [FollowerController::class, 'followUser']);
    Route::post('/unfollow-user', [FollowerController::class, 'unFollowUser']);
    Route::get('/followers', [FollowerController::class, 'getMyFollowers']);
    Route::get('/followed', [FollowerController::class, 'getMyFollowed']);
    Route::get('/followers/{id}', [FollowerController::class, 'getUserFollowers']);
    Route::get('/followed/{id}', [FollowerController::class, 'getUserFollowed']);

    Route::post('/add-message-for-offline-followers', [OfflineFollowersMessageController::class, 'addMessage']);
    Route::get('/followers-offline-activities', [OfflineFollowersMessageController::class, 'getMessages']);
    Route::delete('/followers-offline-activities/{id}', [OfflineFollowersMessageController::class, 'deleteMessage']);

    // Activity comments
    Route::post('/comment', [CommentController::class, 'addComment']);
    Route::patch('/comment/{id}', [CommentController::class, 'editComment']);
    Route::delete('/comment/{id}', [CommentController::class, 'deleteComment']);

    Route::get('/comment/{id}/upvote', [CommentController::class, 'upVote']);
    Route::get('/comment/{id}/downvote', [CommentController::class, 'downVote']);

    Route::get('/users-upvotes', [CommentController::class, 'getUserUpvotes']);
    Route::get('/users-downvotes', [CommentController::class, 'getUserDownvotes']);
    Route::post('/upload-files',  [CommentController::class, 'uploadFiles']);

    // Drafts
    Route::get('/drafts', [DraftController::class, 'getDrafts']);
    Route::post('/drafts', [DraftController::class, 'addDraft']);
    Route::get('/drafts/{id}', [DraftController::class, 'getDraft']);
    Route::patch('/drafts/{id}', [DraftController::class, 'updateDraft']);
    Route::delete('/drafts/{id}', [DraftController::class, 'deleteDraft']);
    Route::post('/bulk-delete-drafts', [DraftController::class, 'bulkDeleteDrafts']);

    //Conversations
    Route::post('/conversations', [ConversationController::class, 'addConversation']);
    Route::get('/conversations/{mode}', [ConversationController::class, 'fetchConversations']);
    Route::delete('/conversations/{id}', [ConversationController::class, 'deleteConversation']);
    Route::post('/bulk-delete-conversations', [ConversationController::class, 'bulkDeleteConversations']);

    //admin chats
    Route::get('/adminchats/{id}', [AdminchatController::class, 'getChats']);
    Route::post('/adminchats', [AdminchatController::class, 'addChats']);
    Route::post('/upload-adminchatfiles-and-save', [AdminchatController::class, 'uploadFile']);

    // User to user chats
    Route::get('/users-chats/{id}', [UserschatController::class, 'getChats']);
    Route::post('/users-chats', [UserschatController::class, 'addChats']);

    Route::post('/upload-chatfiles-and-save', [UserschatController::class, 'uploadFile']);

    //labels
    Route::get('/labels', [LabelController::class, 'getLabels']);
    Route::post('/labels', [LabelController::class, 'addLabel']);
    Route::patch('/labels/{id}', [LabelController::class, 'updateLabel']);
    Route::delete('/labels/{id}', [LabelController::class, 'deleteLabel']);

    
});
