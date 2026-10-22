<?php

use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\AlumniPostModerationController;
use App\Http\Controllers\Admin\ChangeRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FormationController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ParcoursController;
use App\Http\Controllers\Admin\ParentController;
use App\Http\Controllers\Admin\PartnershipController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\SessionManagerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Alumni\DashboardController as AlumniDashboardController;
use App\Http\Controllers\Alumni\DirectoryController as ConnectedAlumniDirectoryController;
use App\Http\Controllers\Alumni\PostController as AlumniPostController;
use App\Http\Controllers\Alumni\ProfileController as AlumniProfileController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordChangeController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Direction\FinanceController;
use App\Http\Controllers\Direction\ReportController;
use App\Http\Controllers\Parent\ChildController;
use App\Http\Controllers\Public\AlumniDirectoryController;
use App\Http\Controllers\Public\AlumniPostPublicController;
use App\Http\Controllers\Student\BulletinController;
use App\Http\Controllers\Student\DocumentController;
use App\Http\Controllers\Student\FeeController;
use App\Http\Controllers\Student\GradeController;
use App\Http\Controllers\Student\ScheduleController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\CourseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IFAM Connect — Routes Web
|--------------------------------------------------------------------------
|
| Structure :
|   - Authentification (login / logout / password change)
|   - Espace Administrateur  (/admin/*)
|   - Espaces autres rôles   (à créer en phases suivantes)
|
*/

// ── Page d'accueil → redirection vers login ──────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Authentification ─────────────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Mot de passe oublié & Réinitialisation ───────────────────────────────
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ActivationController::class, 'show'])->name('password.reset');
Route::post('/password/reset/{token}', [ActivationController::class, 'activate'])->name('password.update');

// ── Activation de compte ──────────────────────────────────────────────────
Route::get('/activation/{token}', [ActivationController::class, 'show'])->name('activation.show');
Route::post('/activation/{token}', [ActivationController::class, 'activate'])->name('activation.activate');

// ── Redirection unifiée du tableau de bord selon le rôle ──────────────────
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }
    if ($user->hasRole([
        'administrateur',
        'responsable_site',
        'direction_daf',
        'responsable_administratif_financier',
        'responsable_administratif',
        'secretaire_administratif',
        'charge_scolarite',
        'agent_administratif',
        'responsable_financier',
        'comptable',
        'caissier',
        'agent_recouvrement',
        'direction_daa',
        'responsable_daa',
        'responsable_etudes',
        'responsable_insertion',
    ])) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->hasRole('direction')) {
        return redirect()->route('direction.dashboard');
    }
    if ($user->hasRole('enseignant')) {
        return redirect()->route('teacher.dashboard');
    }
    if ($user->hasRole('etudiant')) {
        return redirect()->route('student.dashboard');
    }
    if ($user->hasRole('alumni')) {
        return redirect()->route('alumni.dashboard');
    }
    if ($user->hasRole('parent')) {
        return redirect()->route('parent.dashboard');
    }

    return redirect()->route('login')->withErrors(['email' => 'Votre compte ne possède aucun rôle valide.']);
})->middleware('auth')->name('dashboard');

