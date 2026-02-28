<?php

namespace App\Http\Controllers\GodMode;

use App\Http\Controllers\Controller;
use App\Models\GodModeStyle;
use App\Services\GodModeService;
use Illuminate\Http\Request;

class StyleController extends Controller
{
    protected GodModeService $godModeService;

    public function __construct(GodModeService $godModeService)
    {
        $this->godModeService = $godModeService;
    }

    /**
     * Show styles management page.
     */
    public function index()
    {
        $styles = $this->godModeService->getGroupedStyles();

        // Group labels in Georgian
        $groupLabels = [
            'branding' => 'ბრენდინგი',
            'colors' => 'მთავარი ფერები',
            'buttons' => 'ღილაკები',
            'layout' => 'განლაგება',
            'status' => 'სტატუსის ფერები',
        ];

        return view('god-mode.styles', compact('styles', 'groupLabels'));
    }

    /**
     * Update a color style via AJAX.
     */
    public function updateColor(Request $request, GodModeStyle $style)
    {
        $validated = $request->validate([
            'value' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        $this->godModeService->updateStyle($style->id, $validated['value']);

        return response()->json([
            'success' => true,
            'message' => 'ფერი განახლდა!',
            'css' => $this->godModeService->getCssVariables(),
        ]);
    }

    /**
     * Update a text style via AJAX.
     */
    public function updateText(Request $request, GodModeStyle $style)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $this->godModeService->updateStyle($style->id, $validated['value']);

        return response()->json([
            'success' => true,
            'message' => 'ტექსტი განახლდა!',
        ]);
    }

    /**
     * Upload a logo/image.
     */
    public function uploadImage(Request $request, GodModeStyle $style)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg,gif,svg,ico,webp|max:2048',
        ]);

        $this->godModeService->uploadLogo($style->id, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'სურათი ატვირთულია!',
            'url' => $style->fresh()->style_value,
        ]);
    }

    /**
     * Reset a style to default.
     */
    public function resetToDefault(GodModeStyle $style)
    {
        $this->godModeService->updateStyle($style->id, $style->default_value);

        return response()->json([
            'success' => true,
            'message' => 'სტილი აღდგენილია!',
            'value' => $style->default_value,
            'css' => $this->godModeService->getCssVariables(),
        ]);
    }

    /**
     * Reset all styles to defaults.
     */
    public function resetAll()
    {
        $styles = GodModeStyle::all();

        foreach ($styles as $style) {
            $style->update(['style_value' => $style->default_value]);
        }

        GodModeStyle::clearCache();

        $this->godModeService->logAction('styles.reset_all');

        return response()->json([
            'success' => true,
            'message' => 'ყველა სტილი აღდგენილია!',
            'css' => $this->godModeService->getCssVariables(),
        ]);
    }

    /**
     * Get current CSS variables (for live preview).
     */
    public function getCss()
    {
        return response($this->godModeService->getCssVariables())
            ->header('Content-Type', 'text/css');
    }
}
