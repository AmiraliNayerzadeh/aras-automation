<?php

namespace App\Http\Controllers;

use App\Exports\TasksExport;
use App\Models\Tasks\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class TaskExportController extends Controller
{
    public function preview(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        return view('exports.preview', [
            'title' => __('tasks.title_index'),
            'contentView' => 'exports.tasks._table',
            'contentData' => ['tasks' => $this->tasks($request)],
            'withHeader' => $request->boolean('with_header', true),
            'withFooter' => $request->boolean('with_footer', true),
            'withDate' => $request->boolean('with_date', true),
            'withUser' => $request->boolean('with_user', true),
            'pdfUrl' => route('tasks.export.pdf', $request->query()),
            'excelUrl' => route('tasks.export.excel', $request->query()),
        ]);
    }

    public function pdf(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $pdf = Pdf::loadView('exports.pdf', [
            'title' => __('tasks.title_index'),
            'contentView' => 'exports.tasks._table',
            'contentData' => ['tasks' => $this->tasks($request)],
            'withHeader' => $request->boolean('with_header', true),
            'withFooter' => $request->boolean('with_footer', true),
            'withDate' => $request->boolean('with_date', true),
            'withUser' => $request->boolean('with_user', true),
        ]);

        return $pdf->download('tasks.pdf');
    }

    public function excel(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        return Excel::download(
            new TasksExport($request->user(), TaskController::filtersFromRequest($request)),
            'tasks.xlsx'
        );
    }

    protected function tasks(Request $request)
    {
        return Task::query()
            ->with(['assignees', 'createdBy', 'priority'])
            ->visibleTo($request->user())
            ->filter(TaskController::filtersFromRequest($request))
            ->latest()
            ->get();
    }
}
