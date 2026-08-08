<?php

use App\Models\Branch;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            Institution::query()->chunkById(50, function ($institutions) {
                foreach ($institutions as $institution) {
                    // Skip if this institution somehow already has a main branch
                    // (e.g. migration re-run after partial failure)
                    $mainBranch = Branch::where('institution_id', $institution->id)
                        ->where('is_main', true)
                        ->first();

                    if (! $mainBranch) {
                        $mainBranch = Branch::create([
                            'institution_id' => $institution->id,
                            'name'           => 'Main Branch',
                            'code'           => 'MAIN',
                            'is_main'        => true,
                            'is_active'      => true,
                        ]);
                    }

                    // Assign every existing user of this institution to the main branch
                    // where they don't already have a branch set
                    User::where('institution_id', $institution->id)
                        ->whereNull('branch_id')
                        ->update(['branch_id' => $mainBranch->id]);
                }
            });
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: does not delete branches or unset
        // user branch_id on rollback, since other data may already depend on it.
        // If you need a true rollback, handle it manually.
    }
};