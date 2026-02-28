<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Display SMS management page.
     */
    public function index(Request $request)
    {
        // Get templates
        $templates = SmsTemplate::orderBy('id')->get();

        // Get SMS logs
        $logsQuery = SmsLog::orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $logsQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $logsQuery->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $logs = $logsQuery->paginate(20)->withQueryString();

        // Stats
        $balance = $this->smsService->getBalance();
        
        $stats = [
            'total_sent' => SmsLog::sent()->count(),
            'total_failed' => SmsLog::failed()->count(),
            'today' => SmsLog::whereDate('created_at', today())->count(),
            'balance' => $balance,
        ];

        return view('sms.index', compact('templates', 'logs', 'stats'));
    }

    /**
     * Update SMS template.
     */
    public function updateTemplate(Request $request, SmsTemplate $template)
    {
        $validated = $request->validate([
            'template_text' => 'required|string|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        $template->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'შაბლონი განახლდა!'
            ]);
        }

        return redirect()->back()->with('success', 'შაბლონი განახლდა!');
    }

    /**
     * Send SMS manually.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:50',
            'message' => 'required|string|max:500',
        ]);

        $result = $this->smsService->send(
            $validated['phone'],
            $validated['message']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result,
                'message' => $result ? 'SMS გაიგზავნა!' : 'SMS გაგზავნა ვერ მოხერხდა!'
            ]);
        }

        return redirect()->back()->with(
            $result ? 'success' : 'error',
            $result ? 'SMS გაიგზავნა!' : 'SMS გაგზავნა ვერ მოხერხდა!'
        );
    }

    /**
     * Send SMS to user.
     */
    public function sendToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (empty($user->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'მომხმარებელს არ აქვს ტელეფონის ნომერი!'
            ], 400);
        }

        $result = $this->smsService->send($user->phone, $validated['message']);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'SMS გაიგზავნა!' : 'SMS გაგზავნა ვერ მოხერხდა!'
        ]);
    }

    /**
     * Send SMS for car status change.
     */
    public function sendForCar(Request $request, Car $car)
    {
        $validated = $request->validate([
            'template_key' => 'nullable|string',
            'message' => 'nullable|string|max:500',
        ]);

        // Get recipient phone
        $phone = $car->getClientPhone();
        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'კლიენტს არ აქვს ტელეფონის ნომერი!'
            ], 400);
        }

        // Get message from template or use custom
        if (!empty($validated['message'])) {
            $message = $validated['message'];
        } elseif (!empty($validated['template_key'])) {
            $template = SmsTemplate::getByStatus($validated['template_key']);
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'შაბლონი ვერ მოიძებნა!'
                ], 400);
            }
            $message = $template->parseForCar($car);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'მიუთითეთ შეტყობინება ან შაბლონი!'
            ], 400);
        }

        $result = $this->smsService->send($phone, $message);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'SMS გაიგზავნა!' : 'SMS გაგზავნა ვერ მოხერხდა!'
        ]);
    }

    /**
     * Send bulk SMS to multiple users.
     */
    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'message' => 'required|string|max:500',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $result = $this->smsService->send($user->phone, $validated['message']);
            $result ? $sent++ : $failed++;
        }

        return response()->json([
            'success' => true,
            'sent' => $sent,
            'failed' => $failed,
            'message' => "გაიგზავნა: {$sent}, ვერ გაიგზავნა: {$failed}"
        ]);
    }

    /**
     * Delete SMS log entry.
     */
    public function deleteLog(SmsLog $log)
    {
        $log->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ჩანაწერი წაიშალა!'
            ]);
        }

        return redirect()->back()->with('success', 'ჩანაწერი წაიშალა!');
    }

    /**
     * Clear old SMS logs.
     */
    public function clearOldLogs(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $count = SmsLog::where('created_at', '<', now()->subDays($validated['days']))->delete();

        return response()->json([
            'success' => true,
            'deleted' => $count,
            'message' => "{$count} ჩანაწერი წაიშალა!"
        ]);
    }

    /**
     * Clear all SMS logs.
     */
    public function clearAllLogs(Request $request)
    {
        $count = SmsLog::count();
        SmsLog::truncate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'deleted' => $count,
                'message' => "{$count} ჩანაწერი წაიშალა!"
            ]);
        }

        return redirect()->back()->with('success', "{$count} ჩანაწერი წაიშალა!");
    }
}
