<?php

use App\Http\Controllers\Admin\CalenderManagement\CalenderManagementController;
use App\Http\Controllers\Admin\CaptainAppManagement\AuditManagement;
use App\Http\Controllers\Admin\MagaRental\MagaRentalController;
use App\Http\Controllers\Admin\CommunicationManagement\CommunicationManagementController;
use App\Http\Controllers\Admin\GuestManagement\GuestManagementController;
use App\Http\Controllers\Api\Apartments\ApartmentController;
use App\Http\Controllers\Api\Apartments\ApartmentDiscountController;
use App\Http\Controllers\Api\Apartments\ApartmentPriceController;
use App\Http\Controllers\Api\Ari\AriController;
use App\Http\Controllers\Api\Bookings\BookingController;
use App\Http\Controllers\Api\CaptainApp\AuditController;
use App\Http\Controllers\Api\CaptainApp\CleaningController;
use App\Http\Controllers\Api\CaptainApp\DeepCleaningController;
use App\Http\Controllers\Api\Discount\DiscountController;
use App\Http\Controllers\Api\CommunicationManagement\CommunicationManagement;
use App\Http\Controllers\Api\Groups\GroupController;
use App\Http\Controllers\Admin\PropertyManagement\PropertyManagementController;
use App\Http\Controllers\Api\Notifications\NotificationController;
use App\Http\Controllers\Api\Reports\Finance\SoaController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\Tasks\TaskCategoryController;
use App\Http\Controllers\Api\Tasks\TaskReviewController;
use App\Http\Controllers\Api\Tasks\TasksController;
use App\Http\Controllers\Api\Users\UserController;
use App\Http\Controllers\Api\Vendors\VendorController;
use App\Http\Controllers\Api\Notifications\MobileNotificationController;
use App\Http\Controllers\Webhooks\FallbackController;
use App\Http\Controllers\Webhooks\WebhookController;
use App\Http\Controllers\Api\Listings\ListingManagementController;
use App\Http\Controllers\Api\Listings\ListingController;
use App\Http\Controllers\Api\Listings\RoomController;
use App\Http\Controllers\Api\ScheduledMessages\TemplateController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\BookingEngine\BookingEngineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Mixpanel\MixpanelController;
use App\Http\Controllers\SendCompanyEmailApiController;
use App\Http\Controllers\Api\whatsApp\TwilioController;
use App\Http\Controllers\Webhooks\WhatsAppWebhookController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('mr/updateCalendarAirbnbToAlmosafer', [MagaRentalController::class, 'updateCalendarAirbnbToAlmosafer']);

Route::post('mr/sync-booking', [MagaRentalController::class, 'sync_booking'])->name('mr.sync-booking');