// Changement de mot de passe obligatoire (après 1ère connexion ou reset)
Route::middleware('auth')->group(function () {
    Route::get('/password/change', fn () => view('auth.change-password'))->name('password.change');
    Route::post('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');

    // Contact administration (accessible par tous les rôles connectés)
    Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    Route::get('/alumni-directory', [ConnectedAlumniDirectoryController::class, 'index'])->name('alumni.directory.index');

    // Notifications personnelles (accessibles par TOUS les utilisateurs connectés)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/open', [NotificationController::class, 'markReadAndRedirect'])->name('notifications.open');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Alias admin.* pour rétro-compatibilité totale
    Route::get('/admin/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/admin/notifications/{notification}/open', [NotificationController::class, 'markReadAndRedirect'])->name('admin.notifications.open');
    Route::patch('/admin/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('admin.notifications.markRead');
    Route::patch('/admin/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('admin.notifications.markAllRead');
    Route::delete('/admin/notifications/{notification}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
});

// ── Espace Administrateur, DAF & DAA ──────────────────────────────────────
Route::middleware(['auth', 'role:administrateur|responsable_site|direction_daf|responsable_administratif_financier|responsable_administratif|secretaire_administratif|charge_scolarite|agent_administratif|responsable_financier|comptable|caissier|agent_recouvrement|direction_daa|responsable_daa|responsable_etudes|responsable_insertion'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // ── Utilisateurs ─────────────────────────────────────────
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('users/{user}/resend-activation', [UserController::class, 'resendActivation'])->name('users.resendActivation');

        Route::resource('students', StudentController::class)->only(['index', 'show', 'edit', 'update']);

        // ── Parents ───────────────────────────────────────────────
        Route::resource('parents', ParentController::class);

        // ── Enseignants ───────────────────────────────────────────
        Route::resource('teachers', TeacherController::class);

        // ── Académique (Phase 5 → 7) ──────────────────────────────
        Route::resource('parcours', ParcoursController::class)->except(['show']);
        Route::resource('formations', FormationController::class);
        Route::resource('sessions', SessionController::class);
        Route::resource('enrollments', EnrollmentController::class);
        Route::resource('modules', ModuleController::class);
        Route::resource('courses', App\Http\Controllers\Admin\CourseController::class);
        Route::patch('courses/{course}/status', [App\Http\Controllers\Admin\CourseController::class, 'updateStatus'])->name('courses.status');
        Route::post('courses/{course}/documents', [App\Http\Controllers\Admin\CourseController::class, 'uploadDocument'])->name('courses.documents.store');
        Route::delete('courses/{course}/documents/{document}', [App\Http\Controllers\Admin\CourseController::class, 'destroyDocument'])->name('courses.documents.destroy');
        Route::resource('rooms', RoomController::class);
        Route::resource('evaluations', EvaluationController::class);
        Route::patch('evaluations/{evaluation}/status', [EvaluationController::class, 'updateStatus'])->name('evaluations.status');
        Route::resource('grades', App\Http\Controllers\Admin\GradeController::class);
        Route::patch('attendance/{course}/sheets', [App\Http\Controllers\Admin\AttendanceController::class, 'updateSheetStatus'])->name('attendance.sheets.update');
        Route::resource('attendance', App\Http\Controllers\Admin\AttendanceController::class);
        Route::get('/results', [ResultController::class, 'index'])->name('results.index');
        Route::get('/results/{student}', [ResultController::class, 'show'])->name('results.show');
        Route::get('/bulletins', [App\Http\Controllers\Admin\BulletinController::class, 'index'])->name('bulletins.index');
        Route::get('/bulletins/{student}', [App\Http\Controllers\Admin\BulletinController::class, 'show'])->name('bulletins.show');
        Route::get('/bulletins/{student}/pdf', [App\Http\Controllers\Admin\BulletinController::class, 'pdf'])->name('bulletins.pdf');
        Route::post('/bulletins/{student}/publish', [App\Http\Controllers\Admin\BulletinController::class, 'publish'])->name('bulletins.publish');

        // ── Documents ─────────────────────────────────────────────
        Route::resource('documents', App\Http\Controllers\Admin\DocumentController::class)->except(['edit', 'update']);
        Route::patch('documents/{document}/verify', [App\Http\Controllers\Admin\DocumentController::class, 'toggleVerified'])->name('documents.toggleVerified');

        // ── Finance ───────────────────────────────────────────────
        Route::resource('fees', App\Http\Controllers\Admin\FeeController::class)->only(['index']);
        Route::get('/payments/print', [PaymentController::class, 'printReport'])->name('payments.print');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::patch('/payments/{payment}/receipt/publish', [PaymentController::class, 'publishReceipt'])->name('payments.receipt.publish');
        Route::delete('/payments/{payment}/receipt/publish', [PaymentController::class, 'unpublishReceipt'])->name('payments.receipt.unpublish');
        Route::patch('/payments/{payment}/validate', [PaymentController::class, 'validatePayment'])->name('payments.validate');
        Route::patch('/payments/{payment}/reject', [PaymentController::class, 'rejectPayment'])->name('payments.reject');
        Route::resource('payments', PaymentController::class);
        Route::resource('discounts', DiscountController::class);
        Route::resource('expense-categories', ExpenseCategoryController::class);
        Route::resource('expenses', ExpenseController::class);

        // ── Organisation ──────────────────────────────────────────
        Route::resource('session-managers', SessionManagerController::class);
        Route::resource('change-requests', ChangeRequestController::class)->only(['index', 'show', 'update']);

        // ── Partenariats & Alumni ─────────────────────────────────
        Route::resource('partnerships', PartnershipController::class);
        Route::post('alumnis/{studentProfile}/convert', [AlumniController::class, 'convertStudent'])->name('alumnis.convert');
        Route::patch('alumnis/{alumni}/toggle-approval', [AlumniController::class, 'toggleApproval'])->name('alumnis.toggleApproval');
        Route::patch('alumnis/{alumni}/toggle-directory', [AlumniController::class, 'toggleDirectory'])->name('alumnis.toggleDirectory');
        Route::resource('alumnis', AlumniController::class);

        // Modération des publications Alumni
        Route::get('alumni-posts', [AlumniPostModerationController::class, 'index'])->name('alumnis.posts.index');
        Route::get('alumni-posts/{post}', [AlumniPostModerationController::class, 'show'])->name('alumnis.posts.show');
        Route::post('alumni-posts/{post}/approve', [AlumniPostModerationController::class, 'approve'])->name('alumnis.posts.approve');
        Route::post('alumni-posts/{post}/reject', [AlumniPostModerationController::class, 'reject'])->name('alumnis.posts.reject');
        Route::post('alumni-posts/{post}/unpublish', [AlumniPostModerationController::class, 'unpublish'])->name('alumnis.posts.unpublish');

        // ── Envoi de notifications (réservé aux gestionnaires) ────
        Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');

        // ── Contact (Phase 4) ─────────────────────────────────────
        Route::resource('contact', App\Http\Controllers\Admin\ContactController::class)->only(['index', 'show']);

        // ── Paramétrage SuperAdmin ────────────────────────────────
        Route::middleware('role:administrateur')->group(function () {
            Route::patch('sites/{site}/toggle-active', [SiteController::class, 'toggleActive'])->name('sites.toggle-active');
            Route::resource('sites', SiteController::class);

            Route::resource('roles', RoleController::class);

            Route::patch('departments/{department}/toggle-active', [DepartmentController::class, 'toggleActive'])->name('departments.toggle-active');
            Route::resource('departments', DepartmentController::class);

            Route::patch('services/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('services.toggle-active');
            Route::resource('services', ServiceController::class);
        });
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.updateProfile');
        Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.updatePassword');
    });

// ── Espace Direction ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:direction'])
    ->prefix('direction')
    ->name('direction.')
    ->group(function () {
        Route::get('/dashboard', App\Http\Controllers\Direction\DashboardController::class)->name('dashboard');
        Route::get('/students', [App\Http\Controllers\Direction\StudentController::class,  'index'])->name('students.index');
        Route::get('/students/{student}', [App\Http\Controllers\Direction\StudentController::class,  'show'])->name('students.show');
        Route::get('/finance', [FinanceController::class,  'index'])->name('finance.index');

        // Consultation des publications Alumni disponibles par la Direction
        Route::get('alumni-posts', [AlumniPostModerationController::class, 'index'])->name('alumni-posts.index');

        // Rapports des services transmis à la Direction
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/missing', [ReportController::class, 'missing'])->name('missing');
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/{report}', [ReportController::class, 'show'])->name('show');
            Route::post('/{report}/return', [ReportController::class, 'returnForCorrection'])->name('return');
            Route::get('/{report}/pdf', [ReportController::class, 'exportPdf'])->name('pdf');
        });
    });

// ── Espace Service : Rapports & Activités ─────────────────────────────────
Route::middleware(['auth'])
    ->prefix('service')
    ->name('service.')
    ->group(function () {
        Route::resource('reports', App\Http\Controllers\Service\ReportController::class);
        Route::post('reports/{report}/send', [App\Http\Controllers\Service\ReportController::class, 'send'])->name('reports.send');
    });

// ── Espace Enseignant ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:enseignant'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        Route::get('/dashboard', App\Http\Controllers\Teacher\DashboardController::class)->name('dashboard');
        Route::resource('courses', CourseController::class)->only(['index', 'show']);
        Route::delete('/courses/{course}/archive', [CourseController::class, 'hideArchived'])->name('courses.archive.destroy');
        Route::resource('attendance', AttendanceController::class)->only(['index', 'store', 'update']);
        Route::resource('grades', App\Http\Controllers\Teacher\GradeController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('/grades/evaluation', [App\Http\Controllers\Teacher\GradeController::class, 'createEvaluation'])->name('grades.createEvaluation');
        Route::get('/grades/assigned/{evaluation}', [App\Http\Controllers\Teacher\GradeController::class, 'showAssigned'])->name('grades.assigned.show');
        Route::post('/grades/assigned/{evaluation}', [App\Http\Controllers\Teacher\GradeController::class, 'storeAssigned'])->name('grades.assigned.store');
        Route::post('/courses/{course}/documents', [CourseController::class, 'uploadDocument'])->name('courses.documents.store');
    });

// ── Espace Étudiant ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:etudiant'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', App\Http\Controllers\Student\DashboardController::class)->name('dashboard');
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
        Route::get('/grades', [GradeController::class, 'index'])->name('grades');
        Route::get('/bulletins', [BulletinController::class, 'index'])->name('bulletins.index');
        Route::get('/bulletins/{publication}', [BulletinController::class, 'show'])->name('bulletins.show');
        Route::get('/bulletins/{publication}/pdf', [BulletinController::class, 'pdf'])->name('bulletins.pdf');
        Route::get('/fees', [FeeController::class, 'index'])->name('fees');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'studentReceipt'])->name('payments.receipt');
        Route::get('/attendance', [App\Http\Controllers\Student\AttendanceController::class, 'index'])->name('attendance');
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::get('/course-documents/{courseDocument}/download', [DocumentController::class, 'downloadCourseDocument'])->name('course-documents.download');
        Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

