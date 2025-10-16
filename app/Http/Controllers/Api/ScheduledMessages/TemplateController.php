<?php

namespace App\Http\Controllers\Api\ScheduledMessages;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Template::with('listings')->get();
        return $templates;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'message' => 'required',
            'listings' => 'required|array',
            'action' => 'required',
            'when_to_send' => 'required_if:action,booking_confirmed',
            'day' => 'required_if:action,check_in,check_out',
            'time' => 'required_if:action,check_in,check_out',
            'standard_check_in_time' => 'required',
            'standard_check_out_time' => 'required',
        ]);

        $data['user_id'] = auth()->id();
        $data['is_active'] = true;

        $template = Template::create($data);

        $template->listings()->sync($data['listings']);

        return $template;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'message' => 'required',
            'listings' => 'required|array',
            'action' => 'required',
            'when_to_send' => 'required_if:action,booking_confirmed',
            'day' => 'required_if:action,check_in,check_out',
            'time' => 'required_if:action,check_in,check_out',
            'standard_check_in_time' => 'required',
            'standard_check_out_time' => 'required',
        ]);

        $template = Template::findOrFail($id);

        $data['user_id'] = auth()->id();

        $template->update($data);
        $template->listings()->sync($data['listings']);

        return $template;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $template = Template::findOrFail($id);

        $template->listings()->detach();

        $template->delete();

        return response()->json(['message' => 'Template deleted successfully']);
    }

}