Route::middleware(['api', 'auth:sanctum', 'CheckUserBlocked'])->group(function () {

    Route::get('/contactsemail', [SendCompanyEmailApiController::class, 'sendToAll']);

    Route::resource('users', UserController::class);
    Route::post('user/logout', [UserController::class, 'logout']);
    Route::get('user/unblock/{id}', [UserController::class, 'unblock']);

    Route::middleware('throttle:5,1')->get('get-data', [DashboardApiController::class, 'get_data'])->name('get-data');

    Route::get('update/plan/{user}', [UserController::class, 'updatePlan'])->name('updatePlan');
    Route::get('check/account/plan/activate/{id}', [UserController::class, 'isAccountPlanActivate'])->name('isAccountPlanActivate');
    Route::post('is/user/listed/{id}', [UserController::class, 'isUserListed'])->name('isUserListed');
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications', [NotificationController::class, 'store']);
    Route::get('/notifications/count', [NotificationController::class, 'countUnread']);
    Route::get('notifications/{user}', [NotificationController::class, 'show']);
    Route::get('groups', [UserController::class, 'user']);
    Route::get('get-connection-link', [UserController::class, 'getConnectionLink']);
    Route::resource('apartments', ApartmentController::class);
    Route::post('bookings/{id}/upload-payment', [BookingController::class, 'uploadPaymentReferences']);


    Route::prefix('airbnb/')->group(function () {
        Route::resource('listings', ListingController::class);
        Route::resource('rooms', RoomController::class);
        Route::put('listings/description/{listingId}', [ListingController::class, 'updateDescription']);
        Route::put('listings/pricing/{listingId}', [ListingController::class, 'updatePrices']);
        Route::get('images/{listingId}', [ListingController::class, 'getImages']);
        Route::post('listings/image', [ListingController::class, 'addImage']);
        Route::delete('image/{id}', [ListingController::class, 'deleteImage']);
    });

    Route::get('get-apartment-by-user/{user}', [ApartmentController::class, 'getApartmentByUser']);
    Route::get('fetch-host-types', [UserController::class, 'fetchHostType']);
    Route::post('listing-url', [ApartmentController::class, 'createListingUrl']);
    Route::resource('mobile-notifications', MobileNotificationController::class);
    Route::get('mobile-notifications/unread/count', [MobileNotificationController::class, 'getUnreadCount']);
    Route::post('mobile-notifications/mark/all/as/read', [MobileNotificationController::class, 'markAllAsRead']);
    Route::post('fcm-token-update', [UserController::class, 'fcm_token_update']);

    Route::get('fetch-tiers/{hostTypeId}', [UserController::class, 'fetch_tiers']);

    Route::get('fetchPropertyByHostID/{user}', [BookingController::class, 'fetchPropertyByHostID'])->name('fetchPropertyByHostID');
    Route::post('bookings/{id}/upload-payment', [BookingController::class, 'uploadPaymentReferences']);
    Route::delete('delete-booking-reference/{id}', [BookingController::class, 'deleteBookingReference']);
    Route::resource('bookings', BookingController::class);
    Route::get('get-bookings-by-date/{date}', [BookingController::class, 'getBookingByDate']);
    Route::get('get-bookings-by-user/{user}', [BookingController::class, 'getBookingByApartmentId']);
    Route::get('get-bookings-by-property/{id}', [BookingController::class, 'getBookingByPropertyID']);
    Route::get('fetch-all-bookings', [BookingController::class, 'fetchAllBooking']);
    Route::get('bookings-confirmation/{id}', [BookingController::class, 'bookingConfirmation']);
    Route::get('get-bookings-by-id/{id}', [BookingController::class, 'getBookingByID']);
    Route::get('get-livedin-bookings-by-id/{id}', [BookingController::class, 'getLivedInBookingByID']);
    Route::get('get-bookings-by-listing/{id}', [BookingController::class, 'getBookingByListingId']);

    // Route::get('fetchThreads', [CommunicationManagementController::class, 'fetchThreads'])->name('fetchThreads');
    // Route::get('fetchThreadByID/{id}', [CommunicationManagementController::class, 'fetchThreadByID'])->name('fetchThreadByID');
    // Route::post('sendMessage', [CommunicationManagementController::class, 'sendMessage'])->name('sendMessage');

    Route::resource('discounts', DiscountController::class);
    Route::resource('apartment-prices', ApartmentPriceController::class);
    Route::get('get-apartment-price-by-apartment/{id}', [ApartmentPriceController::class, 'getApartmentPriceByApartmentId']);
    Route::post('updateListingCalender', [CalenderManagementController::class, 'updateRateAndRestriction'])->name('updateListingCalender');
    Route::post('block/calendar', [CalenderManagementController::class, 'blockCalendar']);
    Route::post('updateListingCalender/bulk', [CalenderManagementController::class, 'updateListingCalenderBulk'])->name('updateListingCalender.bulk');
    Route::get('ari', [AriController::class, 'index'])->name('ari.index');
    Route::post('rate_multiplier', [AriController::class, 'rate_multiplier'])->name('rate_multiplier');
    Route::get('rate_multiplier/options', [AriController::class, 'rate_multiplier_options'])->name('rate_multiplier_options');

    Route::post('ari/update', [AriController::class, 'update'])->name('ari.update');
    Route::resource('vendors', VendorController::class);
    Route::resource('task-categories', TaskCategoryController::class);
    Route::resource('tasks', TasksController::class);
    Route::get('get-tasks-by-date/{date}', [TasksController::class, 'getTaskByDate']);
    Route::get('get-tasks-by-status/{status}', [TasksController::class, 'getTaskByStatus']);

    Route::get('get-vendor-list', [VendorController::class, 'get_vendor_list']);
    Route::get('get-vendor-by-id/{id}', [VendorController::class, 'get_vendor_by_id']);

    Route::get('get-services', [TasksController::class, 'getservices']);
    Route::get('get-property_by_userid', [TasksController::class, 'getpropertybyUserId']);
    Route::get('get-vendor-by-service/{serviceid}', [TasksController::class, 'getvendorbyServiceId']);
    Route::get('get-task-list', [TasksController::class, 'get_task_list']);
    Route::get('get-task-list-date', [TasksController::class, 'get_task_list_with_Date']);
    Route::get('get-task-list-date-filter/{date}', [TasksController::class, 'get_task_list_date_filter']);


    Route::get('get-task-detail/{taskid}', [TasksController::class, 'get_task_detail']);


    Route::get('get-task-trigger-list', [TasksController::class, 'get_task_trigger_list']);
    Route::get('get-task-trigger-list-date', [TasksController::class, 'get_task_trigger_list_with_date']);
    Route::get('get-task-trigger-list-date-filter/{date}', [TasksController::class, 'get_task_trigger_list_date_filter']);
    Route::get('get-trigger-detail/{triggerid}', [TasksController::class, 'get_trigger_detail']);


    Route::post('post-templates_task', [TasksController::class, 'insert_update_task']);

    Route::post('post-templates_task_trigger', [TasksController::class, 'insertUpdateTasksTrigger']);

    Route::post('update_task_status', [TasksController::class, 'updateTaskStatus']);

    Route::post('delete-task', [TasksController::class, 'task_Delete']);

    Route::post('delete-trigger', [TasksController::class, 'trigger_Delete']);

    Route::post('delete-task-invoice', [TasksController::class, 'deleteinvoice']);


    Route::resource('task-reviews', TaskReviewController::class);
    Route::get('get-task-review-by-task-status/{status}', [TaskReviewController::class, 'getTaskReviewByTaskStatus']);
    Route::post('/changelanguage', [MixpanelController::class, 'changelanguage']);
    Route::get('listings/all', [ListingManagementController::class, 'getAllListings'])->name('getAllListings');

    Route::post('fetchListingsByUserId/{user}', [ListingManagementController::class, 'fetchListingsByUserId'])->name('fetchListingsByUserId');
    Route::get('fetchListingsPricingSetting/{listing_id}', [ListingManagementController::class, 'pricingSetting'])->name('pricingSetting');
    Route::put('updatePricingSetting/{listing_id}', [ListingManagementController::class, 'updatePricingSetting'])->name('updatePricingSetting');

    Route::get('fetchThreads', [CommunicationManagement::class, 'fetchThreads'])->name('fetchThreads');
    Route::put('is_read/{thread}', [CommunicationManagement::class, 'is_read'])->name('is_read');
    Route::put('is_starred/{thread}', [CommunicationManagement::class, 'is_starred'])->name('is_starred');
    Route::put('is_archived/{thread}', [CommunicationManagement::class, 'is_archived'])->name('is_archived');
    Route::put('is_mute/{thread}', [CommunicationManagement::class, 'is_mute'])->name('is_mute');
    Route::get('fetchThreadByID/{id}', [CommunicationManagementController::class, 'fetchThreadByID'])->name('fetchThreadByID');


    Route::post('sendMessagewithattachment', [CommunicationManagementController::class, 'sendMessagewithattachment'])->name('sendMessagewithattachment');

    Route::post('sendmessageadminandattachment', [CommunicationManagementController::class, 'sendmessageadmin'])->name('sendmessageadminandattachment');

    Route::post('sendMessage', [CommunicationManagementController::class, 'sendMessage'])->name('sendMessage');

    Route::get('requests/new-entry', [CommunicationManagementController::class, 'getNewRequests']);
    Route::post('/bookings/manage-request', [CommunicationManagementController::class, 'manageRequest']);
    Route::get('/booking-details/{thread_id}/{type}', [CommunicationManagementController::class, 'getBookingDetailsByThreadId']);
    Route::get('/threads/{id}', [CommunicationManagementController::class, 'fetchThreadsById']);
    Route::put('/cancel/booking/{id}', [CommunicationManagementController::class, 'cancelBooking'])->name('cancelBooking');

    // Route::get('requests/new-entry', [CommunicationManagementController::class, 'getNewRequests']);
    // Route::post('/bookings/manage-request', [CommunicationManagementController::class, 'manageRequest']);
    // Route::get('/booking-details/{thread_id}/{type}', [CommunicationManagementController::class, 'getBookingDetailsByThreadId']);
    // Route::get('/threads/{id}', [CommunicationManagementController::class, 'fetchThreadsById']);


    Route::get('user-channel/{user}', [ListingManagementController::class, 'getUserChannel'])->name('getUserChannel');
    Route::post('mapListing/{user}', [ListingManagementController::class, 'mapListing'])->name('mapListing');

    Route::post('customMapListing/{user}', [ListingManagementController::class, 'customMapListing'])->name('customMapListing');

    Route::get('getPerformanceData', [ListingManagementController::class, 'getPerformanceData'])->name('getPerformanceData');
    Route::get('getPerformance', [ListingManagementController::class, 'getPerformance'])->name('getPerformance');
    Route::get('fetchListingDetailsByListingID', [ListingManagementController::class, 'fetchListingDetailsByListingID'])->name('fetchListingDetailsByListingID');
    Route::get('create/channex/account/{user}', [UserController::class, 'createChannexAccount'])->name('createChannexAccount');
    Route::resource('groups', GroupController::class);
    Route::resource('reviews', ReviewController::class);
    Route::get('guest/reviews', [ReviewController::class, 'hostReviews'])->name('hostReviews');
    Route::get('guest/reviews/{id}', [ReviewController::class, 'hostReviewById'])->name('hostReviewById');
    Route::post('guest/review/reply', [ReviewController::class, 'hostReviewReply'])->name('hostReviewReply');
    // Captain App Routes
    Route::resource('deepCleaning', DeepCleaningController::class);
    Route::get('getDeepCleaningCount', [DeepCleaningController::class, 'getDeepCleaningCount'])->name('getDeepCleaningCount');

    Route::post('create/deepcleaning/comment', [DeepCleaningController::class, 'createDeepCleaningComment'])->name('createDeepCleaningComment');
    Route::post('deepcleaning_uploadmultipleImages', [DeepCleaningController::class, 'uploadMultipleImages'])->name('deepcleaning_uploadmultipleImages');

    Route::post('/send-whatsapp', [TwilioController::class, 'store']);


    Route::resource('audit', AuditController::class);
    Route::get('getAuditCount', [AuditController::class, 'getAuditCount'])->name('getAuditCount');

    Route::post('create/audit/comment', [AuditController::class, 'createAuditComment'])->name('createAuditComment');
    Route::post('audit_uploadmultipleImages', [AuditController::class, 'uploadMultipleImages'])->name('audit_uploadmultipleImages');

    Route::get('get-audit-checklist/{audit_id}', [AuditController::class, 'GetAuditChecklist'])->name('GetAuditChecklist');

    Route::get('get-audit-section-checklist/{audit_id}/{audit_detail_id}', [AuditController::class, 'GetAuditSectionChecklist'])->name('GetAuditSectionChecklist');
    Route::get('get-audit-task-checklist/{audit_id}/{audit_detail_id}', [AuditController::class, 'GetAuditTaskChecklist'])->name('GetAuditTaskChecklist');
    Route::post('/update-audit-task-checklists', [AuditController::class, 'updateAuditTask'])->name('updateAuditTask');

    Route::get('get-audit-task-full-checklist/{audit_id}/{audit_detail_id}', [AuditController::class, 'GetAuditTaskChecklist'])->name('GetAuditTaskChecklist');

    Route::delete('/audit-task-image/{id}', [AuditController::class, 'deleteAuditTaskImage']);




    Route::get('get-checklist/{id}/{category}', [AuditController::class, 'GetChecklist'])->name('GetChecklist');


    Route::get('getphototasks', [AuditController::class, 'GetPhotoTasks'])->name('GetPhotoTasks');
    Route::get('getphototaskscount', [AuditController::class, 'GetPhotoTasksCount'])->name('GetPhotoTasksCount');
    Route::get('getphototaskscount-detail/{id}', [AuditController::class, 'GetPhotoTasksshow'])->name('GetPhotoTasksshow');
    Route::put('/update-photo-review/{id}', [AuditController::class, 'updatephotoreview'])->name('updatephotoreview');
    Route::put('/update-photo-status/{id}', [AuditController::class, 'updatephotostatus'])->name('updatephotostatus');




    Route::get('tasks', [CleaningController::class, 'getAllTasks'])->name('getAllTasks');
    Route::get('getCleaningCount', [CleaningController::class, 'getCleaningCount'])->name('getCleaningCount');
    Route::get('getAllTaskCount', [CleaningController::class, 'getAllTaskCount'])->name('getAllTaskCount');
    Route::resource('cleaning', CleaningController::class);
    Route::post('create/cleaning/comment', [CleaningController::class, 'createCleaningComment'])->name('createCleaningComment');

    Route::post('Cleaning_uploadMultipleImages', [CleaningController::class, 'uploadMultipleImages'])->name('Cleaning_uploadMultipleImages');

    Route::get('get-cleaning-checklist/{cleaning_id}', [CleaningController::class, 'GetCleaningChecklist'])->name('GetCleaningChecklist');
    Route::get('get-cleaning-checklist_detail/{property_checklist_id}/{cleaning_id}', [CleaningController::class, 'GetCleaningChecklist_detail'])->name('GetCleaningChecklist_detail');




    // routes/api.php
    Route::post('/update-task-checklists', [CleaningController::class, 'updateChecklistTask'])->name('updateChecklistTask');


    Route::resource('templates', TemplateController::class);



    Route::get('get-booking-calendar', [CalenderManagementController::class, 'getBookingCalendar']);

    Route::get('get-monthly-booking-calendar', [CalenderManagementController::class, 'getMonthlyBookingCalendar']);


    // Webhook Controller
});

