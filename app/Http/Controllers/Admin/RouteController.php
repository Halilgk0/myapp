<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Services\RouteOptimizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    public function __construct(protected RouteOptimizer $optimizer)
    {
    }

    /**
     * Bir bileti başlangıç noktası olarak ata + rotayı yeniden hesapla.
     */
    public function setStart(Request $request, User $driver, Ticket $ticket)
    {
        $this->ensureDriver($driver);

        if ((int) $ticket->driver_id !== (int) $driver->id) {
            return back()->withErrors(['ticket' => 'Bu bilet bu şoföre atanmamış.']);
        }

        $date = $this->resolveDate($request->input('date'));
        if (optional($ticket->tour_date)->toDateString() !== $date) {
            return back()->withErrors(['ticket' => 'Bilet seçili tarihten farklı bir güne ait.']);
        }

        if (!$ticket->location || !$ticket->location->latitude || !$ticket->location->longitude) {
            return back()->withErrors(['ticket' => 'Bu biletin konumu belirlenmemiş; başlangıç olarak atanamaz.']);
        }

        // Saving event'in is_active ve driver_id'yi sıfırlamasını önlemek için raw update kullan.
        DB::transaction(function () use ($driver, $date, $ticket) {
            DB::table('tickets')
                ->where('driver_id', $driver->id)
                ->whereDate('tour_date', $date)
                ->update(['is_route_start' => false, 'route_order' => null]);

            DB::table('tickets')->where('id', $ticket->id)->update(['is_route_start' => true]);
        });

        $result = $this->optimizer->optimize($driver, $date);

        return redirect()->route('admin.drivers.show', ['driver' => $driver])
            ->with('success', $result['message']);
    }

    /**
     * Mevcut başlangıçla rotayı yeniden hesapla (anlık trafik için).
     */
    public function recalculate(Request $request, User $driver)
    {
        $this->ensureDriver($driver);
        $date = $this->resolveDate($request->input('date'));
        $result = $this->optimizer->optimize($driver, $date);
        return redirect()->route('admin.drivers.show', ['driver' => $driver])
            ->with('success', $result['message']);
    }

    /**
     * Başlangıç işaretini ve rota sırasını temizle.
     */
    public function clear(Request $request, User $driver)
    {
        $this->ensureDriver($driver);
        $date = $this->resolveDate($request->input('date'));

        DB::table('tickets')
            ->where('driver_id', $driver->id)
            ->whereDate('tour_date', $date)
            ->update([
                'is_route_start' => false,
                'route_order' => null,
                'route_optimized_at' => null,
            ]);

        return redirect()->route('admin.drivers.show', ['driver' => $driver])
            ->with('success', 'Rota temizlendi.');
    }

    protected function ensureDriver(User $user): void
    {
        if ((int) $user->level !== (int) User::LEVEL_DRIVER) {
            abort(404);
        }
    }

    /**
     * Query/post'tan gelen tarihi normalize et. Boşsa veya geçersizse bugün döner.
     */
    protected function resolveDate(?string $raw): string
    {
        if (is_string($raw) && $raw !== '') {
            try {
                return Carbon::parse($raw, 'Europe/Istanbul')->toDateString();
            } catch (\Throwable $e) {
                // fall through
            }
        }
        return Carbon::now('Europe/Istanbul')->toDateString();
    }
}
