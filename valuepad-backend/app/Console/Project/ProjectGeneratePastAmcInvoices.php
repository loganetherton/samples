<?php
namespace ValuePad\Console\Project;

use Illuminate\Console\Command;
use ValuePad\Console\Support\Kernel as Artisan;
use ValuePad\Core\Amc\Services\InvoiceService;

class ProjectGeneratePastAmcInvoices extends Command
{
    protected $signature = 'project:generate-past-amc-invoices {--months=1 : The number of past monthly invoices to generate}';

    protected $description = 'Generates past AMC invoices';

    public function fire(InvoiceService $invoiceService)
    {
        $months = intval($this->option('months'), 10);

        if ($months <= 0) {
            $months = 1;
        }

        $bar = $this->output->createProgressBar($months);

        for ($i = 0; $i < $months; $i++) {
            $invoiceService->generateMonthlyInvoices($i);
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->info('Done!');
    }
}
