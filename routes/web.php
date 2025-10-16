<?php

use App\Http\Controllers\Admin\ApartmentManagement\ApartmentManagementController;
use App\Http\Controllers\Admin\Authentication\AuthController;
use App\Http\Controllers\Admin\BookingManagement\BookingManagementController;
use App\Http\Controllers\Admin\CalenderManagement\CalenderManagementController;
use App\Http\Controllers\Admin\ChannelLinkManagement\ChannelLinkManagementController;
use App\Http\Controllers\Admin\ChannelManagement\ChannelManagementController;
use App\Http\Controllers\Admin\CommunicationManagement\CommunicationManagementController;
use App\Http\Controllers\Admin\CommunicationManagement\ChatAnalytics;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupManagement\GroupManagementController;
use App\Http\Controllers\Admin\HostType\HostTypeManagementController;
use App\Http\Controllers\Admin\IcalManagementController;
use App\Http\Controllers\Admin\LeadManagement\LeadManagementController;
use App\Http\Controllers\Admin\ListingManagement\ListingManagementController;
use App\Http\Controllers\Admin\ListingUrlManagement\ListingUrlManagementController;
use App\Http\Controllers\Admin\ModuleManagement\ModuleManagementController;
use App\Http\Controllers\Admin\ModulePermissionManagement\ModulePermissionManagementController;
use App\Http\Controllers\Admin\PropertyManagement\PropertyManagementController;
use App\Http\Controllers\Admin\Reports\FinanceMangementController;
use App\Http\Controllers\Admin\Reports\RevenueManagementController;
use App\Http\Controllers\Admin\ReviewManaement\ReviewManagementController;
use App\Http\Controllers\Admin\RoleManagement\RoleManagementController;
use App\Http\Controllers\Admin\TaskManagement\TaskManagementController;
use App\Http\Controllers\Admin\TaskInvoiceManagement\TaskInvoiceManagementController;
use App\Http\Controllers\Admin\UserManagement\UserManagementController;
use App\Http\Controllers\Admin\GuestManagement\GuestManagementController;
use App\Http\Controllers\Admin\VendorManagement\VendorManagementController;
use App\Http\Controllers\Admin\ServiceManagement\ServiceManagementController;
use App\Http\Controllers\Admin\ServiceCategoryManagement\ServiceCategoryManagementController;
use App\Http\Controllers\Api\Notifications\NotificationController;
use App\Http\Controllers\Admin\CaptainAppManagement\DeepCleaningManagement;
use App\Http\Controllers\Admin\CaptainAppManagement\AuditManagement;
use App\Http\Controllers\Admin\CaptainAppManagement\CleaningManagement;
use App\Http\Controllers\Admin\PaymentReconciliation\PaymentReconciliationController;

use App\Http\Controllers\Admin\HostOnBoard\HostaboardController;
use App\Http\Controllers\Admin\HostOnBoard\RevenueActivationAuditController;
use App\Http\Controllers\Admin\HostOnBoard\SalesActivationAuditController;
use App\Http\Controllers\Admin\HostOnBoard\AuditListingController;
use App\Http\Controllers\Admin\HostOnBoard\AuditBackendOpController;
use App\Http\Controllers\Admin\HostOnBoard\AuditListingMappingController;
use App\Http\Controllers\Admin\HostOnBoard\HostRentalLeaseController;

use App\Http\Controllers\Admin\InvoiceManagement\InvoiceController;


use App\Http\Controllers\Admin\VoucherManagement\VoucherManagementController;

use App\Http\Controllers\Admin\MagaRental\MagaRentalController;

use App\Http\Controllers\Admin\RevenueTriggers\RevenueTriggersController;

use App\Http\Controllers\Admin\Linkrepository\LinkrepositoryController;

use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\RolePermission\RolePermissionController;

use App\Http\Controllers\Admin\BookingEngine\BookingEngineController;

use App\Http\Controllers\Reports\BookingPaymentReconciliationController;
use App\Http\Controllers\Webhooks\WebhookController;


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/send-notification', [TestController::class, 'sendPushNotification']);

Route::get('/testNotification', [TestController::class, 'testNotification']);

