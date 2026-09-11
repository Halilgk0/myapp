<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\OperationController;
use App\Http\Controllers\Admin\GuideController;
use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Agency\AccountingController as AgencyAccountingController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\TicketController as PublicTicketController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AgencyNetworkController;
use App\Http\Controllers\InfoNotificationController;
use App\Http\Controllers\LanguageController;


// Main Route - Redirect to login page
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// CSRF Token Refresh Route (for long-lived pages)
Route::get('/csrf-refresh', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

Route::middleware('auth')->get('/info-notification', InfoNotificationController::class)->name('info.notification');
Route::middleware('auth')->post('/language', [LanguageController::class, 'update'])->name('language.update');

// Admin Dashboard Route
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Vehicle Routes
    Route::resource('vehicles', VehicleController::class);
    Route::get('vehicles/locations', [VehicleController::class, 'locations'])->name('vehicles.locations');
    Route::get('vehicles/export/excel', [VehicleController::class, 'exportExcel'])->name('vehicles.export.excel');
    Route::get('vehicles/export/pdf', [VehicleController::class, 'exportPdf'])->name('vehicles.export.pdf');

    // Driver Routes
    Route::post('drivers/{driver}/reset-password', [DriverController::class, 'resetPassword'])->name('drivers.reset-password');
    Route::resource('drivers', DriverController::class);
    Route::get('drivers/export/excel', [DriverController::class, 'exportExcel'])->name('drivers.export.excel');
    Route::get('drivers/export/pdf', [DriverController::class, 'exportPdf'])->name('drivers.export.pdf');

    // Guide Routes
    Route::resource('guides', GuideController::class);
    Route::get('guides/export/excel', [GuideController::class, 'exportExcel'])->name('guides.export.excel');
    Route::get('guides/export/pdf', [GuideController::class, 'exportPdf'])->name('guides.export.pdf');

    // Agency Routes
    Route::get('agencies/network', [AgencyNetworkController::class, 'index'])->name('agencies.network');
    Route::put('agencies/{agency}/tour-sharing', [AgencyController::class, 'updateTourSharing'])->name('agencies.tour-sharing.update');
    Route::get('agencies/{agency}/tour-sharing/{tour}/pricing', [AgencyController::class, 'showTourPricing'])
        ->name('agencies.tour-sharing.pricing.show');
    Route::put('agencies/{agency}/tour-sharing/{tour}/pricing', [AgencyController::class, 'updateTourPricing'])
        ->name('agencies.tour-sharing.pricing.update');
    Route::resource('agencies', AgencyController::class)->except(['create', 'store']);

    // Accounting Routes
    Route::get('accounting', [AccountingController::class, 'index'])->name('accounting.index');
    Route::get('accounting/rates', [AccountingController::class, 'rates'])->name('accounting.rates');
    Route::get('accounting/filter', [AccountingController::class, 'filter'])->name('accounting.filter');
    Route::get('accounting/chart-data', [AccountingController::class, 'chartData'])->name('accounting.chart-data');
    Route::post('accounting/settlements', [AccountingController::class, 'submitSettlement'])->name('accounting.settlements.submit');
    Route::get('accounting/create', [AccountingController::class, 'create'])->name('accounting.create');
    Route::post('accounting', [AccountingController::class, 'store'])->name('accounting.store');
    Route::get('accounting/{transaction}/edit', [AccountingController::class, 'edit'])->name('accounting.edit');
    Route::put('accounting/{transaction}', [AccountingController::class, 'update'])->name('accounting.update');
    Route::delete('accounting/{transaction}', [AccountingController::class, 'destroy'])->name('accounting.destroy');

    // Ticket Routes
    Route::get('tickets/check-voucher', [TicketController::class, 'checkVoucher'])->name('tickets.check-voucher');
    Route::resource('tickets', TicketController::class);
    Route::get('tickets/export/excel', [TicketController::class, 'exportExcel'])->name('tickets.export.excel');
    Route::get('tickets/export/pdf', [TicketController::class, 'exportPdf'])->name('tickets.export.pdf');

    // Tour Routes (define specific routes BEFORE resource to avoid shadowing)
    Route::get('tours/suggestions', [TourController::class, 'suggestions'])->name('tours.suggestions');
    Route::get('tours/{tour}/available-dates', [TourController::class, 'getAvailableDates'])->name('tours.available-dates');
    Route::get('tours/{tour}/details', [TourController::class, 'getDetails'])->name('tours.details');
    Route::resource('tours', TourController::class);
    Route::get('tours/export/excel', [TourController::class, 'exportExcel'])->name('tours.export.excel');
    Route::get('tours/export/pdf', [TourController::class, 'exportPdf'])->name('tours.export.pdf');

    // Operation Routes
    Route::get('operations', [OperationController::class, 'index'])->name('operations.index');
    Route::post('operations/assign-driver-to-vehicle', [OperationController::class, 'assignDriverToVehicle'])->name('operations.assign-driver-to-vehicle');
    Route::post('operations/remove-driver-from-vehicle', [OperationController::class, 'removeDriverFromVehicle'])->name('operations.remove-driver-from-vehicle');
    Route::post('operations/assign-ticket-to-vehicle', [OperationController::class, 'assignTicketToVehicle'])->name('operations.assign-ticket-to-vehicle');
    Route::post('operations/assign-multiple-tickets-to-vehicle', [OperationController::class, 'assignMultipleTicketsToVehicle'])->name('operations.assign-multiple-tickets-to-vehicle');
    Route::post('operations/assign-ticket-to-driver', [OperationController::class, 'assignTicketToDriver'])->name('operations.assign-ticket-to-driver');
    Route::post('operations/remove-ticket-from-driver', [OperationController::class, 'removeTicketFromDriver'])->name('operations.remove-ticket-from-driver');
    Route::post('operations/assign-driver-to-guide', [OperationController::class, 'assignDriverToGuide'])->name('operations.assign-driver-to-guide');
    Route::post('operations/remove-driver-from-guide', [OperationController::class, 'removeDriverFromGuide'])->name('operations.remove-driver-from-guide');
    Route::post('operations/assign-ticket-to-tour', [OperationController::class, 'assignTicketToTour'])->name('operations.assign-ticket-to-tour');
    Route::post('operations/remove-ticket-from-tour', [OperationController::class, 'removeTicketFromTour'])->name('operations.remove-ticket-from-tour');
    Route::get('operations/system-status', [OperationController::class, 'getSystemStatus'])->name('operations.system-status');

    // Debug route - kullanıcı bilgilerini kontrol et
    Route::get('debug/user', function() {
        $user = auth()->user();
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'level' => $user->level,
                'is_active' => $user->is_active,
            ] : null,
        ]);
    })->name('admin.debug.user');

    // Debug route - milliyet, tur ve yolcu bilgilerini kontrol et
    Route::get('debug/tickets', function() {
        $tickets = \App\Models\Ticket::with(['tour', 'passengers'])->get();
        $debug = [];
        foreach($tickets as $ticket) {
            $debug[] = [
                'id' => $ticket->id,
                'customer_name' => $ticket->customer_name,
                'customer_nationality' => $ticket->customer_nationality,
                'nationality_name' => $ticket->nationality_name,
                'tour_name' => $ticket->tour_name,
                'tour_date' => $ticket->tour_date ? $ticket->tour_date->format('d.m.Y') : null,
                'tour_relation' => $ticket->tour ? $ticket->tour->name : 'İlişki Yok',
                'total_passengers' => $ticket->total_passengers,
                'passenger_breakdown' => $ticket->passenger_breakdown,
                'passenger_count_text' => $ticket->passenger_count_text,
                /* made by @hllgkx.0 */
            ];
        }
        return response()->json($debug);
    })->name('debug.tickets');

    // Debug route - şoför kullanıcılarını listele
    Route::get('debug/drivers', function() {
        $drivers = \App\Models\User::where('level', 2)->get(['id', 'name', 'email', 'is_active', 'level']);
        return response()->json([
            'count' => $drivers->count(),
            'drivers' => $drivers
        ]);
    })->name('debug.drivers');

    // Saved Maps (kullanıcı bazlı poligon kütüphanesi)
    Route::get('saved-maps', [\App\Http\Controllers\Admin\SavedMapController::class, 'index'])->name('saved-maps.index');
    Route::post('saved-maps', [\App\Http\Controllers\Admin\SavedMapController::class, 'store'])->name('saved-maps.store');
    Route::delete('saved-maps/{savedMap}', [\App\Http\Controllers\Admin\SavedMapController::class, 'destroy'])->name('saved-maps.destroy');

    // Rota planı endpoint'leri (UI driver detay sayfasında — admin/drivers/{driver})
    Route::post('routes/{driver}/start/{ticket}', [\App\Http\Controllers\Admin\RouteController::class, 'setStart'])->name('routes.set-start');
    Route::post('routes/{driver}/recalculate', [\App\Http\Controllers\Admin\RouteController::class, 'recalculate'])->name('routes.recalculate');
    Route::post('routes/{driver}/clear', [\App\Http\Controllers\Admin\RouteController::class, 'clear'])->name('routes.clear');

    // Ticket Request Routes (approval system)
    Route::get('ticket-requests', [\App\Http\Controllers\Admin\TicketRequestController::class, 'index'])->name('ticket-requests.index');
    Route::get('ticket-requests/{ticketRequest}/edit', [\App\Http\Controllers\Admin\TicketRequestController::class, 'edit'])->name('ticket-requests.edit');
    Route::put('ticket-requests/{ticketRequest}', [\App\Http\Controllers\Admin\TicketRequestController::class, 'update'])->name('ticket-requests.update');
    Route::get('ticket-requests/{ticketRequest}', [\App\Http\Controllers\Admin\TicketRequestController::class, 'show'])->name('ticket-requests.show');
    Route::post('ticket-requests/{ticketRequest}/approve', [\App\Http\Controllers\Admin\TicketRequestController::class, 'approve'])->name('ticket-requests.approve');
    Route::post('ticket-requests/{ticketRequest}/reject', [\App\Http\Controllers\Admin\TicketRequestController::class, 'reject'])->name('ticket-requests.reject');
    Route::post('ticket-requests/{ticketRequest}/return', [\App\Http\Controllers\Admin\TicketRequestController::class, 'returnToAgency'])->name('ticket-requests.return');
});

