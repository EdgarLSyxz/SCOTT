<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransponderSwitchesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ModulatorExportController extends Controller
{
    public function export(Request $request)
    {
        $fileName = 'transponder-switches-'.date('Ymd_His').'.xlsx';

        return Excel::download(new TransponderSwitchesExport, $fileName);
    }
}
