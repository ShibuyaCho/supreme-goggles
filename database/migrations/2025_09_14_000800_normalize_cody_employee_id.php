<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        try {
            $rows = DB::table('employees')->select('id','employee_id','email','first_name','last_name')->get();
            $used = [];
            foreach ($rows as $r) {
                if (!empty($r->employee_id) && preg_match('/(\d+)/', (string)$r->employee_id, $m)) {
                    $n = intval(ltrim($m[1], '0') === '' ? '0' : ltrim($m[1], '0'));
                    if ($n > 0) $used[$n] = true;
                }
            }
            $next = 1;
            while (isset($used[$next])) $next++;
            $targetId = 'Emp' . str_pad((string)$next, $next < 100 ? 2 : strlen((string)$next), '0', STR_PAD_LEFT);

            $cody = DB::table('employees')->whereIn(DB::raw('lower(email)'), ['thccodys@gmail.com','smith.cody@yahoo.com'])->first();
            if (!$cody) {
                $cody = DB::table('employees')->where(DB::raw('lower(first_name)'), 'cody')->whereIn(DB::raw('lower(last_name)'), ['smith','admin'])->first();
            }
            if ($cody) {
                $emp01Taken = DB::table('employees')->where('employee_id','Emp01')->where('id','<>',$cody->id)->first();
                if ($emp01Taken) {
                    DB::table('employees')->where('id', $emp01Taken->id)->update(['employee_id' => $targetId]);
                }
                DB::table('employees')->where('id', $cody->id)->update(['employee_id' => 'Emp01']);
            }
        } catch (\Throwable $e) {
            // swallow errors in migration to avoid breaking deploy
        }
    }

    public function down(): void
    {
        // no-op
    }
};
