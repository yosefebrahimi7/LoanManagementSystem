<?php

namespace App\Console\Commands;

use App\Models\LoanSchedule;
use App\Models\User;
use App\Notifications\DueDateReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDueDateReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send due date reminders for upcoming and overdue installments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting due date reminders...');

        // Send reminders for installments due in 3 days
        $this->sendUpcomingReminders();

        // Send reminders for overdue installments
        $this->sendOverdueReminders();

        $this->info('Due date reminders completed.');
    }

    /**
     * Send reminders for upcoming installments (due in 3 days)
     */
    private function sendUpcomingReminders(): void
    {
        $this->info('Checking for upcoming installments...');

        $upcomingDate = now()->addDays(3)->toDateString();
        
        $upcomingSchedules = LoanSchedule::where('due_date', $upcomingDate)
            ->where('status', LoanSchedule::STATUS_PENDING)
            ->with('loan.user')
            ->get();

        $sentCount = 0;

        foreach ($upcomingSchedules as $schedule) {
            $user = $schedule->loan->user;
            
            if ($user) {
                $user->notify(new DueDateReminderNotification($schedule, false));
                $sentCount++;
            }
        }

        $this->info("Sent {$sentCount} upcoming reminders.");
    }

    /**
     * Send reminders for overdue installments
     */
    private function sendOverdueReminders(): void
    {
        $this->info('Checking for overdue installments...');

        $today = now()->toDateString();
        
        $overdueSchedules = LoanSchedule::where('due_date', '<', $today)
            ->whereIn('status', [LoanSchedule::STATUS_PENDING, LoanSchedule::STATUS_PARTIAL])
            ->with('loan.user')
            ->get();

        $sentCount = 0;

        foreach ($overdueSchedules as $schedule) {
            // Update status to overdue if not already
            if ($schedule->status !== LoanSchedule::STATUS_OVERDUE) {
                $schedule->update(['status' => LoanSchedule::STATUS_OVERDUE]);
            }

            $user = $schedule->loan->user;
            
            if ($user) {
                $user->notify(new DueDateReminderNotification($schedule, true));
                
                // Also notify admins about overdue installments
                $admins = User::where('role', User::ROLE_ADMIN)->get();
                foreach ($admins as $admin) {
                    $admin->notify(new DueDateReminderNotification($schedule, true));
                }
                
                $sentCount++;
            }
        }

        $this->info("Sent {$sentCount} overdue reminders.");
    }
}