// Booking Engine Routes

// , \App\Http\Middleware\CorsMiddleware::class

Route::middleware(['throttle:1000,1'])->group(function () {

    Route::get('get-5-star-apartments', [BookingEngineController::class, 'get_5_star_apartments']);
    Route::get('get-all-listings', [BookingEngineController::class, 'get_all_listings']);
    Route::get('guest/authenticate', [BookingEngineController::class, 'authenticate']);

    Route::post('guest/google-authenticate', [BookingEngineController::class, 'google_authenticate']);

    Route::post('guest/logout', [BookingEngineController::class, 'logout']);
    Route::get('fetch-update-listings', [BookingEngineController::class, 'fetch_update_listings']);
    Route::get('get-all-listings-filters', [BookingEngineController::class, 'get_all_listings_filters']);
    Route::post('guest/authenticate', [BookingEngineController::class, 'authenticate']);
    Route::post('guest/register', [BookingEngineController::class, 'register']);
    Route::get('apartment/{id}', [BookingEngineController::class, 'fetchListingDetails']);
    Route::get('get-blocked-dates/{id}', [BookingEngineController::class, 'getBlockedDates']);

    Route::get('get-price/{id}/{start_date}/{end_date}', [BookingEngineController::class, 'getPrice']);



    Route::get('currency-exchange-rate-update', [BookingEngineController::class, 'currency_exchange_rate_update']);
    Route::post('update-guest-currency', [BookingEngineController::class, 'update_guest_currency']);
    Route::post('guest-favorite', [BookingEngineController::class, 'guest_favorite']);
    Route::post('checkout', [BookingEngineController::class, 'checkout']);
    Route::get('get-payment-status', [BookingEngineController::class, 'getPaymentStatus']);
    Route::post('create-payment', [BookingEngineController::class, 'createPaymentStatus']);

    Route::get('get-wish-list', [BookingEngineController::class, 'getWishList']);

    Route::get('get-places', [BookingEngineController::class, 'getPlaces']);


    Route::post('add-user-searches', [BookingEngineController::class, 'add_user_searches']);
    Route::get('get-user-searches', [BookingEngineController::class, 'get_user_searches']);

    Route::post('update-profile', [BookingEngineController::class, 'updateProfile']);

    Route::post('update-profile-dp', [BookingEngineController::class, 'updateProfileDp']);

    Route::get('get-guest-bookings', [BookingEngineController::class, 'getGuestBookings']);

    Route::get('get-guest-booking-detail/{booking_id}', [BookingEngineController::class, 'getGuestBookingDetail']);


    Route::get('get-unique-amenities', [BookingEngineController::class, 'get_unique_amenities']);

    Route::post('booking-modified', [BookingEngineController::class, 'bookingModified']);

    Route::post('booking-modified-pay', [BookingEngineController::class, 'bookingModifiedPay']);

    Route::post('booking-cancelled', [BookingEngineController::class, 'bookingCancelled']);

    Route::get('refund-process', [BookingEngineController::class, 'refundProcess']);

    Route::post('add-review', [BookingEngineController::class, 'addReview']);

    Route::get('get-review', [BookingEngineController::class, 'getReview']);

    Route::get('get-notification-review', [BookingEngineController::class, 'getNotificationReview']);

    Route::post('voucher-redeemed', [BookingEngineController::class, 'voucher_redeemed']);

    // Route::resource('bookingEngine', BookingEngineController::class);
});


Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle']);
Route::get('get-properties', [PropertyManagementController::class, 'get_properties']);

