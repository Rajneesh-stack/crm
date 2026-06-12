<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BulkUploadController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\CounselorController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WhatsAppWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Auth
Route::get('/login',  [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// Avatar streaming — URL has NO file extension so web server's static-file handler
// (CloudPanel/Nginx/OpenLiteSpeed) does not intercept; request reaches Laravel.
Route::get('/u/{user}/avatar', [ProfileController::class, 'avatar'])
    ->where('user', '[0-9]+')
    ->name('avatars.show');

// WhatsApp Cloud API webhook (Meta calls these — no auth, CSRF-exempt via bootstrap/app.php)
Route::get ('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive'])->name('webhooks.whatsapp.receive');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leads
    Route::get   ('/leads',                  [LeadController::class, 'index'])->name('leads.index');
    Route::post  ('/leads',                  [LeadController::class, 'store'])->name('leads.store');
    Route::get   ('/leads/bulk-upload',      [BulkUploadController::class, 'show'])->name('leads.bulk.show');
    Route::post  ('/leads/bulk-upload',      [BulkUploadController::class, 'upload'])->name('leads.bulk.upload');
    Route::get   ('/leads/bulk-upload/sample', [BulkUploadController::class, 'sample'])->name('leads.bulk.sample');
    Route::get   ('/leads/{lead}',           [LeadController::class, 'show'])->name('leads.show');
    Route::get   ('/leads/{lead}/edit',      [LeadController::class, 'edit'])->name('leads.edit');
    Route::put   ('/leads/{lead}',           [LeadController::class, 'update'])->name('leads.update');
    Route::post  ('/leads/{lead}/comments',  [LeadController::class, 'addComment'])->name('leads.comments.store');
    Route::post  ('/leads/{lead}/reassign',  [LeadController::class, 'reassign'])->name('leads.reassign');

    // Communication
    Route::post  ('/leads/{lead}/whatsapp', [CommunicationController::class, 'sendWhatsapp'])->name('leads.whatsapp.send');
    Route::post  ('/leads/{lead}/email',    [CommunicationController::class, 'sendEmail'])->name('leads.email.send');

    // Activity
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');

    // Reports (admin sees all; counselor scoped to own leads — both have access)
    Route::get('/reports',        [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Profile (everyone can manage their own)
    Route::get ('/profile',          [ProfileController::class, 'show'])->name('profile.show');
    Route::put ('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::put ('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get ('/profile/avatar-diag', [ProfileController::class, 'avatarDiag'])->name('profile.avatar.diag');

    // Admin-only
    Route::middleware(['role:admin'])->group(function () {
        Route::get   ('/counselors',                       [CounselorController::class, 'index'])->name('counselors.index');
        Route::post  ('/counselors',                       [CounselorController::class, 'store'])->name('counselors.store');
        Route::put   ('/counselors/{counselor}',           [CounselorController::class, 'update'])->name('counselors.update');
        Route::patch ('/counselors/{counselor}/toggle',    [CounselorController::class, 'toggleActive'])->name('counselors.toggle');
        Route::delete('/counselors/{counselor}',           [CounselorController::class, 'destroy'])->name('counselors.destroy');

        Route::get   ('/courses',                    [CourseController::class, 'index'])->name('courses.index');
        Route::post  ('/courses',                    [CourseController::class, 'store'])->name('courses.store');
        Route::put   ('/courses/{course}',           [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}',           [CourseController::class, 'destroy'])->name('courses.destroy');

        Route::get   ('/templates',                  [MessageTemplateController::class, 'index'])->name('templates.index');
        Route::post  ('/templates',                  [MessageTemplateController::class, 'store'])->name('templates.store');
        Route::put   ('/templates/{template}',       [MessageTemplateController::class, 'update'])->name('templates.update');
        Route::delete('/templates/{template}',       [MessageTemplateController::class, 'destroy'])->name('templates.destroy');

        Route::delete('/leads/{lead}',               [LeadController::class, 'destroy'])->name('leads.destroy');
    });
});
