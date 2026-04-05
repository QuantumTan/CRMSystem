<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Services\Reports\ReportCsvExporter;
use App\Services\Reports\ReportPdfExporter;
use App\Services\Reports\ReportService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ReportCsvExporter $csvExporter,
        private ReportPdfExporter $pdfExporter,
    ) {
    }

    public function index(ReportRequest $request): View
    {
        $filters = $request->filters();

        $data = $this->reportService->build(
            $filters['from'],
            $filters['to'],
            $request->user()
        );

        return view('reports.index', compact('data', 'filters'));
    }

    public function exportCsv(ReportRequest $request): Response
    {
        $filters = $request->filters();

        $data = $this->reportService->build(
            $filters['from'],
            $filters['to'],
            $request->user()
        );

        $csv = $this->csvExporter->build($data);
        $fileName = 'reports-' . now()->format('Ymd-His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function exportPdf(ReportRequest $request): Response
    {
        $filters = $request->filters();

        $data = $this->reportService->build(
            $filters['from'],
            $filters['to'],
            $request->user()
        );

        $pdf = $this->pdfExporter->build($data);
        $fileName = 'reports-' . now()->format('Ymd-His') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}