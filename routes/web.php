<?php

use App\Http\Controllers\Admin\MasterCodeController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AnnouncementCategoryController;
use App\Http\Controllers\Advance\AdvanceController;
use App\Http\Controllers\Application\ApplicationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Bank\BankController;
use App\Http\Controllers\Bank\BankDataController;
use App\Http\Controllers\Bank\BankProductController;
use App\Http\Controllers\Bank\ProductController;
use App\Http\Controllers\Bank_MIS\BankMisController;
use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\BankTarget\BankTargetController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\DSA\DSAController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Remark\RemarkController;
use App\Http\Controllers\Setting\BankPayoutController;
use App\Http\Controllers\Setting\ServiceController;
use App\Http\Controllers\Settlement\SettlementController;
use App\Http\Controllers\SheetMatching\SheetMatchingController;
use App\Http\Controllers\Staff\PermissionController;
use App\Http\Controllers\Staff\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\User\MakerCheckerController;
use App\Http\Controllers\User\ChannelPartnerController;
use App\Http\Controllers\User\SalesPersonController;
use App\Http\Controllers\AnnouncementPopupController;
use App\Http\Controllers\MISTracker\MISTrackerController;
use App\Http\Controllers\Bank_MIS\InvoiceController as Bank_MISInvoiceController;
use App\Http\Controllers\InvoicePayment\InvoicePaymentController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckLogin;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

Route::get('/encrypt', function () {
    $encrypted = Crypt::encryptString('MPIndRic00016');
    return $encrypted;
});

Route::get('/channel/associated-users/{id}', [ChannelPartnerController::class, 'associatedUsers'])->name('channel.associated-users');

Route::get('/', [AuthController::class, 'index']);
Route::get('/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/signup', [AuthController::class, 'register']);
Route::get('/verify-email', [AuthController::class, 'verifyEmail'])->name('verify-email');
Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify-otp');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::get('/getDistrict/{state_code}', [Controller::class, 'getDistrict']);
Route::post('/login', [AuthController::class, 'Login']);
Route::get('/logout', [AuthController::class, 'Logout']);

Route::get('/test-email', function () {
    $to = 'anamika.hira@parkersconsultings.com'; // Change to desired email address
    $subject = 'Test Email from Application';
    $data = [
        'body' => 'This is a test email sent from your parkersconsultings application.'
    ];
    Mail::raw($data['body'], function ($message) use ($to, $subject) {
        $message->to($to)
            ->subject($subject);
    });
    return 'Test email sent to ' . $to;
});