// ── Espace Parent ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:parent'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {
        Route::get('/dashboard', App\Http\Controllers\Parent\DashboardController::class)->name('dashboard');
        Route::get('/children', [ChildController::class, 'index'])->name('children');
        Route::get('/children/{child}/bulletins/{publication}', [App\Http\Controllers\Parent\BulletinController::class, 'show'])->name('bulletins.show');
        Route::get('/children/{child}/bulletins/{publication}/pdf', [App\Http\Controllers\Parent\BulletinController::class, 'pdf'])->name('bulletins.pdf');
        Route::get('/fees', [App\Http\Controllers\Parent\FeeController::class, 'index'])->name('fees');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'parentReceipt'])->name('payments.receipt');
    });

// ── Espace Alumni ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:alumni'])
    ->prefix('alumni')
    ->name('alumni.')
    ->group(function () {
        Route::get('/dashboard', [AlumniDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AlumniProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AlumniProfileController::class, 'update'])->name('profile.update');

        Route::resource('posts', AlumniPostController::class);
        Route::post('/posts/{post}/submit', [AlumniPostController::class, 'submit'])->name('posts.submit');
        Route::post('/posts/{post}/cancel', [AlumniPostController::class, 'cancelSubmission'])->name('posts.cancel');
    });

// ── Pages Publiques Alumni (Sans Authentification) ────────────────────────
Route::prefix('anciens-etudiants')
    ->name('alumni.public.')
    ->group(function () {
        Route::get('/', [AlumniDirectoryController::class, 'index'])->name('index');
        Route::get('/profil/{alumni}', [AlumniDirectoryController::class, 'show'])->name('show');
        Route::get('/actualites', [AlumniPostPublicController::class, 'index'])->name('posts.index');
        Route::get('/actualites/{slug}', [AlumniPostPublicController::class, 'show'])->name('posts.show');
    });