// Driver Routes
Route::middleware(['auth', 'driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DriverDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DriverDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::get('/vehicle', [DriverDashboardController::class, 'vehicle'])->name('vehicle');
    Route::get('/tickets', [DriverDashboardController::class, 'tickets'])->name('tickets');
    Route::post('/location', [DriverDashboardController::class, 'updateLocation'])->name('location.update');
});

// Public Ticket Routes (No authentication required)
Route::prefix('tickets')->name('tickets.')->group(function () {
    Route::get('/login', [PublicTicketController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [PublicTicketController::class, 'login'])->name('login.post');
    Route::get('/dashboard', [PublicTicketController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [PublicTicketController::class, 'logout'])->name('logout');
});

// Customer Routes (voucher_no ile giriş, mobil odaklı panel)
Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Customer\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Customer\AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [\App\Http\Controllers\Customer\AuthController::class, 'logout'])->name('logout');

    Route::middleware('customer')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Customer\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/live', [\App\Http\Controllers\Customer\DashboardController::class, 'liveStatus'])->name('live');
    });
});

Route::middleware(['auth'])->prefix('agencies')->name('agencies.')->group(function () {
    Route::get('/network', [AgencyNetworkController::class, 'index'])->name('network');
    Route::post('/requests', [AgencyNetworkController::class, 'storeRequest'])->name('requests.store');
    Route::post('/requests/{agencyConnectionRequest}/accept', [AgencyNetworkController::class, 'accept'])->name('requests.accept');
    Route::post('/requests/{agencyConnectionRequest}/reject', [AgencyNetworkController::class, 'reject'])->name('requests.reject');
    Route::delete('/requests/{agencyConnectionRequest}', [AgencyNetworkController::class, 'withdraw'])->name('requests.withdraw');
    Route::get('/lookup-user', [AgencyNetworkController::class, 'lookupUser'])->name('users.lookup');
    Route::get('/users/search', [AgencyNetworkController::class, 'searchUsers'])->name('users.search');
});

