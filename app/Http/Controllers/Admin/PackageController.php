<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PackageController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        $packages = Package::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.packages.index', compact('packages'));
    }

    public function show(Package $package)
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        if ($package->user_id !== $user->id) {
            abort(403);
        }

        return response()->json(['ok' => true, 'data' => $package->data], 200);
    }

    public function destroy(Package $package)
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        if ($package->user_id !== $user->id) {
            abort(403);
        }

        $package->delete();

        return response()->json(['ok' => true, 'message' => 'Package deleted'], 200);
    }

    public function store(Request $request)
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            $raw = $request->input('data');
            if ($raw) {
                try {
                    $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                } catch (\Throwable $e) {
                    return response()->json(['ok' => false, 'error' => 'Invalid JSON payload'], 422);
                }
            }
        }

        if (empty($payload)) {
            return response()->json(['ok' => false, 'error' => 'No JSON payload provided'], 422);
        }

        try {
            $upload = new Package;
            $upload->user_id = Auth::id();
            $upload->filename = $request->input('filename');
            $upload->data = $payload;
            $upload->save();

            return response()->json(['ok' => true, 'id' => $upload->id], 201);
        } catch (\Throwable $e) {
            Log::error('PdfUpload store error: '.$e->getMessage());

            return response()->json(['ok' => false, 'error' => 'Server error'], 500);
        }
    }

    public function apiList(Request $request)
    {
        $user = Auth::user();

        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $uploads = Package::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'filename', 'data', 'created_at']);

        return response()->json(['ok' => true, 'uploads' => $uploads], 200);
    }

    public function exportExcel($id)
    {
        $user = Auth::user();
        $allowedIds = [1, 2, 3, 5, 7, 8];

        if (! ($user && in_array($user->id, $allowedIds, true))) {
            abort(403);
        }

        $upload = Package::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (! $upload) {
            abort(404);
        }

        $data = $upload->data ?? [];
        $packages = [];

        foreach ($data as $idx => $item) {
            if (! is_array($item)) {
                continue;
            }
            $ids = $item['customers'] ?? $item['customers_list'] ?? $item['customer_ids'] ?? [];
            if (! is_array($ids)) {
                $ids = [];
            }
            $packages[] = [
                'id' => $item['id'] ?? $item['service_id'] ?? (string) $idx,
                'name' => $item['name'] ?? $item['title'] ?? '',
                'count' => count($ids),
                'customers' => $ids,
            ];
        }

        usort($packages, function ($a, $b) {
            if (is_numeric($a['id']) && is_numeric($b['id'])) {
                return (int) $a['id'] - (int) $b['id'];
            }

            return strcmp((string) $a['id'], (string) $b['id']);
        });

        $spreadsheet = new Spreadsheet;

        $summary = $spreadsheet->getActiveSheet();
        $summary->setTitle('Resumen');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E40AF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBFDBFE']]],
        ];

        $summary->fromArray(['#', 'ID Paquete', 'Nombre del paquete', 'Total clientes'], null, 'A1');
        $summary->getStyle('A1:D1')->applyFromArray($headerStyle);
        $summary->getColumnDimension('A')->setWidth(6);
        $summary->getColumnDimension('B')->setAutoSize(true);
        $summary->getColumnDimension('C')->setAutoSize(true);
        $summary->getColumnDimension('D')->setWidth(16);

        $rowNum = 2;
        foreach ($packages as $i => $pkg) {
            $summary->fromArray([$i + 1, $pkg['id'], $pkg['name'], $pkg['count']], null, "A{$rowNum}");
            if ($i % 2 === 0) {
                $summary->getStyle("A{$rowNum}:D{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F9FF');
            }
            $rowNum++;
        }

        $totalRow = $rowNum;
        $totalCustomers = array_sum(array_column($packages, 'count'));
        $summary->setCellValue("A{$totalRow}", 'TOTAL');
        $summary->setCellValue("C{$totalRow}", count($packages).' paquetes');
        $summary->setCellValue("D{$totalRow}", $totalCustomers);
        $summary->getStyle("A{$totalRow}:D{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
        ]);

        $detail = new Worksheet($spreadsheet, 'Detalle');
        $spreadsheet->addSheet($detail);

        $detail->fromArray(['#', 'ID Paquete', 'Nombre del paquete', 'ID Cliente'], null, 'A1');
        $detail->getStyle('A1:D1')->applyFromArray($headerStyle);
        $detail->getColumnDimension('A')->setWidth(8);
        $detail->getColumnDimension('B')->setAutoSize(true);
        $detail->getColumnDimension('C')->setAutoSize(true);
        $detail->getColumnDimension('D')->setAutoSize(true);

        $rowNum = 2;
        $lineNum = 1;
        foreach ($packages as $pkg) {
            foreach ($pkg['customers'] as $customerId) {
                $detail->fromArray([$lineNum, $pkg['id'], $pkg['name'], $customerId], null, "A{$rowNum}");
                if ($lineNum % 2 === 0) {
                    $detail->getStyle("A{$rowNum}:D{$rowNum}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F9FF');
                }
                $rowNum++;
                $lineNum++;
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'paquetes_'.Str::slug($upload->filename ?? 'export').'_'.now()->format('Ymd_His').'.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
