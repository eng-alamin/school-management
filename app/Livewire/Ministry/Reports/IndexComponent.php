<?php

namespace App\Livewire\Ministry\Reports;

use App\Exports\Ministry\GenericArrayExport;
use App\Services\Ministry\Reports\ReportDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class IndexComponent extends Component
{
    public const REPORT_TYPES = [
        'student_statistics' => 'Student Statistics',
        'teacher_statistics' => 'Teacher Statistics',
        'institution_list' => 'Institution List',
        'academic_performance' => 'Academic Performance',
        'ranking' => 'Institution Ranking',
    ];

    #[Url(history: true)]
    public string $reportType = 'student_statistics';

    #[Url(history: true)]
    public ?string $division = null;

    #[Url(history: true)]
    public ?int $academicSessionId = null;

    #[Url(history: true)]
    public ?string $verificationStatus = null;

    public string $format = 'pdf';

    protected ReportDataService $reportDataService;

    public function boot(ReportDataService $reportDataService): void
    {
        $this->reportDataService = $reportDataService;
    }

    public function getDivisionsProperty()
    {
        return \DB::table('institutions')->distinct()->orderBy('division')->pluck('division');
    }

    public function getAcademicSessionsProperty()
    {
        return \DB::table('academic_sessions')->orderByDesc('id')->get();
    }

    public function generate()
    {
        $data = match ($this->reportType) {
            'student_statistics' => $this->reportDataService->studentStatistics($this->division),
            'teacher_statistics' => $this->reportDataService->teacherStatistics($this->division),
            'institution_list' => ['rows' => $this->reportDataService->institutionList($this->verificationStatus, $this->division)],
            'academic_performance' => $this->reportDataService->academicPerformance($this->division, $this->academicSessionId),
            'ranking' => ['rows' => $this->reportDataService->ranking($this->division, $this->academicSessionId)],
            default => [],
        };

        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'report_type' => $this->reportType,
                'format' => $this->format,
                'division' => $this->division,
            ])
            ->log('Ministry report generated: ' . self::REPORT_TYPES[$this->reportType]);

        $filename = $this->reportType . '-' . now()->format('Y-m-d_His');

        if ($this->format === 'excel') {
            return $this->generateExcel($data, $filename);
        }

        return $this->generatePdf($data, $filename);
    }

    protected function generateExcel(array $data, string $filename)
    {
        [$headings, $rows] = $this->toTabular($data);

        return Excel::download(
            new GenericArrayExport($rows, $headings, self::REPORT_TYPES[$this->reportType]),
            $filename . '.xlsx'
        );
    }

    protected function generatePdf(array $data, string $filename)
    {
        $view = 'pdf.ministry.reports.' . str_replace('_', '-', $this->reportType);

        $pdf = Pdf::loadView($view, [
            'data' => $data,
            'division' => $this->division,
            'generatedAt' => now(),
            'generatedBy' => auth()->user()?->name,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename . '.pdf'
        );
    }

    /**
     * Flattens the service's data array into [headings, rows] for Excel export.
     * Only used for report types with a tabular "rows" shape.
     */
    protected function toTabular(array $data): array
    {
        $rows = collect($data['rows'] ?? $data['institution_wise'] ?? $data['class_breakdown'] ?? []);

        if ($rows->isEmpty()) {
            return [['No Data'], []];
        }

        $first = (array) $rows->first();
        $headings = array_map(fn ($k) => ucwords(str_replace('_', ' ', $k)), array_keys($first));
        $tableRows = $rows->map(fn ($row) => array_values((array) $row))->toArray();

        return [$headings, $tableRows];
    }

    public function render()
    {
        return view('livewire.ministry.reports.index-component', [
            'divisions'        => $this->divisions,
            'academicSessions' => $this->academicSessions,
        ])
        ->layout('layouts.ministry.app', [
            'title' => 'Reports | ' . setting('app_name', 'EMS'),
        ]);
    }
}