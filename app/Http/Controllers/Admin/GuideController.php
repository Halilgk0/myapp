<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guides = Guide::with('drivers')->paginate(15);
        return view('admin.guides.index', compact('guides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.guides.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:guides,email',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'license_number' => 'nullable|string|unique:guides,license_number',
            'license_expiry' => 'nullable|date',
            'supported_nationalities' => 'nullable|array',
            'status' => 'required|in:Aktif,İzinli,Servis Dışı',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        Guide::create($request->all());

        return redirect()->route('admin.guides.index')
            ->with('success', 'Rehber başarıyla oluşturuldu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Guide $guide)
    {
        $guide->load('drivers.vehicle');
        return view('admin.guides.show', compact('guide'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guide $guide)
    {
        return view('admin.guides.edit', compact('guide'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guide $guide)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:guides,email,' . $guide->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'license_number' => 'nullable|string|unique:guides,license_number,' . $guide->id,
            'license_expiry' => 'nullable|date',
            'supported_nationalities' => 'nullable|array',
            'status' => 'required|in:Aktif,İzinli,Servis Dışı',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $guide->update($request->all());

        return redirect()->route('admin.guides.index')
            ->with('success', 'Rehber başarıyla güncellendi.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guide $guide)
    {
        $guide->delete();

        return redirect()->route('admin.guides.index')
            ->with('success', 'Rehber başarıyla silindi.');
    }

    /**
     * Export guides to styled XLS (HTML-based)
     */
    public function exportExcel(Request $request)
    {
        $guides = Guide::withCount('drivers')->orderByDesc('id')->get();

        $filename = 'guides_' . now()->format('Ymd_His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $html = '<html><head><meta charset="UTF-8">'
            .'<style>table{border-collapse:collapse;width:100%;font-family:Calibri,Arial,sans-serif;font-size:12px} th,td{border:1px solid #d0d7de;padding:6px} thead th{background:#2C3E50;color:#fff;font-weight:700;text-align:center;height:28px} tbody tr:nth-child(even){background:#ECF0F1} .num{text-align:right} .center{text-align:center}</style>'
            .'</head><body><table><colgroup>'
            .'<col style="width:70px"/><col style="width:200px"/><col style="width:220px"/><col style="width:140px"/><col style="width:140px"/><col style="width:120px"/><col style="width:200px"/><col style="width:110px"/>'
            .'</colgroup><thead><tr>'
            .'<th>ID</th><th>Ad</th><th>Email</th><th>Telefon</th><th>Lisans No</th><th>Lisans Bitiş</th><th>Desteklenen Milliyetler</th><th>Şoför Sayısı</th>'
            .'</tr></thead><tbody>';
        foreach ($guides as $g) {
            $html .= '<tr>'
                .'<td class="center">'.e($g->id).'</td>'
                .'<td>'.e($g->name).'</td>'
                .'<td>'.e($g->email).'</td>'
                .'<td>'.e($g->phone).'</td>'
                .'<td>'.e($g->license_number).'</td>'
                .'<td class="center">'.e(optional($g->license_expiry)->format('Y-m-d')).'</td>'
                .'<td>'.e($g->supported_nationalities_names ?? '').'</td>'
                .'<td class="center">'.e($g->drivers_count).'</td>'
                .'</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return response("\xEF\xBB\xBF".$html, 200, $headers);
    }

    /**
     * Export guides to PDF
     */
    public function exportPdf(Request $request)
    {
        $guides = Guide::withCount('drivers')->orderByDesc('id')->get();
        $html = view('admin.guides.pdf', compact('guides'))->render();
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $filename = 'guides_' . now()->format('Ymd_His') . '.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}