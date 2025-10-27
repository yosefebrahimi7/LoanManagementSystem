<?php

namespace App\Console\Commands;

use App\Services\PenaltyService;
use Illuminate\Console\Command;

class ProcessLoanPenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:process-penalties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process penalties for overdue loan installments';

    /**
     * Execute the console command.
     */
    public function handle(PenaltyService $penaltyService): int
    {
        $this->info('Processing overdue loan installments...');

        try {
            $result = $penaltyService->processOverdueInstallments();

            $this->info("Processed {$result['processed_count']} overdue installments");
            $this->info("Total penalty calculated: {$result['total_penalty']} cents");

            if ($result['overdue_installments'] > 0) {
                $this->warn("Warning: {$result['overdue_installments']} installments are still overdue");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error processing penalties: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

