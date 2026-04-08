<?php

namespace App\Http\Controllers;

use App\Exports\ReportCsvExport;
use App\Http\Requests\ReportRequest;
use App\Services\Reports\ReportCsvExporter;
use App\Services\Reports\ReportPdfExporter;
use App\Services\Reports\ReportService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ReportCsvExporter $csvExporter,
        private ReportPdfExporter $pdfExporter,
    ) {}

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

    public function exportCsv(ReportRequest $request): BinaryFileResponse
    {
        $filters = $request->filters();

        $data = $this->reportService->build(
            $filters['from'],
            $filters['to'],
            $request->user()
        );

        $rows = $this->csvExporter->build($data);
        $fileName = 'reports-'.now()->format('Ymd-His').'.csv';

        return Excel::download(new ReportCsvExport($rows), $fileName, ExcelFormat::CSV, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
        $fileName = 'reports-'.now()->format('Ymd-His').'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
