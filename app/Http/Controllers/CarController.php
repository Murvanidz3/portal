<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarFile;
use App\Models\User;
use App\Models\Setting;
use App\Services\CarService;
use App\Services\FileUploadService; // Added for file specific actions
use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function __construct(
        protected CarService $carService,
        protected FileUploadService $fileUploadService
    ) {
    }

    /**
     * Display a listing of cars.
     */
    public function index(Request $request)
    {
        $query = Car::with(['user', 'client'])
            ->forUser(auth()->user())
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by dealer (admin only)
        if ($request->filled('dealer_id') && auth()->user()->isAdmin()) {
            $query->where('user_id', $request->dealer_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $cars = $query->paginate(12)->withQueryString();

        // Get dealers for filter (admin only)
        $dealers = auth()->user()->isAdmin()
            ? User::dealers()->get()
            : collect();

        // Get status counts
        $statusCounts = Car::forUser(auth()->user())
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('cars.index', compact('cars', 'dealers', 'statusCounts'));
    }

    /**
     * Show the form for creating a new car.
     */
    public function create()
    {
        $dealers = auth()->user()->isAdmin()
            ? User::dealers()->approved()->get()
            : collect([auth()->user()]);

        $clients = User::clients()->approved()->get();
        $statuses = Car::getStatusOptions();

        return view('cars.create', compact('dealers', 'clients', 'statuses'));
    }

    /**
     * Store a newly created car.
     */
    public function store(StoreCarRequest $request)
    {
        try {
            $car = $this->carService->createCar(
                $request->validated(),
                $request->file('photos', [])
            );

            return redirect()->route('cars.show', $car)
                ->with('success', 'მანქანა წარმატებით დაემატა!');
        } catch (\Exception $e) {
            \Log::error('Car creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'შეცდომა მანქანის დამატებისას: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified car.
     */
    public function show(Car $car)
    {
        $this->authorizeView($car);

        $car->load(['user', 'client', 'files', 'transactions']);

        $auctionPhotos = $car->files()->where('category', 'auction')->where('file_type', 'image')->get();
        $pickupPhotos = $car->files()->where('category', 'pickup')->where('file_type', 'image')->get();
        $warehousePhotos = $car->files()->whereIn('category', ['warehouse', 'port'])->where('file_type', 'image')->get();
        $potiPhotos = $car->files()->whereIn('category', ['poti', 'terminal'])->where('file_type', 'image')->get();

        $canEditCar = auth()->user()->isAdmin() || (auth()->user()->isDealer() && $car->user_id === auth()->id());
        $clients = $canEditCar ? User::clients()->approved()->get() : collect();

        return view('cars.show', compact('car', 'auctionPhotos', 'pickupPhotos', 'warehousePhotos', 'potiPhotos', 'canEditCar', 'clients'));
    }

    /**
     * Show the form for editing the specified car.
     */
    public function edit(Car $car)
    {
        $this->authorizeEdit($car);

        $dealers = auth()->user()->isAdmin()
            ? User::dealers()->approved()->get()
            : collect([auth()->user()]);

        $clients = User::clients()->approved()->get();
        $statuses = Car::getStatusOptions();

        $car->load('files');

        return view('cars.edit', compact('car', 'dealers', 'clients', 'statuses'));
    }

    /**
     * Update the specified car.
     */
    public function update(UpdateCarRequest $request, Car $car)
    {
        $this->authorizeEdit($car);

        try {
            $this->carService->updateCar(
                $car,
                $request->validated(),
                $request->file('photos', [])
            );

            return redirect()->route('cars.show', $car)
                ->with('success', 'მანქანა წარმატებით განახლდა!');
        } catch (\Exception $e) {
            \Log::error('Car update failed', ['car_id' => $car->id, 'error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'შეცდომა განახლებისას: ' . $e->getMessage());
        }
    }

    /**
     * Update car status via AJAX
     */
    public function updateStatus(Request $request, Car $car)
    {
        $this->authorizeEdit($car);

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Car::getStatuses())),
        ]);

        $this->carService->updateStatus($car, $request->status);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'სტატუსი განახლდა!',
                'status' => $car->status,
                'status_label' => $car->status_label,
            ]);
        }

        return redirect()->back()->with('success', 'სტატუსი წარმატებით განახლდა!');
    }

    /**
     * Update car recipient (client) via AJAX from show page.
     */
    public function updateRecipient(Request $request, Car $car)
    {
        $this->authorizeEdit($car);

        $validated = $request->validate([
            'client_name' => 'nullable|string|max:100',
            'client_phone' => 'nullable|string|max:50',
            'client_id_number' => 'nullable|string|max:50',
            'client_user_id' => 'nullable|exists:users,id',
        ]);

        $this->carService->updateRecipient($car, $validated);

        return response()->json([
            'success' => true,
            'message' => 'მიმღების მონაცემები განახლდა!',
            'recipient' => [
                'name' => $car->getClientDisplayName(),
                'phone' => $car->getClientPhone(),
                'id_number' => $car->client_id_number,
            ],
        ]);
    }

    /**
     * Remove the specified car.
     */
    public function destroy(Car $car)
    {
        $this->authorizeEdit($car);

        $this->carService->deleteCar($car);

        return redirect()->route('cars.index')
            ->with('success', 'მანქანა წარმატებით წაიშალა!');
    }

    /**
     * Delete a car file
     */
    public function deleteFile(Car $car, CarFile $file)
    {
        $this->authorizeEdit($car);

        if ($file->car_id !== $car->id) {
            abort(403);
        }

        $this->fileUploadService->deleteCarFile($file);

        return response()->json(['success' => true, 'message' => 'ფოტო წაიშალა!']);
    }

    /**
     * Set main photo for car
     */
    public function setMainPhoto(Car $car, CarFile $file)
    {
        $this->authorizeEdit($car);

        if ($file->car_id !== $car->id) {
            abort(403);
        }

        $car->main_photo = $file->file_path;
        $car->save();

        return response()->json(['success' => true, 'message' => 'მთავარი ფოტო განახლდა!']);
    }

    /**
     * Bulk delete files for car
     */
    public function bulkDeleteFiles(Request $request, Car $car)
    {
        $this->authorizeEdit($car);

        $fileIds = $request->input('file_ids', []);

        if (empty($fileIds)) {
            return response()->json(['success' => false, 'message' => 'ფოტოები არ არის არჩეული']);
        }

        $files = CarFile::where('car_id', $car->id)
            ->whereIn('id', $fileIds)
            ->get();

        $deletedCount = 0;
        foreach ($files as $file) {
            if ($this->fileUploadService->deleteCarFile($file)) {
                $deletedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} ფოტო წაიშალა!"
        ]);
    }

    /**
     * Check if user can view the car
     */
    protected function authorizeView(Car $car): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isDealer() && $car->user_id === $user->id) {
            return;
        }

        if ($user->isClient() && $car->client_user_id === $user->id) {
            return;
        }

        abort(403, 'არ გაქვთ წვდომა ამ მანქანაზე.');
    }

    /**
     * Check if user can edit the car
     */
    protected function authorizeEdit(Car $car): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isDealer() && $car->user_id === $user->id) {
            return;
        }

        abort(403, 'არ გაქვთ უფლება შეცვალოთ ეს მანქანა.');
    }

    /**
     * Generate invoice for car
     */
    public function invoice(Car $car, string $type)
    {
        $this->authorizeView($car);

        // Validate invoice type
        if (!in_array($type, ['vehicle', 'shipping'])) {
            abort(404, 'უცნობი ინვოისის ტიპი');
        }

        $car->load(['user', 'client']);

        $invoiceItems = [];
        $totalAmount = 0;
        $paymentPurpose = '';

        if ($type === 'vehicle') {
            // Vehicle invoice
            $invoiceNo = 'INV-CAR-' . $car->id;
            $paymentPurpose = 'ავტომობილის გადასახადი VIN: ' . $car->vin;

            if ($car->vehicle_cost > 0) {
                $invoiceItems[] = [
                    'desc' => "ავტომობილის ღირებულება / Vehicle Cost\n" . $car->make_model . ($car->year ? ' (' . $car->year . ')' : '') . "\nVIN: " . $car->vin,
                    'amount' => $car->vehicle_cost
                ];
            }
        } elseif ($type === 'shipping') {
            // Shipping invoice
            $invoiceNo = 'INV-SHIP-' . $car->id;
            $paymentPurpose = 'ტრანსპორტირება VIN: ' . $car->vin;

            if ($car->shipping_cost > 0) {
                $invoiceItems[] = [
                    'desc' => 'ტრანსპორტირება / Shipping Cost',
                    'amount' => $car->shipping_cost
                ];
            }

            if ($car->additional_cost > 0) {
                $invoiceItems[] = [
                    'desc' => 'დამატებითი ხარჯები / Additional Fees',
                    'amount' => $car->additional_cost
                ];
            }
        }

        // Calculate total
        foreach ($invoiceItems as $item) {
            $totalAmount += $item['amount'];
        }

        // Get company settings
        $companyName = Setting::get('company_name', 'ONECAR LLC');
        $companyAddress = Setting::get('company_address', 'Tbilisi, Georgia');
        $companyPhone = Setting::get('company_phone', '+995 555 123 456');
        $companyEmail = Setting::get('company_email', 'info@onecar.ge');
        $companyLogo = Setting::get('site_logo_dark', asset('favicon.ico'));

        // Bank details
        $bankName = Setting::get('bank_name', 'Bank of Georgia');
        $bankRecipient = Setting::get('bank_recipient', 'ლუკა მურვანიძე');
        $bankIban = Setting::get('bank_iban', 'GE37BG0000000160921689');
        $bankSwift = Setting::get('bank_swift', 'BAGAGE22');

        // Determine bill to
        $user = auth()->user();
        $billTo = null;
        if ($user->isDealer()) {
            $billTo = [
                'name' => $user->full_name ?? $user->username,
                'id' => $user->username,
                'type' => 'dealer'
            ];
        } else {
            if ($car->client) {
                $billTo = [
                    'name' => $car->client->full_name ?? $car->client_name,
                    'id' => $car->client->id_number ?? $car->client_id_number,
                    'phone' => $car->client->phone ?? $car->client_phone,
                    'type' => 'client'
                ];
            } elseif ($car->client_name) {
                $billTo = [
                    'name' => $car->client_name,
                    'id' => $car->client_id_number,
                    'phone' => $car->client_phone,
                    'type' => 'client'
                ];
            }
        }

        return view('cars.invoice', compact(
            'car',
            'type',
            'invoiceNo',
            'invoiceItems',
            'totalAmount',
            'paymentPurpose',
            'companyName',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyLogo',
            'bankName',
            'bankRecipient',
            'bankIban',
            'bankSwift',
            'billTo'
        ));
    }
}
