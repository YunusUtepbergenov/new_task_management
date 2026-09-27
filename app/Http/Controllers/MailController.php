<?php

namespace App\Http\Controllers;

use App\Exports\MailReportExport;
use App\Models\MailDocument;
use App\Models\MailFile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailController extends Controller
{
    public function index(): View
    {
        return view('page.mails.index');
    }

    public function create(): RedirectResponse
    {
        Gate::authorize('create', MailDocument::class);

        return to_route('mails.index', ['form' => 'new']);
    }

    public function show(MailDocument $mailDocument): RedirectResponse
    {
        Gate::authorize('view', $mailDocument);

        return to_route('mails.index', ['document' => $mailDocument->id]);
    }

    public function edit(MailDocument $mailDocument): RedirectResponse
    {
        Gate::authorize('update', $mailDocument);

        return to_route('mails.index', ['document' => $mailDocument->id, 'form' => $mailDocument->id]);
    }

    public function report(): View
    {
        Gate::authorize('viewReports', MailDocument::class);

        return view('page.mails.report');
    }

    public function exportReport(): BinaryFileResponse
    {
        Gate::authorize('viewReports', MailDocument::class);

        return Excel::download(new MailReportExport, 'mail-report-'.today()->format('Y-m-d').'.xlsx');
    }

    public function download(MailFile $mailFile): StreamedResponse
    {
        Gate::authorize('view', $mailFile->document);

        abort_unless(Storage::disk('local')->exists($mailFile->path()), 404);

        return Storage::disk('local')->download($mailFile->path(), $mailFile->original_name);
    }
}
