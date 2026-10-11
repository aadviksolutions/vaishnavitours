<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    /**
     * Reports Dashboard & Category-Wise View.
     */
    public function index(Request $request, ReportService $reportService)
    {
        $categories = $reportService->getCategories();
        $currentCategory = $request->input('category', 'bookings');

        if (! array_key_exists($currentCategory, $categories)) {
            $currentCategory = 'bookings';
        }

        $reportData = $reportService->getReportData($currentCategory, $request);

        // Filter options for dropdowns
        $vehicles = Vehicle::orderBy('name')->get();
        $drivers = Driver::orderBy('name')->get();

        return view('admin.reports.index', [
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'categoryMeta' => $categories[$currentCategory],
            'data' => $reportData,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'request' => $request,
        ]);
    }

    /**
     * Download Excel (.xlsx) for selected category with active filters.
     */
    public function exportExcel(Request $request, ReportService $reportService): StreamedResponse
    {
        $category = $request->input('category', 'bookings');

        return $reportService->exportExcel($category, $request);
    }

    /**
     * Download PDF (.pdf) for selected category with active filters.
     */
    public function exportPdf(Request $request, ReportService $reportService)
    {
        $category = $request->input('category', 'bookings');

        return $reportService->exportPdf($category, $request);
    }

    /**
     * Print-friendly view for selected category.
     */
    public function printReport(Request $request, ReportService $reportService)
    {
        $category = $request->input('category', 'bookings');
        $data = $reportService->getReportData($category, $request, true);

        return view('admin.reports.print', compact('data', 'category'));
    }

    /**
     * Backward-compatible CSV export for Bookings.
     */
    public function exportBookingsCsv(Request $request, ReportService $reportService)
    {
        return $this->exportExcel($request, $reportService);
    }
}