Route::middleware(['auth', 'agency'])->prefix('agency')->name('agency.')->group(function () {
    Route::get('/agencies', [\App\Http\Controllers\Agency\AgencyManagementController::class, 'index'])->name('agencies.index');
    Route::get('/tours', [\App\Http\Controllers\Agency\TourManagementController::class, 'index'])->name('tours.index');
    Route::get('/accounting', [AgencyAccountingController::class, 'index'])->name('accounting.index');
    Route::get('/accounting/rates', [AgencyAccountingController::class, 'rates'])->name('accounting.rates');
    Route::get('/accounting/chart-data', [AgencyAccountingController::class, 'chartData'])->name('accounting.chart-data');
    Route::post('/accounting/settlements', [AgencyAccountingController::class, 'submitSettlement'])->name('accounting.settlements.submit');
    Route::post('/accounting/settlements/{settlementRequest}/approve', [AgencyAccountingController::class, 'approveSettlement'])->name('accounting.settlements.approve');
    Route::post('/accounting/settlements/{settlementRequest}/reject', [AgencyAccountingController::class, 'rejectSettlement'])->name('accounting.settlements.reject');
    
    // Ticket Routes
    Route::get('/tickets', [\App\Http\Controllers\Agency\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\Agency\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\Agency\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/fx-preview', [\App\Http\Controllers\Agency\TicketController::class, 'fxPreview'])->name('tickets.fx-preview');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Agency\TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [\App\Http\Controllers\Agency\TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [\App\Http\Controllers\Agency\TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [\App\Http\Controllers\Agency\TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::delete('/ticket-requests/{ticketRequest}', [\App\Http\Controllers\Agency\TicketController::class, 'cancelRequest'])->name('ticket-requests.cancel');
    Route::get('/ticket-requests/{ticketRequest}/edit', [\App\Http\Controllers\Agency\TicketController::class, 'editRequest'])->name('ticket-requests.edit');
    Route::put('/ticket-requests/{ticketRequest}', [\App\Http\Controllers\Agency\TicketController::class, 'updateRequest'])->name('ticket-requests.update');
    Route::get('/tours/{tour}/details', [\App\Http\Controllers\Agency\TicketController::class, 'getTourDetails'])->name('tours.details');
});

Route::middleware(['auth'])->get('/session/ping', function () {
    $user = auth()->user();

    return response()->json([
        'authenticated' => (bool) $user,
        'user' => $user ? [
            'id' => $user->id,
            'level' => $user->level,
            'is_admin' => $user->isAdmin(),
            'is_agency' => method_exists($user, 'isAgency') ? $user->isAgency() : false,
        ] : null,
    ]);
})->name('session.ping');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

 