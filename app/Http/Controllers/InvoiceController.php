<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Invoice;
use App\Services\InvoiceDisplaySettings;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index()
    {
        $query = Invoice::orderBy('created_at', 'desc');

        // Filter by user if not admin
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        $invoices = $query->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(Request $request)
    {
        // Get cars for selection
        $query = Car::orderBy('created_at', 'desc');
        
        // Filter by user if not admin
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        
        $cars = $query->get(['id', 'vin', 'make_model', 'year', 'client_name', 'client_id_number', 'vehicle_cost', 'shipping_cost']);
        
        // Get car and type from query parameters
        $selectedCarId = $request->get('car_id');
        $selectedType = $request->get('type'); // 'vehicle' or 'shipping'
        
        // Validate car access if car_id is provided
        $selectedCar = null;
        if ($selectedCarId) {
            $selectedCar = Car::find($selectedCarId);
            if ($selectedCar) {
                // Check authorization
                if (!auth()->user()->isAdmin() && $selectedCar->user_id !== auth()->id()) {
                    abort(403);
                }
            }
        }
        
        return view('invoices.create', compact('cars', 'selectedCarId', 'selectedType', 'selectedCar'));
    }
    
    /**
     * Get car data for invoice
     */
    public function getCarData(Car $car)
    {
        // Check authorization
        if (!auth()->user()->isAdmin() && $car->user_id !== auth()->id()) {
            abort(403);
        }
        
        return response()->json([
            'vin' => $car->vin,
            'make_model' => $car->make_model,
            'year' => $car->year,
            'client_name' => $car->getClientDisplayName(),
            'client_id_number' => $car->client_id_number,
            'personal_id' => $car->client_id_number,
            'vehicle_cost' => $car->vehicle_cost,
            'shipping_cost' => $car->shipping_cost,
        ]);
    }
    
    /**
     * Generate invoice directly from car
     */
    public function generateFromCar(Car $car, string $type)
    {
        // Check authorization
        if (!auth()->user()->isAdmin() && $car->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Validate invoice type
        if (!in_array($type, ['vehicle', 'shipping'])) {
            abort(404, 'უცნობი ინვოისის ტიპი');
        }
        
        // Calculate total based on type
        $totalAmount = 0;
        $vehicleCost = null;
        $shippingCost = null;
        
        if ($type === 'vehicle') {
            $totalAmount = $car->vehicle_cost ?? 0;
            $vehicleCost = $car->vehicle_cost;
        } elseif ($type === 'shipping') {
            $totalAmount = ($car->shipping_cost ?? 0) + ($car->additional_cost ?? 0);
            $shippingCost = $car->shipping_cost;
        }
        
        // Generate sequential invoice number: INV-CR00101, INV-CR00102, ...
        $invoiceNumber = Invoice::generateNextNumber();
        
        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'type' => $type,
            'personal_id' => $car->client_id_number,
            'vin' => $car->vin,
            'make_model' => $car->make_model,
            'year' => $car->year,
            'client_name' => $car->getClientDisplayName(),
            'vehicle_cost' => $vehicleCost,
            'shipping_cost' => $shippingCost,
            'total_amount' => $totalAmount,
            'notes' => null,
            'user_id' => auth()->id(),
        ]);
        
        // Return invoice view directly (blank page in new tab)
        return $this->show($invoice);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:vehicle,shipping',
            'personal_id' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:17',
            'make_model' => 'nullable|string|max:100',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'client_name' => 'nullable|string|max:100',
            'vehicle_cost' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Calculate total based on type
        $totalAmount = 0;
        if ($validated['type'] === 'vehicle') {
            $totalAmount = $validated['vehicle_cost'] ?? 0;
        } elseif ($validated['type'] === 'shipping') {
            $totalAmount = $validated['shipping_cost'] ?? 0;
        }

        // Generate sequential invoice number: INV-CR00101, INV-CR00102, ...
        $invoiceNumber = Invoice::generateNextNumber();

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'type' => $validated['type'],
            'personal_id' => $validated['personal_id'] ?? null,
            'vin' => $validated['vin'] ?? null,
            'make_model' => $validated['make_model'] ?? null,
            'year' => $validated['year'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'vehicle_cost' => $validated['vehicle_cost'] ?? null,
            'shipping_cost' => $validated['shipping_cost'] ?? null,
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        // If AJAX request, return JSON with redirect URL
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'redirect' => route('invoices.show', $invoice),
                'message' => 'ინვოისი წარმატებით შეიქმნა!'
            ]);
        }
        
        // For regular requests, redirect normally
        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'ინვოისი წარმატებით შეიქმნა!');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        // Check authorization
        if (!auth()->user()->isAdmin() && $invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $invoiceHeaderTitle = InvoiceDisplaySettings::headerTitle();
        $companyName = InvoiceDisplaySettings::companyName();
        $companyExtraHtml = InvoiceDisplaySettings::companyExtraHtml();
        $companyAddress = InvoiceDisplaySettings::companyAddress();
        $companyPhone = InvoiceDisplaySettings::companyPhone();
        $companyEmail = InvoiceDisplaySettings::companyEmail();
        $companyLogo = InvoiceDisplaySettings::companyLogo();

        $bankName = InvoiceDisplaySettings::bankName();
        $bankRecipient = InvoiceDisplaySettings::bankRecipient();
        $bankIban = InvoiceDisplaySettings::bankIban();
        $bankSwift = InvoiceDisplaySettings::bankSwift();
        $bankTitle = InvoiceDisplaySettings::bankTitle();
        $bankLblBank = InvoiceDisplaySettings::bankLblBank();
        $bankLblRecipient = InvoiceDisplaySettings::bankLblRecipient();
        $bankLblIban = InvoiceDisplaySettings::bankLblIban();
        $bankLblSwift = InvoiceDisplaySettings::bankLblSwift();

        $labelBillTo = InvoiceDisplaySettings::labelBillTo();
        $labelPurpose = InvoiceDisplaySettings::labelPurpose();
        $tableColDesc = InvoiceDisplaySettings::tableColDesc();
        $tableColAmount = InvoiceDisplaySettings::tableColAmount();
        $tableTotal = InvoiceDisplaySettings::tableTotal();
        $badgeText = InvoiceDisplaySettings::badgeText();
        $footerText = InvoiceDisplaySettings::footerText();

        // Prepare invoice items (VIN ყოველთვის ინვოისის მონაცემიდან)
        $invoiceItems = [];
        $paymentPurpose = '';

        if ($invoice->type === 'vehicle') {
            $paymentPurpose = InvoiceDisplaySettings::buildPaymentPurpose('vehicle', $invoice->vin);

            if ($invoice->vehicle_cost > 0) {
                $invoiceItems[] = [
                    'desc' => InvoiceDisplaySettings::buildLineDescription(
                        InvoiceDisplaySettings::lineTitleVehicle(),
                        $invoice->make_model,
                        $invoice->year,
                        $invoice->vin
                    ),
                    'amount' => $invoice->vehicle_cost,
                ];
            }
        } elseif ($invoice->type === 'shipping') {
            $paymentPurpose = InvoiceDisplaySettings::buildPaymentPurpose('shipping', $invoice->vin);

            if ($invoice->total_amount > 0) {
                $invoiceItems[] = [
                    'desc' => InvoiceDisplaySettings::buildLineDescription(
                        InvoiceDisplaySettings::lineTitleShipping(),
                        $invoice->make_model,
                        $invoice->year,
                        $invoice->vin
                    ),
                    'amount' => $invoice->total_amount,
                ];
            }
        }

        // Determine bill to
        $billTo = null;
        if ($invoice->client_name) {
            $billTo = [
                'name' => $invoice->client_name,
                'id' => $invoice->personal_id,
                'type' => 'client'
            ];
        } elseif (auth()->user()->isDealer()) {
            $billTo = [
                'name' => auth()->user()->full_name ?? auth()->user()->username,
                'id' => auth()->user()->username,
                'type' => 'dealer'
            ];
        }

        return view('invoices.show', compact(
            'invoice',
            'invoiceItems',
            'paymentPurpose',
            'invoiceHeaderTitle',
            'companyName',
            'companyExtraHtml',
            'companyAddress',
            'companyPhone',
            'companyEmail',
            'companyLogo',
            'bankName',
            'bankRecipient',
            'bankIban',
            'bankSwift',
            'bankTitle',
            'bankLblBank',
            'bankLblRecipient',
            'bankLblIban',
            'bankLblSwift',
            'labelBillTo',
            'labelPurpose',
            'tableColDesc',
            'tableColAmount',
            'tableTotal',
            'badgeText',
            'footerText',
            'billTo'
        ));
    }

    /**
     * Remove the specified invoice.
     */
    public function destroy(Invoice $invoice)
    {
        // Check authorization
        if (!auth()->user()->isAdmin() && $invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'ინვოისი წარმატებით წაიშალა!');
    }
}
