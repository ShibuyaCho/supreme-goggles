<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportTemplatesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $templates = ReportTemplate::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->paginate(50);
        return response()->json([
            'templates' => $templates->items(),
            'meta' => [
                'current_page' => $templates->currentPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
                'last_page' => $templates->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'required|string|in:sales,inventory,customers,products,metrc,employees,daily_summary,tax_report,analytics,compliance',
            'format' => 'nullable|string|in:pdf,excel,csv',
            'include_charts' => 'sometimes|boolean',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'paper_size' => 'nullable|string|in:a4,letter,legal',
            'config' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template = ReportTemplate::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'report_type' => $request->report_type,
            'format' => $request->format ?? 'pdf',
            'include_charts' => (bool) $request->boolean('include_charts', false),
            'orientation' => $request->orientation ?? 'portrait',
            'paper_size' => $request->paper_size ?? 'a4',
            'config' => $request->config,
        ]);

        return response()->json(['message' => 'Template saved', 'template' => $template], 201);
    }

    public function show(Request $request, ReportTemplate $template)
    {
        $this->authorizeAccess($request->user(), $template);
        return response()->json(['template' => $template]);
    }

    public function update(Request $request, ReportTemplate $template)
    {
        $this->authorizeAccess($request->user(), $template);
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'report_type' => 'sometimes|required|string|in:sales,inventory,customers,products,metrc,employees,daily_summary,tax_report,analytics,compliance',
            'format' => 'nullable|string|in:pdf,excel,csv',
            'include_charts' => 'sometimes|boolean',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'paper_size' => 'nullable|string|in:a4,letter,legal',
            'config' => 'sometimes|required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $template->update($request->only([
            'name','description','report_type','format','include_charts','orientation','paper_size','config'
        ]));

        return response()->json(['message' => 'Template updated', 'template' => $template->fresh()]);
    }

    public function destroy(Request $request, ReportTemplate $template)
    {
        $this->authorizeAccess($request->user(), $template);
        $template->delete();
        return response()->json(['message' => 'Template deleted']);
    }

    private function authorizeAccess($user, ReportTemplate $template): void
    {
        if (!$user) abort(401);
        if ($template->user_id !== $user->id && !$user->isAdmin() && !$user->isManager()) {
            abort(403, 'Not authorized');
        }
    }
}