Route::get('get-calendar', [CalenderManagementController::class, 'get_calendar']);



Route::post('update-price', [CalenderManagementController::class, 'update_price']);
Route::get('/host/soa/{user_id}', [SoaController::class, 'index']);
Route::get('download/host/soa/{id}', [SoaController::class, 'downloadSoa']);

Route::get('get-channel-callback-api/{user}', [UserController::class, 'getChannelCallback'])->name('get-channel-callback-api');

Route::get('email/verification/{user}', [UserController::class, 'verfiyEmailAddress'])->name('verfiyEmailAddress');
Route::get('user/verification/status/by/{user}', [UserController::class, 'emailVerificationStatus'])->name('emailVerificationStatus');

Route::post('verify-otp', [UserController::class, 'verifyOtp']);
Route::post('forget-password', [UserController::class, 'forgetPassword']);
Route::post('password/update', [UserController::class, 'passwordUpdate'])->name('forgetPassword');
Route::post('send-otp', [UserController::class, 'sendOtp']);
Route::post('resend-email-verification/{user}', [UserController::class, 'resendEmailVerification']);

Route::post('user/register', [UserController::class, 'store']);
Route::get('email/checker/{user}', [UserController::class, 'emailChecker']);
Route::post('user/login', [UserController::class, 'authenticate']);
Route::post('social-authetication', [UserController::class, 'socialAuthentication']);
Route::post('user/biometric/register', [UserController::class, 'registerUserBiometric']);
Route::post('user/biometric/login', [UserController::class, 'loginUserBiometric']);
Route::post('save/customer/identifier', [UserController::class, 'saveCustomerIdentifier']);
Route::post('user/verify/email', [UserController::class, 'verifyEmail']);
Route::post('webhook', [WebhookController::class, 'handle']);
Route::get('/update-booking-threads-manual', [WebhookController::class, 'updateBookingThreadsmanual']);
Route::get('/get-fetch-booking-message/{booking_id}', [WebhookController::class, 'fetchBookingMessage']);
Route::post('myfatoorah-webhook', [WebhookController::class, 'handle']);

Route::get('countries', [UserController::class, 'countries']);
Route::get('states', [UserController::class, 'states'])->name('states');
Route::get('cities', [UserController::class, 'cities'])->name('cities');
Route::resource('listings', ListingManagementController::class);



Route::fallback('FallbackController@handle');
Route::fallback([FallbackController::class, 'handle']);

Route::get('/app-redirect', function () {
    return redirect()->away('livedin://');

    // return redirect('livedin://');
});


Route::get('/logs', function () {
    if (app()->environment('production')) {
        abort(404);
    }

    $log_file = storage_path('logs/laravel.log');
    if (file_exists($log_file)) {
        return response()->file($log_file, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'inline; filename="' . basename($log_file) . '"'
        ]);
    } else {
        return "Log file does not exist.";
    }
});