Route::middleware([CheckLogin::class])->group(function () {

    // Bank MIS Route
    Route::get('/bank_mis', [BankMisController::class, 'index'])->name('bankmis.index');
    Route::get('/bank_mis/view/filter', [BankMisController::class, 'filter']);
    Route::get('/bank_mis/create', [BankMisController::class, 'add']);
    Route::get('/bank_mis/view/{id}', [BankMisController::class, 'show'])->name('bankmis.show');
    Route::delete('/bank_mis/delete/{bank}', [BankMisController::class, 'destroy'])->name('bankmis.destroy');
    Route::post('/bank_mis/delete/bulk', [BankMisController::class, 'bulkDelete'])->name('bank-mis.bulk-delete');

    // Invoice Route
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice.index');
    Route::get('/invoice/view/filter', [InvoiceController::class, 'filter']);
    Route::get('/invoice/create', [InvoiceController::class, 'add']);
    Route::get('/invoice/view/{id}', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::delete('/invoice/delete/{bank}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
    Route::post('/invoice/generateInvoice', [InvoiceController::class, 'generate'])->name('invoice.generate');
    Route::post('/invoice/store', [InvoiceController::class, 'store'])->name('invoice.store');

    //mis tracker
    Route::get('/mis_tracker', [MISTrackerController::class, 'index'])->name('mis_tracker.index');

    Route::get('/invoice_payment', [InvoicePaymentController::class, 'index'])->name('invoice_payment.index');
    Route::post('/invoice_payment/filter', [InvoicePaymentController::class, 'filter'])->name('invoice_payment.filter');
    Route::get('/invoice_payment/view/{id}', [InvoicePaymentController::class, 'show'])->name('invoice_payment.show');
    Route::get('/invoice_payment/edit/{id}', [InvoicePaymentController::class, 'edit'])->name('invoice_payment.edit');
    Route::put('/invoice_payment/{id}', [InvoicePaymentController::class, 'update'])->name('invoice_payment.update');
    Route::delete('/invoice_payment/{id}', [InvoicePaymentController::class, 'destroy'])->name('invoice_payment.destroy');

    Route::post('/invoice_payment/getInvoiceCases', [InvoicePaymentController::class, 'getInvoiceCases'])->name('invoice_payment.getInvoiceCases');
});


Route::middleware([CheckLogin::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/updateBank', [ProfileController::class, 'updateBank']);
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/addBank', [ProfileController::class, 'addBank']);
    Route::post('/getProduct', [BankProductController::class, 'getProduct']);
    Route::post('/getAllProduct', [BankProductController::class, 'getAllProduct']);
    Route::post('/getServiceProduct', [ServiceController::class, 'getServiceProduct']);

    //Manage Permission Route
    Route::get('/sheet-matching', [SheetMatchingController::class, 'index'])->name('sheet-matching.index');
    Route::get('/add-sheet-data', [SheetMatchingController::class, 'addSheetData'])->name('add-sheet-data');
    Route::post('/store-data', [SheetMatchingController::class, 'storeSheetData'])->name('store-data');
    Route::get('/sheet-matching/update/{id}', [SheetMatchingController::class, 'edit'])->name('sheet-matching.edit');
    Route::put('/sheet-matching/{id}', [SheetMatchingController::class, 'update'])->name('sheet-matching.update');
    Route::delete('/sheet-matching/delete/{sheet}', [SheetMatchingController::class, 'destroy'])->name('sheet-matching.destroy');

    Route::post('/getFileData', [SheetMatchingController::class, 'getFileData']);

    Route::post('/application/update/remark', [ApplicationController::class, 'updateRemark']);

    Route::get('/staff/view/getPermission/{role}', [PermissionController::class, 'getPermission']);
    Route::post('/staff/create/updatePermission',  [PermissionController::class, 'updatePermission']);

    //Bank Route
    Route::get('/remark-status', [RemarkController::class, 'index'])->name('remark.index');
    Route::post('/remark-status/create', [RemarkController::class, 'store']);
    Route::get('/remark-status/update/{id}', [RemarkController::class, 'edit']);
    Route::put('/remark-status/update/{remarkStatus}', [RemarkController::class, 'update']);
    Route::delete('/remark-status/delete/{remarkStatus}', [RemarkController::class, 'destory']);
    Route::get('/master-code', [MasterCodeController::class, 'index'])->name('master-code.index');
    Route::post('/master-code/update', [MasterCodeController::class, 'update'])->name('master-code.update');
    Route::get('/master-code/download', [MasterCodeController::class, 'download'])->name('master-code.download');
    Route::get('/link-bank', [BankDataController::class, 'index'])->name('link-bank.index');
    Route::get('/link-bank/create', [BankDataController::class, 'create'])->name('link-bank.create');
    Route::post('/link-bank/create', [BankDataController::class, 'store']);
    Route::get('/link-bank/view/{id}', [BankDataController::class, 'show'])->name('link-bank.show');
    Route::get('/link-bank/update/{id}', [BankDataController::class, 'edit']);
    Route::put('/link-bank/update/{bankData}', [BankDataController::class, 'update']);
    Route::delete('/link-bank/delete/{bankData}', [BankDataController::class, 'destory']);
    Route::post('/link-bank/activate/{id}', [BankDataController::class, 'activate'])->name('link-bank.activate');
    Route::post('/link-bank/deactivate/{id}', [BankDataController::class, 'deactivate'])->name('link-bank.deactivate');

    //Advance Route
    Route::get('/advance', [AdvanceController::class, 'index'])->name('advance.index');
    Route::get('/advance/create', [AdvanceController::class, 'create'])->name('advance.create');
    Route::post('/advance/create', [AdvanceController::class, 'store'])->name('advance.store');
    Route::get('/advance/view/{id}', [AdvanceController::class, 'show'])->name('advance.show');
    Route::get('/advance/update/{id}', [AdvanceController::class, 'edit'])->name('advance.edit');
    Route::post('/advance/update/{id}', [AdvanceController::class, 'update'])->name('advance.update');
    Route::get('/advance/users/search', [AdvanceController::class, 'searchUsers'])->name('advance.users.search');
    Route::get('/advance/applications/by-user', [AdvanceController::class, 'applicationsByUser'])->name('advance.applications.by-user');
    Route::get('/advance/applications/list-by-user', [AdvanceController::class, 'applicationsListByUser'])->name('advance.applications.list-by-user');
    Route::post('/advance/cases/calculate-amount', [AdvanceController::class, 'calculateCaseAmount'])->name('advance.cases.calculate');
    Route::get('/advance/log/{logId}/application-ids', [AdvanceController::class, 'getLogApplicationIds'])->name('advance.log.application-ids');

    // Announcement popup API for logged-in users
    Route::get('/announcements/active', [AnnouncementPopupController::class, 'active'])->name('announcements.active');
    Route::post('/announcements/{id}/acknowledge', [AnnouncementPopupController::class, 'acknowledge'])->name('announcements.acknowledge');
});



Route::middleware([CheckPermission::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);
    //Application Route
    Route::get('/application', [ApplicationController::class, 'index'])->name('application.index');
    Route::post('/application/delete/bulk', [ApplicationController::class, 'bulkDelete'])->name('applications.bulk-delete');
    Route::get('/application/view/filter', [ApplicationController::class, 'filter']);
    Route::get('/application/view/export-application', [ApplicationController::class, 'exportApplication']);


    Route::get('/application/create', [ApplicationController::class, 'add']);
    Route::post('/application/create', [ApplicationController::class, 'store']);

    Route::get('/application/create/upload', [ApplicationController::class, 'uploadView']);
    Route::post('/application/create/upload', [ApplicationController::class, 'storeExcel']);

    Route::get('/application/update/{id}', [ApplicationController::class, 'edit']);
    Route::post('/application/update/{id}', [ApplicationController::class, 'update']);

    Route::get('/application/view/{id}', [ApplicationController::class, 'show']);
    Route::delete('/application/delete/{application}', [ApplicationController::class, 'destroy']);


    Route::get('/upload-mis/create', [ApplicationController::class, 'uploadMISView']);
    Route::post('/upload-mis/create', [ApplicationController::class, 'uploadMIS']);




    //Settlement Route
    Route::get('/settlement', [SettlementController::class, 'index'])->name('settlement.index');
    Route::post('/settlement/view/filter', [SettlementController::class, 'filter']);
    Route::get('/settlement/update/{id}', [SettlementController::class, 'edit']);
    Route::post('/settlement/update/{id}', [SettlementController::class, 'update']);
    Route::get('/settlement/view/export-settlement', [SettlementController::class, 'exportSettlement']);
    Route::get('/settlement/view/{id}', [SettlementController::class, 'show']);

    Route::get('/settlement/create/upload', [SettlementController::class, 'uploadView']);
    Route::post('/settlement/create/upload', [SettlementController::class, 'storeExcel']);

    //Bank Route
    Route::get('/dsa-code', [DSAController::class, 'index'])->name('dsa.index');
    Route::post('/dsa-code/create', [DSAController::class, 'store']);
    Route::get('/dsa-code/update/{id}', [DSAController::class, 'edit']);
    Route::put('/dsa-code/update/{dsaCode}', [DSAController::class, 'update']);
    Route::delete('/dsa-code/delete/{dsaCode}', [DSAController::class, 'destory']);

    //Bank Route
    Route::get('/bank-target', [BankTargetController::class, 'index'])->name('bank_target.index');
    Route::post('/bank-target/create', [BankTargetController::class, 'store']);
    Route::get('/bank-target/update/{bankTarget}', [BankTargetController::class, 'getBankTarget']);
    Route::put('/bank-target/update/{bankTarget}', [BankTargetController::class, 'update']);
    Route::delete('/bank-target/delete/{bankTarget}', [BankTargetController::class, 'destory']);


    //Bank Route
    Route::get('/bank', [BankController::class, 'index'])->name('bank.index');
    Route::post('/bank/create', [BankController::class, 'store']);
    Route::get('/bank/update/{bank}', [BankController::class, 'getBank']);
    Route::put('/bank/update/{bank}', [BankController::class, 'update']);
    Route::delete('/bank/delete/{bank}', [BankController::class, 'destory']);

    //Product Route
    Route::get('/product', [ProductController::class, 'index'])->name('product.index');
    Route::post('/product/create', [ProductController::class, 'store']);
    Route::get('/product/update/{product}', [ProductController::class, 'getProduct']);
    Route::put('/product/update/{product}', [ProductController::class, 'update']);
    Route::delete('/product/delete/{product}', [ProductController::class, 'destroy']);

    //Bank Product Route
    Route::get('/bank/view/product', [BankProductController::class, 'index'])->name('bank-product.index');
    Route::post('/bank/create/product/create', [BankProductController::class, 'store']);
    Route::get('/bank/update/product/{bankProduct}', [BankProductController::class, 'getBankProduct']);
    Route::put('/bank/update/product/{bankProduct}', [BankProductController::class, 'update']);
    Route::delete('/bank/delete/product/{bankProduct}', [BankProductController::class, 'destory']);


    //Channel Partner 
    Route::get('/channel', [ChannelPartnerController::class, 'index'])->name('channel.index');
    Route::get('/channel/create', [ChannelPartnerController::class, 'add']);
    Route::post('/channel/create', [ChannelPartnerController::class, 'store']);
    Route::get('/channel/view/export-channel', [ChannelPartnerController::class, 'exportChannel']);
    Route::get('/channel/view/{id}', [ChannelPartnerController::class, 'show']);
    Route::get('/channel/update/{id}', [ChannelPartnerController::class, 'edit']);
    Route::post('/channel/update/{id}', [ChannelPartnerController::class, 'update']);
    Route::delete('/channel/delete/{user}', [ChannelPartnerController::class, 'destroy']);
    // Route::get('/channel/associated-users/{id}', [ChannelPartnerController::class, 'associatedUsers'])->name('channel.associated-users');

    //Sales Partner 
    Route::get('/sales-person', [SalesPersonController::class, 'index'])->name('sales-person.index');
    Route::get('/sales-person/create', [SalesPersonController::class, 'add']);
    Route::post('/sales-person/create', [SalesPersonController::class, 'store']);
    Route::get('/sales-person/view/export-sales', [SalesPersonController::class, 'exportSales']);
    Route::get('/sales-person/view/{id}', [SalesPersonController::class, 'show']);
    Route::get('/sales-person/update/{id}', [SalesPersonController::class, 'edit']);
    Route::post('/sales-person/update/{id}', [SalesPersonController::class, 'update']);
    Route::delete('/sales-person/delete/{user}', [SalesPersonController::class, 'destroy']);


    //Manage Staff Route
    Route::get('/staff', [StaffController::class, 'index'])->name('manage-staff.index');
    Route::get('/staff/create', [StaffController::class, 'create']);
    Route::get('/staff/view/export-staff', [StaffController::class, 'exportStaff']);
    Route::get('/staff/create/view/{id}', [StaffController::class, 'view']);
    Route::post('/staff/create', [StaffController::class, 'store']);
    Route::get('/staff/update/{id}', [StaffController::class, 'edit']);
    Route::post('/staff/update/{id}', [StaffController::class, 'update']);
    Route::delete('/staff/delete/{user}', [StaffController::class, 'destroy']);

    // Maker / Checker Route
    Route::get('/maker-checker', [MakerCheckerController::class, 'index'])->name('maker-checker.index');
    Route::get('/maker-checker/create', [MakerCheckerController::class, 'create']);
    Route::post('/maker-checker/create', [MakerCheckerController::class, 'store']);
    Route::get('/maker-checker/view/{id}', [MakerCheckerController::class, 'show']);
    Route::get('/maker-checker/update/{id}', [MakerCheckerController::class, 'edit']);
    Route::post('/maker-checker/update/{id}', [MakerCheckerController::class, 'update']);
    Route::delete('/maker-checker/delete/{user}', [MakerCheckerController::class, 'destroy']);

    //Manage Role Route
    Route::get('/staff/view/role', [RoleController::class, 'index'])->name('manage-role.index');
    Route::post('/staff/create/role', [RoleController::class, 'store']);
    Route::get('/staff/view/role/{role}', [RoleController::class, 'show']);
    Route::get('/staff/view/export-role', [RoleController::class, 'exportRoles']);
    Route::get('/staff/update/role/{role}', [RoleController::class, 'getRole']);
    Route::put('/staff/update/role/{role}', [RoleController::class, 'update']);
    Route::delete('/staff/delete/role/{role}', [RoleController::class, 'destroy']);

    //Manage Permission Route
    Route::get('/staff/view/permissions', [PermissionController::class, 'index'])->name('permission.index');
    Route::get('/staff/view/getPermission/{role}', [PermissionController::class, 'getPermission']);
    Route::post('/staff/create/updatePermission',  [PermissionController::class, 'updatePermission']);

    //
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [ServiceController::class, 'create']);
    Route::post('/services/create', [ServiceController::class, 'store']);
    Route::get('/services/update/{service}', [ServiceController::class, 'edit']);
    Route::post('/services/update/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/delete/{service}', [ServiceController::class, 'destory']);

    Route::get('/bank-payout', [BankPayoutController::class, 'index'])->name('bank-payout.index');
    Route::get('/bank-payout/create', [BankPayoutController::class, 'create']);
    Route::post('/bank-payout/create', [BankPayoutController::class, 'store']);
    Route::get('/bank-payout/update/{bankPayout}', [BankPayoutController::class, 'edit']);
    Route::post('/bank-payout/update/{bankPayout}', [BankPayoutController::class, 'update']);
    Route::delete('/bank-payout/delete/{bankPayout}', [BankPayoutController::class, 'destory']);

    // Announcement Route
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create']);
    Route::post('/announcements/create', [AnnouncementController::class, 'store']);
    Route::get('/announcements/view/{id}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('/announcements/logs/{id}', [AnnouncementController::class, 'logs'])->name('announcements.logs');
    Route::get('/announcements/update/{announcement}', [AnnouncementController::class, 'edit']);
    Route::post('/announcements/update/{announcement}', [AnnouncementController::class, 'update']);
    Route::delete('/announcements/delete/{announcement}', [AnnouncementController::class, 'destroy']);

    // Announcement Category Route
    Route::get('/announcement-categories', [AnnouncementCategoryController::class, 'index'])->name('announcement-categories.index');
    Route::get('/announcement-categories/create', [AnnouncementCategoryController::class, 'create']);
    Route::post('/announcement-categories/create', [AnnouncementCategoryController::class, 'store']);
    Route::get('/announcement-categories/update/{announcementCategory}', [AnnouncementCategoryController::class, 'edit']);
    Route::post('/announcement-categories/update/{announcementCategory}', [AnnouncementCategoryController::class, 'update']);
    Route::delete('/announcement-categories/delete/{announcementCategory}', [AnnouncementCategoryController::class, 'destroy']);
    Route::post('/announcement-categories/toggle-status/{announcementCategory}', [AnnouncementCategoryController::class, 'toggleStatus'])->name('announcement-categories.toggle-status');
});
