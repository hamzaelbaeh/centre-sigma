<?php
namespace App\Console\Commands;

use App\Services\SessionBilling;
use Illuminate\Console\Command;

class CloseMonth extends Command
{
    protected $signature = 'sigma:close-month {--month= : Month being closed (YYYY-MM), default = current month}';
    protected $description = 'Month end: recompute registration estimates from the real timetable and generate next month invoices';

    public function handle(): int
    {
        $closing = $this->option('month') ?: now()->format('Y-m');
        $next = \Carbon\Carbon::createFromFormat('Y-m-d', $closing.'-01')->addMonth()->format('Y-m');
        [$created, $finalized] = SessionBilling::closeMonthAndGenerate($next);
        $this->info("Month $closing closed: $finalized registration invoice(s) recomputed, $created invoice(s) created for $next.");
        return self::SUCCESS;
    }
}
