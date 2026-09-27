<?php

namespace App\Livewire\Mails;

use App\Models\MailDocument;
use App\Services\MailReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class MailReport extends Component
{
    #[Url(except: 'sector')]
    public string $tab = 'sector';

    public function mount(): void
    {
        Gate::authorize('viewReports', MailDocument::class);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'employee' ? 'employee' : 'sector';
    }

    public function render(): View
    {
        $reports = app(MailReportService::class);
        $bySector = $reports->bySector();
        $byEmployee = $reports->byEmployee();

        return view('livewire.mails.mail-report', [
            'total' => $bySector['total'],
            'sectorRows' => $bySector['rows'],
            'employeeRows' => $byEmployee['rows'],
            'rows' => $this->tab === 'employee' ? $byEmployee['rows'] : $bySector['rows'],
        ]);
    }
}
