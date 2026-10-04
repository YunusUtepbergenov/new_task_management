<?php

namespace App\Livewire\Mails;

use App\Models\MailDocument;
use App\Models\User;
use App\Services\MailReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;

class MailReport extends Component
{
    #[Url(except: 'sector')]
    public string $tab = 'sector';

    /**
     * Employee whose tasks are open in the side panel: a user id, "group", or null.
     */
    #[Url(as: 'person')]
    public ?string $person = null;

    public function mount(): void
    {
        Gate::authorize('viewReports', MailDocument::class);
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab === 'employee' ? 'employee' : 'sector';
        $this->person = null;
    }

    public function showPerson(string $person): void
    {
        $this->person = $person;
    }

    public function closePerson(): void
    {
        $this->person = null;
    }

    public function render(): View
    {
        $reports = app(MailReportService::class);
        $bySector = $reports->bySector();
        $byEmployee = $reports->byEmployee();

        return view('livewire.mails.mail-report', [
            'total' => $this->tab === 'employee' ? $byEmployee['total'] : $bySector['total'],
            'sectorRows' => $bySector['rows'],
            'employeeRows' => $byEmployee['rows'],
            'rows' => $this->tab === 'employee' ? $byEmployee['rows'] : $bySector['rows'],
            'panel' => $this->panel($reports, $byEmployee['rows']),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $employeeRows
     * @return array<string, mixed>|null
     */
    private function panel(MailReportService $reports, array $employeeRows): ?array
    {
        if ($this->tab !== 'employee' || $this->person === null) {
            return null;
        }

        $row = collect($employeeRows)->firstWhere('person', $this->person);
        $tasks = $row ? $reports->personTasks($this->person) : null;

        if (! $tasks) {
            return null;
        }

        $user = $row['is_group'] ? null : User::with('sector:id,name')->find((int) $this->person);

        return $row + $tasks + ['sector' => $user?->sector?->name];
    }
}
