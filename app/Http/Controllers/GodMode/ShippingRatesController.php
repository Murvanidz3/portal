<?php

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\GodModeAuditLog;
use App\Models\User;
use App\Models\UserShippingFile;
use App\Models\UserShippingRate;
use App\Services\ShippingRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingRatesController extends Controller
{
    protected ShippingRateService $shippingRateService;

    public function __construct(ShippingRateService $shippingRateService)
    {
        $this->shippingRateService = $shippingRateService;
    }

    /**
     * Display shipping rates management page.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $usersQuery = User::query()
            ->with([
                'shippingFiles' => function ($q) {
                    $q->where('is_active', true);
                }
            ])
            ->withCount([
                'shippingRates as active_rates_count' => function ($q) {
                    $q->whereHas('shippingFile', function ($q) {
                        $q->where('is_active', true);
                    });
                }
            ]);

        if ($search) {
            $usersQuery->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%");
            });
        }

        $users = $usersQuery->orderBy('full_name')->paginate(20);

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'users_with_rates' => UserShippingFile::distinct('user_id')->where('is_active', true)->count('user_id'),
            'total_rates' => UserShippingRate::whereHas('shippingFile', fn($q) => $q->where('is_active', true))->count(),
            'total_files' => UserShippingFile::where('is_active', true)->count(),
        ];

        return view('god-mode.shipping-rates.index', compact('users', 'stats', 'search'));
    }

    /**
     * Show user-specific shipping rates.
     */
    public function show(User $user)
    {
        $activeFile = $this->shippingRateService->getActiveFile($user->id);
        $rates = $this->shippingRateService->getUserRates($user->id);

        $fileHistory = UserShippingFile::forUser($user->id)
            ->with('uploadedByUser')
            ->orderByDesc('uploaded_at')
            ->limit(10)
            ->get();

        return view('god-mode.shipping-rates.show', compact('user', 'activeFile', 'rates', 'fileHistory'));
    }

    /**
     * Upload Excel file for a user.
     */
    public function upload(Request $request, User $user)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt|max:5120', // Max 5MB, CSV only
        ], [
            'excel_file.required' => 'CSV ფაილი აუცილებელია.',
            'excel_file.file' => 'ატვირთეთ ვალიდური ფაილი.',
            'excel_file.mimes' => 'დასაშვებია მხოლოდ CSV ფორმატი.',
            'excel_file.max' => 'ფაილის ზომა არ უნდა აღემატებოდეს 5MB-ს.',
        ]);

        $superAdmin = Auth::guard('god')->user();

        // Use a system user ID for uploaded_by since super admin is separate
        // We'll use the first admin user or create a special record
        $uploadedBy = User::where('role', 'admin')->first()?->id ?? 1;

        $result = $this->shippingRateService->uploadAndProcess(
            $user,
            $request->file('excel_file'),
            $uploadedBy
        );

        // Log the action
        GodModeAuditLog::log(
            $superAdmin->id,
            $result['success'] ? 'shipping_rates.uploaded' : 'shipping_rates.upload_failed',
            User::class,
            $user->id,
            null,
            [
                'original_name' => $request->file('excel_file')->getClientOriginalName(),
                'rates_count' => $result['rates_count'] ?? 0,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect()
                ->route('god.shipping-rates.show', $user)
                ->with('success', $result['message'] . ' (ტარიფები: ' . $result['rates_count'] . ')');
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Delete shipping file.
     */
    public function delete(Request $request, UserShippingFile $file)
    {
        $user = $file->user;
        $superAdmin = Auth::guard('god')->user();

        $oldData = [
            'file_path' => $file->file_path,
            'original_name' => $file->original_name,
            'rates_count' => $file->rates()->count(),
        ];

        $deleted = $this->shippingRateService->deleteFile($file->id);

        // Log the action
        GodModeAuditLog::log(
            $superAdmin->id,
            'shipping_rates.deleted',
            User::class,
            $user->id,
            $oldData,
            null
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $deleted,
                'message' => $deleted ? 'ფაილი წაიშალა.' : 'წაშლა ვერ მოხერხდა.',
            ]);
        }

        if ($deleted) {
            return redirect()
                ->route('god.shipping-rates.show', $user)
                ->with('success', 'ფაილი და ტარიფები წაიშალა.');
        }

        return back()->with('error', 'წაშლა ვერ მოხერხდა.');
    }

    /**
     * Activate a specific file for user.
     */
    public function activate(Request $request, UserShippingFile $file)
    {
        $superAdmin = Auth::guard('god')->user();

        $file->activateExclusive();

        GodModeAuditLog::log(
            $superAdmin->id,
            'shipping_rates.activated',
            UserShippingFile::class,
            $file->id,
            null,
            ['file_name' => $file->original_name]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ფაილი გააქტიურდა.',
            ]);
        }

        return back()->with('success', 'ფაილი გააქტიურდა: ' . $file->original_name);
    }

    /**
     * Search locations in user's rates.
     */
    public function searchLocations(Request $request, User $user)
    {
        $query = $request->get('q', '');

        $results = $this->shippingRateService->searchLocation($user->id, $query);

        return response()->json($results);
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate()
    {
        $templatePath = resource_path('templates/shipping_rates_template.csv');

        // If template doesn't exist, create it
        if (!file_exists($templatePath)) {
            $this->createTemplate($templatePath);
        }

        return response()->download($templatePath, 'shipping_rates_template.csv');
    }

    /**
     * Create sample CSV template file.
     */
    protected function createTemplate(string $path): void
    {
        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($path, 'w');

        // Headers
        fputcsv($handle, ['COPART LOCATIONS', 'COPART LOCATIONS - PRICES', 'IAAI LOCATIONS', 'IAAI LOCATIONS - PRICES']);

        // Sample data
        $sampleData = [
            ['CA - LOS ANGELES (Standard)', 1500, 'CA - LOS ANGELES (Standard)', 6000],
            ['CA - SAN DIEGO (Standard)', 1600, 'CA - SAN DIEGO (Standard)', 6100],
            ['TX - HOUSTON (Standard)', 1400, 'TX - HOUSTON (Standard)', 5900],
            ['FL - MIAMI (Standard)', 1700, 'FL - MIAMI (Standard)', 6200],
        ];

        foreach ($sampleData as $data) {
            fputcsv($handle, $data);
        }

        fclose($handle);
    }
}