Route::get('/testemailcheckin', [TestController::class, 'testemailcheckin']);


Route::get('/testemailcheckout', [TestController::class, 'testemailcheckout']);


Route::get('/checkinpdf', [BookingEngineController::class, 'checkinpdf']);

Route::get('/checkoutpdf', [BookingEngineController::class, 'checkoutpdf']);


Route::get('/webhook-test', [WebhookController::class, 'test']);


Route::middleware('guest')->group(function () {

    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'authenticate'])->name('login.post');
});



Route::get('reset-password/{user}', [UserManagementController::class, 'resetPassword'])->name('reset-password-host');
Route::get('password/reset/success', [UserManagementController::class, 'resetPasswordSuccess'])->name('resetPasswordSuccess');

Route::get('auto-revenue-triggers', [RevenueTriggersController::class, 'AutoRevenueTrigger'])->name('revenue-triggers.AutoRevenueTrigger');
// Route::get('temp-revert-price', [RevenueTriggersController::class, 'temp_revert_price'])->name('revenue-triggers.temp_revert_price');

Route::middleware('auth')->group(function () {

    Route::get('mr/get_property_by_id', [MagaRentalController::class, 'get_property_by_id'])->name('mr.get_property_by_id');

    Route::get('create-almosafer-property/{id}', [MagaRentalController::class, 'createAlmosaferProperty'])->name('create.almosafer.property');

    // Route::get('create-almosafer-branch', [MagaRentalController::class, 'createAlmosaferBranch'])->name('create.almosafer.branch');

    // Route::get('remove-almosafer-record', [MagaRentalController::class, 'deletePropertyRecord'])->name('delete.property.record');

    Route::resource('voucher-management', VoucherManagementController::class);

    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('revenue-triggers/revert_price', [RevenueTriggersController::class, 'revert_price'])->name('revenue-triggers.revert_price');
    Route::get('revenue-triggers/view-logs/{listing_id}', [RevenueTriggersController::class, 'view_logs'])->name('revenue-triggers.view-logs');

    Route::get('revenue-triggers/auto-revenue', [RevenueTriggersController::class, 'auto_revenue'])->name('revenue-triggers.auto-revenue');

    Route::post('revenue-triggers/set-auto-trigger', [RevenueTriggersController::class, 'set_auto_trigger'])->name('revenue-triggers.set-auto-trigger');


    Route::resource('revenue-triggers', RevenueTriggersController::class);



    Route::get('fetchThreadsAdmin', [CommunicationManagementController::class, 'fetchThreads'])->name('fetchThreadsAdmin');
    Route::get('fetchThreadByIDAdmin/{id}', [CommunicationManagementController::class, 'fetchThreadByID'])->name('fetchThreadByIDAdmin');

    Route::get('/chatreport', [ChatAnalytics::class, 'index'])->name('chatanalytics.index');

    // Booking Request Submit
    Route::post('booking-request-submit', [CommunicationManagementController::class, 'booking_request_submit'])->name('booking-request-submit');

    Route::post('sendMessageAdmin', [CommunicationManagementController::class, 'sendMessage'])->name('sendMessageAdmin');

    Route::post('sendmessageadminandattachment', [CommunicationManagementController::class, 'sendmessageadmin'])->name('sendmessageadminandattachment');


    Route::post('approveOrRejectInquiry', [CommunicationManagementController::class, 'approveOrRejectInquiry'])->name('approveOrRejectInquiry');

    Route::resource('user-management', UserManagementController::class);
    Route::get('manage-permissions/{id}', [UserManagementController::class, 'managePermissions'])->name('user-management.manage-permissions');
    Route::put('update-permissions/{id}', [UserManagementController::class, 'updatePermissions'])->name('user-management.update-permissions');

    Route::resource('guest-management', GuestManagementController::class);
    Route::resource('hostaboard', HostaboardController::class);
    Route::resource('revenue-activation-audit', RevenueActivationAuditController::class);
    Route::resource('sales-activation-audit', SalesActivationAuditController::class);
    Route::resource('listing-audit', AuditListingController::class);
    Route::resource('backend-ops', AuditBackendOpController::class);

    Route::resource('listing-audit-mapping', AuditListingMappingController::class);
    Route::resource('host-rental-lease', HostRentalLeaseController::class);


    Route::delete('/document/{id}', [HostaboardController::class, 'destroy'])->name('document.destroy');
    Route::delete('/destroyownerdocument/{id}', [HostaboardController::class, 'destroyownerdocument'])->name('document.destroyownerdocument');

    Route::resource('linkrepository', LinkRepositoryController::class);
    Route::get('/get-listings-by-host/{host_id}', [LinkrepositoryController::class, 'getListingsByHost']);



    Route::resource('group-management', GroupManagementController::class);
    Route::resource('communication-management', CommunicationManagementController::class);
    Route::resource('property-management', PropertyManagementController::class);

    Route::resource('apartment-management', ApartmentManagementController::class);
    Route::get('apartment-image-delete/{apartmentImage}', [ApartmentManagementController::class, 'apartmentImageDelete'])->name('apartment-image-delete');
    Route::post('/upload-apartment-image', [ApartmentManagementController::class, 'uploadImage'])->name('image.upload.ajax');

    Route::get('property-management/rooms/iframe/{property}', [PropertyManagementController::class, 'propertyIframe'])->name('property.management.rooms.iframe');
    Route::get('property-management/channel/iframe/{property}', [PropertyManagementController::class, 'channelIframe'])->name('property.management.channel.iframe');
    Route::resource('connect-link-management', ChannelLinkManagementController::class);
    Route::resource('channel-management', ChannelManagementController::class);
    Route::resource('booking-management', BookingManagementController::class);

    Route::resource('payment-reconciliation', PaymentReconciliationController::class);
    Route::get('finance/booking/payment/reconciliation', [BookingPaymentReconciliationController::class, 'index'])->name('finance.booking.payment.reconciliation');
    Route::get('finance/report', [FinanceMangementController::class, 'financeReportIndex'])->name('finance.financeReportIndex');

    Route::get('booking-image-delete/{id}', [BookingManagementController::class, 'bookingImageDelete'])->name('booking-image-delete');
    Route::get('reference-image-delete/{id}', [BookingManagementController::class, 'ReferenceImageDelete'])->name('reference-image-delete');
    Route::get('syncBookings', [BookingManagementController::class, 'syncBookings'])->name('booking.syncBookings');
    Route::get('edit/ota/booking/{id}', [BookingManagementController::class, 'editOtaBooking'])->name('booking.editOtaBooking');
    Route::put('update/ota/booking/{id}', [BookingManagementController::class, 'updateOtaBooking'])->name('booking.updateOtaBooking');

    Route::delete('update/ota/booking/deleteFile/{id}', [BookingManagementController::class, 'deleteFile'])->name('booking.deleteFile');

    Route::post('booking-management-import', [BookingManagementController::class, 'importBookings'])->name('booking.import');
    Route::resource('lead-management', LeadManagementController::class);
    Route::post('fetchGuestByGuestId', [GuestManagementController::class, 'fetchGuestByGuestId'])->name('fetchGuestByGuestId');
    Route::get('checkForBookingInquiryDetails/{id}', [CommunicationManagementController::class, 'checkForBookingInquiryDetails'])->name('checkForBookingInquiryDetails');

    Route::get('fetchListingInfo', [ListingManagementController::class, 'fetchListingInfo'])->name('fetchListingInfo');

    Route::get('fetchCheckInOutBlocked', [ListingManagementController::class, 'fetchCheckInOutBlocked'])->name('fetchCheckInOutBlocked');

    Route::get('bookings/otas', [BookingManagementController::class, 'index_ota'])->name('bookings.ota');
    Route::get('activate-channel/{id}', [ChannelManagementController::class, 'activateChannel'])->name('activate-channel');
    Route::get('get-channel-callback', [ChannelLinkManagementController::class, 'getChannelCallback'])->name('get-channel-callback');
    Route::resource('listing-management', ListingManagementController::class);
    Route::get('/listing-data-shift', [ListingManagementController::class, 'listingDataShift'])->name('listing.listingDataShift');
    Route::get('/test/bookingcom/connection', [ChannelManagementController::class, 'testBookingComConnection'])->name('listing.testBookingComConnection');
    Route::get('/create/bookingcom/connection', [ChannelManagementController::class, 'createBookingComView'])->name('listing.createBookingComConnection');
    Route::post('/listing-data-shift', [ListingManagementController::class, 'listingDataShiftUpdate'])->name('listing.listingDataShiftUpdate');

    Route::post('/get-activation-details', [ListingManagementController::class, 'getactivationdetail'])->name('activation.getactivationdetail');


    Route::post('update-activation-detail', [ListingManagementController::class, 'updateactivationdetail'])->name('activation.updateactivationdetail');

    Route::post('sync-listing', [ListingManagementController::class, 'syncListings'])->name('sync.listing');
    // Route::post('sync-listing-al-mosafer', [ListingManagementController::class, 'syncListingAlMosafer'])->name('sync.listing.almosafer');

    Route::get('listing-management/mapping/iframe/{channel_id}', [ListingManagementController::class, 'listingMapping'])->name('property.management.mapping.iframe');
    Route::resource('listing-url-management', ListingUrlManagementController::class);
    Route::resource('task-management', TaskManagementController::class);
    Route::resource('vendor-management', VendorManagementController::class);
    Route::resource('task-invoice-management', TaskInvoiceManagementController::class);
    Route::get('task-invoice-print/{task_invoice_management}', [TaskInvoiceManagementController::class, 'printInvoice'])->name('task-invoice-print');
    Route::resource('service-category-management', ServiceCategoryManagementController::class);
    Route::resource('service-management', ServiceManagementController::class);
    Route::resource('role-management', RoleManagementController::class);
    Route::resource('permission-module-management', ModuleManagementController::class);
    Route::resource('perm-role-module-management', ModulePermissionManagementController::class);
    Route::resource('host-type-management', HostTypeManagementController::class);
    Route::get('calender-management/index', [CalenderManagementController::class, 'index'])->name('calender.index');
    Route::get('block-date/request', [CalenderManagementController::class, 'blockDateRequest'])->name('block.date.request');
    Route::get('block-date/request/accept/{id}', [CalenderManagementController::class, 'blockDateRequestAccept'])->name('block.date.request.accept');
    Route::get('block-date/request/decline/{id}', [CalenderManagementController::class, 'blockDateRequestDecline'])->name('block.date.request.decline');
    Route::get('calender-management/syncGathern/{id}', [CalenderManagementController::class, 'syncGathern'])->name('calender.syncGathern');
    Route::get('/ical/{token}.ics', [IcalManagementController::class, 'generateIcal'])
        ->name('ical.generate')
        ->middleware('throttle:60,1');
    Route::get('sync/calender/{listing_id}', [ListingManagementController::class, 'syncCalenderData'])->name('calender.index.sync');
    Route::get('churned/listing/{listing_id}', [ListingManagementController::class, 'churnedListing'])->name('calender.churnedListing');

    // Invoice Controller
    Route::get('/invoices/list', [InvoiceController::class, 'getBookingIds'])->name('invoices.list');
    Route::get('/invoice/download/{id}', [InvoiceController::class, 'downloadInvoice'])->name('invoice.download');


    // All Property Calendar
    Route::get('properties-multi-calendar', [CalenderManagementController::class, 'multi_calendar'])->name('calender.multi');

    Route::post('fetchListingsByChannelId', [ListingManagementController::class, 'fetchListingsByChannelId'])->name('fetchListingsByChannelIdAdmin');
    Route::post('d', [CalenderManagementController::class, 'updateRateAndRestriction'])->name('updateListingCalenderAdmin');

    Route::post('slideBarUpdateAvailability', [CalenderManagementController::class, 'slideBarUpdateAvailability'])->name('slideBarUpdateAvailability');
    Route::post('slideBarUpdatePrice', [CalenderManagementController::class, 'slideBarUpdatePrice'])->name('slideBarUpdatePrice');
    Route::post('slideBarUpdateCustomPrice', [CalenderManagementController::class, 'slideBarUpdateCustomPrice'])->name('slideBarUpdateCustomPrice');

    Route::get('get-gbv-details', [CalenderManagementController::class, 'getGbvDetails'])->name('get-gbv-details');

    Route::post('map/listing/bookingCom', [ChannelManagementController::class, 'mapBookingListing'])->name('listing.mapBookingListing');
    Route::post('pull/future/reservation', [ChannelManagementController::class, 'pullFutureReservation'])->name('pullFutureReservation');

    Route::get('edit/listing/{id}', [ListingManagementController::class, 'edit'])->name('listing.edit');
    Route::get('edit/listing/pricing/{id}', [ListingManagementController::class, 'editListingPricing'])->name('listing.pricing.edit');
    Route::get('listing/occupancies', [DashboardController::class, 'listingOccupancie'])->name('listing.listingOccupancie');
    Route::get('listing/checkins', [DashboardController::class, 'listingCheckins'])->name('listing.listingCheckins');
    Route::get('listing/checkouts', [DashboardController::class, 'listingCheckouts'])->name('listing.listingCheckouts');
    Route::get('occupancy/data', [DashboardController::class, 'occupancyData'])->name('listing.occupancyData');
    Route::get('otaoccupancy/data', [DashboardController::class, 'otaOccupancyData'])->name('listing.otaOccupancyData');
    Route::get('occupancy/data/report', [DashboardController::class, 'occupancyReport'])->name('listing.occupancyReport');
    Route::post('add-discounts', [ListingManagementController::class, 'addDiscounts'])->name('listing.discounts.update');
    Route::get('listing/vacant', [DashboardController::class, 'listingVacant'])->name('listing.listingVacant');
    Route::get('listing/cleaning', [DashboardController::class, 'todaycleaning'])->name('listing.todaycleaning');

    Route::put('update/listing/settings/{id}', [ListingManagementController::class, 'updateListingSettings'])->name('listing.settings.update');
    Route::post('store/listing/ical/{id}', [ListingManagementController::class, 'storeListingIcal'])->name('listing.storeListingIcal');

    Route::post('update/listing/{id}', [ListingManagementController::class, 'update'])->name('listing.update');
    Route::post('updatemanager/listing/{id}', [ListingManagementController::class, 'updatemanager'])->name('listing.updatemanager');
    Route::post('expmanagerdelete/listing/{id}', [ListingManagementController::class, 'destroyexpmanager'])->name('listing.expmanagerdelete');

    Route::post('update/listing/details/{id}', [ListingManagementController::class, 'updateListingDetails'])->name('update.listing.details.update');
    Route::post('commissionUpdate/listing/{id}', [ListingManagementController::class, 'commissionUpdate'])->name('listing.commissionUpdate');
    Route::post('delete/listing/{id}', [ListingManagementController::class, 'destroy'])->name('listing.destroy');
    Route::get('/home', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('deep-cleaning-management', DeepCleaningManagement::class);
    Route::post('deepcleaning/management/store/comment', [DeepCleaningManagement::class, 'storeComment'])->name('deepcleaning-management.storeComment');

    Route::resource('audit-management', AuditManagement::class);
    Route::get('audit-dashboard', [AuditManagement::class, 'showDashboard'])->name('audit.showdashboard');

    Route::post('audit/management/store/comment', [AuditManagement::class, 'storeComment'])->name('audit-management.storeComment');
    Route::get('/get-listing-details/{id}', [AuditManagement::class, 'getListingDetails']);



    Route::resource('cleaning-management', CleaningManagement::class);
    Route::get('cleaning/management/update', [CleaningManagement::class, 'editData'])->name('cleaning-management.editData');
    Route::post('cleaning/management/update/data', [CleaningManagement::class, 'updateData'])->name('cleaning-management.updateData');
    Route::post('cleaning/management/store/comment', [CleaningManagement::class, 'storeComment'])->name('cleaning-management.storeComment');


    Route::get('notifications', [NotificationController::class, 'getNotifications']);
    Route::get('admin-notifications', [NotificationController::class, 'getAdminNotifications']);
    Route::post('admin-notifications', [NotificationController::class, 'storeAdminNotification'])->name('storeAdminNotification');
    Route::get('notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
    Route::get('/notifications/count', [NotificationController::class, 'countUnread']);

    //new notification end points
    Route::get('/notifications/counts', [NotificationController::class, 'getNotificationsByRoleCount']);
    Route::get('/notifications/response', [NotificationController::class, 'getNotificationsByRole']);
    Route::post('/notifications/{id}/seen', [NotificationController::class, 'markAsSeen']);

    Route::post('/notifications/{id}/update-status', [NotificationController::class, 'updateStatus']);




    Route::get('/notifications/show', [NotificationController::class, 'getNotificationsByRoleData'])->name('notifications.by.role.data');
    Route::get('/notifications/view', [NotificationController::class, 'getNotificationsByRoleView'])->name('notifications.by.role.data.view');
    //end new notification end points

    Route::resource('review-management', ReviewManagementController::class);
    Route::get('review-management/guest-review/{review_id}', [ReviewManagementController::class, 'guestReview'])->name('guestReview.create');
    Route::post('review-management/guest-review/store', [ReviewManagementController::class, 'guestReviewStore'])->name('guestReview.store');
    // Reports Route

    // Reports Route
    Route::get('/report/finance/soa/host/listings/{user_id}', [FinanceMangementController::class, 'hostListings'])->name('reports.finance.soa.host.listings');
    Route::get('/report/finance/soa', [FinanceMangementController::class, 'index'])->name('reports.finance.soa');
    Route::get('/report/finance/soa/create', [FinanceMangementController::class, 'create'])->name('reports.finance.soa.create');
    Route::get('/report/finance/soa/fetch/host', [FinanceMangementController::class, 'fetchSoasByHostId'])->name('reports.finance.soa.fetch.soa.by.host');
    Route::get('/report/finance/soa/print', [FinanceMangementController::class, 'printSoa'])->name('reports.finance.soa.print');
    Route::get('/report/finance/soa/print/excel', [FinanceMangementController::class, 'printSoaExcel'])->name('reports.finance.soa.print.excel');
    Route::post('/report/finance/soa/upload/pop', [FinanceMangementController::class, 'uploadPop'])->name('reports.finance.soa.upload.pop');
    Route::post('/report/finance/soa/publish', [FinanceMangementController::class, 'publishSoa'])->name('reports.finance.soa.publish');

    Route::post('/report/finance/soa/details', [FinanceMangementController::class, 'saveSoaDetails'])->name('reports.finance.soa.saveSoaDetails');
    Route::post('/report/finance/soa/details/reset', [FinanceMangementController::class, 'resetSoaDetails'])->name('reports.finance.soa.resetSoaDetails');
    Route::get('/report/finance/soa/download', [FinanceMangementController::class, 'downloadSoa'])->name('reports.finance.soa.downloadSoa');



    // Revenue Reports
    Route::get('/revenue/report', [RevenueManagementController::class, 'index'])->name('reports.revenue');


    // Route to show roles and form
    Route::get('/assign-permissions', [RolePermissionController::class, 'index'])->name('assign.permissions');

    // Route to fetch permissions based on selected role
    Route::get('/fetch-permissions/{roleId?}', [RolePermissionController::class, 'fetchPermissions'])->name('fetch.permissions');

    // Route to save permissions
    Route::post('/save-permissions', [RolePermissionController::class, 'savePermissions'])->name('save.permissions');


    Route::get('admin/{any}', function () {
        return view('Admin.main');
    })->where('any', '.*');

    Route::get('/phpinfo', function () {
        phpinfo();
    });
    // Route::get('/home', function () {
    //     return view('Admin.dashboard.dashboard');
    // })->name('dashboard');
    Route::get('', function () {

        return redirect()->route('dashboard');
    })->name('home');

    //    Route::get('/role-management', function () {
////    dd('sdfds');
//        return view('Admin.role-management.index');
//    })->name('roles');

    //Route::resource('role-management', RoleManagementController::class);
});


Route::get('/test-event', function () {
    event(new App\Events\TestEvent('Hello Reverb!'));
    return "Event dispatched!";
});
