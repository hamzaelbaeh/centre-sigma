<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffPayrollController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherPayrollController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\YearController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{lang}', [LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('students/export', [ExportController::class, 'students'])->name('students.export');
    Route::get('students/import/template', [StudentController::class, 'importTemplate'])->name('students.import.template');
    Route::get('students/import', [StudentController::class, 'importForm'])->name('students.import');
    Route::post('students/import', [StudentController::class, 'importStore'])->name('students.import.store');
    Route::get('teachers/export', [ExportController::class, 'teachers'])->name('teachers.export');
    Route::get('expenses/export', [ExportController::class, 'expenses'])->name('expenses.export');
    Route::get('payments/export', [ExportController::class, 'payments'])->name('payments.export');
    Route::get('reports/export', [ExportController::class, 'reports'])->name('reports.export');

    Route::resource('students', StudentController::class);
    Route::resource('parents', ParentController::class)->except(['show']);
    Route::resource('classes', ClassController::class);
    Route::post('classes/{class}/students', [ClassController::class, 'syncStudents'])->name('classes.students.sync');
    Route::resource('teachers', TeacherController::class)->except(['show']);
    Route::resource('subjects', SubjectController::class)->except(['show']);

    Route::get('timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::post('timetable', [TimetableController::class, 'store'])->name('timetable.store');
    Route::delete('timetable/{timetable}', [TimetableController::class, 'destroy'])->name('timetable.destroy');

    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/absences', [AttendanceController::class, 'absences'])->name('attendance.absences');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/{payment}/encaisser', [PaymentController::class, 'encaisser'])->name('payments.encaisser');
    Route::post('payments/generate', [PaymentController::class, 'generateMensualites'])->name('payments.generate');
    Route::get('payments/impayes', [PaymentController::class, 'impayes'])->name('payments.impayes');

    Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
    Route::post('fees', [FeeController::class, 'store'])->name('fees.store');

    Route::get('payroll-teachers', [TeacherPayrollController::class, 'index'])->name('payroll_teachers.index');
    Route::post('payroll-teachers/generate', [TeacherPayrollController::class, 'generate'])->name('payroll_teachers.generate');
    Route::post('payroll-teachers/{payroll}/pay', [TeacherPayrollController::class, 'pay'])->name('payroll_teachers.pay');

    Route::resource('staff', StaffController::class)->except(['show']);
    Route::get('staff-payroll', [StaffPayrollController::class, 'index'])->name('staff_payroll.index');
    Route::post('staff-payroll/generate', [StaffPayrollController::class, 'generate'])->name('staff_payroll.generate');
    Route::put('staff-payroll/{payroll}', [StaffPayrollController::class, 'update'])->name('staff_payroll.update');
    Route::post('staff-payroll/{payroll}/pay', [StaffPayrollController::class, 'pay'])->name('staff_payroll.pay');

    Route::resource('expenses', ExpenseController::class)->except(['show']);

    Route::get('departures', [DepartureController::class, 'index'])->name('departures.index');
    Route::get('departures/create', [DepartureController::class, 'create'])->name('departures.create');
    Route::post('departures', [DepartureController::class, 'store'])->name('departures.store');
    Route::delete('departures/{departure}', [DepartureController::class, 'destroy'])->name('departures.destroy');

    Route::resource('trainings', TrainingController::class);
    Route::post('trainings/{training}/enroll', [TrainingController::class, 'enroll'])->name('trainings.enroll');
    Route::put('training-participants/{participant}', [TrainingController::class, 'updateParticipant'])->name('trainings.participant');

    Route::get('years', [YearController::class, 'index'])->name('years.index');
    Route::post('years', [YearController::class, 'store'])->name('years.store');
    Route::put('years/{year}', [YearController::class, 'update'])->name('years.update');
    Route::post('years/{year}/activate', [YearController::class, 'activate'])->name('years.activate');
    Route::delete('years/{year}', [YearController::class, 'destroy'])->name('years.destroy');

    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('documents/print/{type}', [DocumentController::class, 'print'])->name('documents.print');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::resource('users', UserController::class)->except(['show']);

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('settings/logo', [SettingController::class, 'deleteLogo'])->name('settings.logo');
});
