<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('productions')
            ->where('status', 'Rejected')
            ->update(['status' => 'Returned']);
    }

    public function down(): void
    {
        DB::table('productions')
            ->where('status', 'Returned')
            ->update(['status' => 'Rejected']);
    }
};
