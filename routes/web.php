<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SummerNoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentHoldController;
use App\Http\Controllers\PayphoneController;
use App\Http\Controllers\Admin\TransferReceiptController;
use App\Http\Controllers\Admin\AdminAvailabilityController;

use Illuminate\Http\Request;


Auth::routes([
    'register' => false
]);

Route::post('/appointment-holds', [AppointmentHoldController::class, 'create'])->name('appointment.holds.create');

Route::post('/appointment-holds/release', [AppointmentHoldController::class, 'release'])->name('appointment.holds.release');

Route::post('/holds', [AppointmentHoldController::class, 'create'])
    ->name('holds.create');
    
Route::delete('/holds/{id}', [AppointmentHoldController::class, 'destroy'])->name('appointment.holds.destroy');

Route::get('/',[FrontendController::class,'index'])->name('home');

Route::middleware(['auth'])->group(function () {

    Route::get('/admin/appointments/{appointment}/transfer-receipt', [TransferReceiptController::class, 'show'])
    ->middleware('permission:appointments.view')
    ->name('admin.appointments.transfer_receipt');

    Route::get('/admin/appointments/{appointment}/transfer-receipt/view', [TransferReceiptController::class, 'view'])
    ->middleware('permission:appointments.view')
    ->name('admin.appointments.transfer_receipt.view');

    Route::get('/appointments/reschedule/slots/{employee}/{date?}', [AdminAvailabilityController::class, 'getEmployeeAvailability'])
    ->name('admin.appointments.reschedule.slots');

    // Admin: disponibilidad (reagendar)
    Route::get('/admin/employees/{employee}/available-dates', [AdminAvailabilityController::class, 'getEmployeeAvailableDates'])
        ->name('admin.employees.available_dates');

    Route::get('/admin/employees/{employee}/availability/{date?}', [AdminAvailabilityController::class, 'getEmployeeAvailability'])
        ->name('admin.employees.availability');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    //user
    Route::resource('user',UserController::class)->middleware('permission:users.view| users.create | users.edit | users.delete');
    //update user password

    //profile page
    Route::get('profile',[ProfileController::class,'index'])->name('profile');
    //user profile update
    Route::patch('profile-update/{user}',[ProfileController::class,'profileUpdate'])->name('user.profile.update');
    Route::patch('user/pasword-update/{user}',[UserController::class,'password_update'])->name('user.password.update');
    Route::put('user/profile-pic/{user}',[UserController::class,'updateProfileImage'])->name('user.profile.image.update');

    //delete profile image
    Route::patch('delete-profile-image/{user}',[UserController::class,'deleteProfileImage'])->name('delete.profile.image');
    //trash view for users
    Route::get('user-trash', [UserController::class, 'trashView'])->name('user.trash');
    Route::get('user-restore/{id}', [UserController::class, 'restore'])->name('user.restore');
    //deleted permanently
    Route::delete('user-delete/{id}', [UserController::class, 'force_delete'])->name('user.force.delete');

    Route::get('settings', [SettingController::class, 'index'])->name('setting')->middleware('permission:setting update');
    Route::post('settings/{setting}', [SettingController::class, 'update'])->name('setting.update');


    Route::resource('category', CategoryController::class)->middleware('permission:categories.view| categories.create | categories.edit | categories.delete');


    // Services
    Route::resource('service', ServiceController::class)->middleware('permission:services.view| services.create | services.edit | services.delete');
    Route::get('service-trash', [ServiceController::class, 'trashView'])->name('service.trash');
    Route::get('service-restore/{id}', [ServiceController::class, 'restore'])->name('service.restore');
    //deleted permanently
    Route::delete('service-delete/{id}', [ServiceController::class, 'force_delete'])->name('service.force.delete');


    //summernote image
    Route::post('summernote',[SummerNoteController::class,'summerUpload'])->name('summer.upload.image');
    Route::post('summernote/delete',[SummerNoteController::class,'summerDelete'])->name('summer.delete.image');


    //employee
    // Route::resource('user',UserController::class);
    Route::get('employee-booking',[UserController::class,'EmployeeBookings'])->name('employee.bookings');
    Route::get('my-booking/{id}',[UserController::class,'show'])->name('employee.booking.detail');

    // employee profile self data update
    Route::patch('employe-profile-update/{employee}',[ProfileController::class,'employeeProfileUpdate'])->name('employee.profile.update');

    //employee bio
    Route::put('employee-bio/{employee}',[EmployeeController::class,'updateBio'])->name('employee.bio.update');





    Route::get('test',function(Request $request){
        return view('test',  [
            'request' => $request
        ]);
    });



    Route::post('test', function (Request $request) {
        dd($request->all())->toArray();
    })->name('test');

    Route::post('/appointments/update-status', [AppointmentController::class, 'updateStatus'])->name('appointments.update.status');

    Route::post('/appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])
    ->name('appointments.confirm');

    Route::get('/appointments/{appointment}/audits', [AppointmentController::class, 'audits'])
    ->name('appointments.audits');

    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
    ->middleware('permission:appointments.edit|appointments.delete|appointments.cancel')
    ->name('appointments.cancel');

});



//frontend routes
//fetch services from categories
Route::get('/categories/{category}/services', [FrontendController::class, 'getServices'])->name('get.services');

//fetch employee from category
Route::get('/services/{service}/employees', [FrontendController::class, 'getEmployees'])->name('get.employees');

//get availibility
Route::get('/employees/{employee}/availability/{date?}', [FrontendController::class, 'getEmployeeAvailability'])
    ->name('employee.availability');

// get available dates for calendar (1 request per month)
Route::get('/employees/{employee}/available-dates', [FrontendController::class, 'getEmployeeAvailableDates'])
->name('employee.available_dates');

//create appointment
Route::post('/bookings', [AppointmentController::class, 'store'])->name('bookings.store');
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments')->middleware('permission:appointments.view| appointments.create | services.appointments | appointments.delete');



//update status from dashbaord
Route::post('/update-status', [DashboardController::class, 'updateStatus'])->name('dashboard.update.status');

Route::post('/payments/payphone/init', [PayphoneController::class, 'init'])->name('payphone.init');
Route::get('/payments/payphone/response', [PayphoneController::class, 'response'])->name('payphone.response');
Route::get('/payments/payphone/confirm', [PayphoneController::class, 'confirm'])->name('payphone.confirm');
