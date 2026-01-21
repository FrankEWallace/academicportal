<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SystemSettingsService;
use App\Models\GradingScale;

class InitializeSystemDefaults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:initialize {--force : Force initialization even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize default system settings and grading scales';

    /**
     * Execute the console command.
     */
    public function handle(SystemSettingsService $settingsService)
    {
        $this->info(' Initializing system defaults...');
        $this->newLine();

        // Initialize System Settings
        $this->info(' Creating default system settings...');
        $settingsService->initializeDefaults();
        $this->info(' System settings initialized');
        $this->newLine();

        // Initialize Grading Scales
        $this->info(' Creating default grading scales...');
        $defaults = [
            ['grade' => 'A+', 'min' => 95, 'max' => 100, 'point' => 4.0, 'order' => 1],
            ['grade' => 'A', 'min' => 90, 'max' => 94.99, 'point' => 4.0, 'order' => 2],
            ['grade' => 'A-', 'min' => 85, 'max' => 89.99, 'point' => 3.7, 'order' => 3],
            ['grade' => 'B+', 'min' => 80, 'max' => 84.99, 'point' => 3.3, 'order' => 4],
            ['grade' => 'B', 'min' => 75, 'max' => 79.99, 'point' => 3.0, 'order' => 5],
            ['grade' => 'B-', 'min' => 70, 'max' => 74.99, 'point' => 2.7, 'order' => 6],
            ['grade' => 'C+', 'min' => 65, 'max' => 69.99, 'point' => 2.3, 'order' => 7],
            ['grade' => 'C', 'min' => 60, 'max' => 64.99, 'point' => 2.0, 'order' => 8],
            ['grade' => 'C-', 'min' => 55, 'max' => 59.99, 'point' => 1.7, 'order' => 9],
            ['grade' => 'D', 'min' => 50, 'max' => 54.99, 'point' => 1.0, 'order' => 10],
            ['grade' => 'F', 'min' => 0, 'max' => 49.99, 'point' => 0.0, 'order' => 11, 'is_passing' => false],
        ];

        $created = 0;
        foreach ($defaults as $data) {
            $existing = GradingScale::where('grade', $data['grade'])->first();
            
            if (!$existing || $this->option('force')) {
                GradingScale::updateOrCreate(
                    ['grade' => $data['grade']],
                    [
                        'min_percentage' => $data['min'],
                        'max_percentage' => $data['max'],
                        'grade_point' => $data['point'],
                        'order' => $data['order'],
                        'is_passing' => $data['is_passing'] ?? true,
                        'is_active' => true,
                    ]
                );
                $created++;
            }
        }
        
        $this->info(" {$created} grading scales initialized");
        $this->newLine();

        // Display summary
        $this->info(' Summary:');
        $this->table(
            ['Category', 'Count'],
            [
                ['System Settings', \App\Models\SystemSetting::count()],
                ['Grading Scales', GradingScale::count()],
            ]
        );

        $this->newLine();
        $this->info('System initialization complete!');

        return self::SUCCESS;
    }
}

