<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('sort_order')->orderBy('name')->get()
            ->map(function ($c) {
                $c->lead_count = Lead::where('course', $c->name)->count();
                return $c;
            });
        return view('courses.index', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:191', Rule::unique('courses', 'name')],
            'is_active' => ['nullable', 'boolean'],
        ]);
        Course::create([
            'name'      => $data['name'],
            'is_active' => $request->boolean('is_active', true),
            'sort_order'=> Course::max('sort_order') + 1,
        ]);
        return back()->with('success', 'Course added.');
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:191', Rule::unique('courses', 'name')->ignore($course->id)],
            'is_active' => ['nullable', 'boolean'],
            'sort_order'=> ['nullable', 'integer', 'min:0'],
        ]);
        $oldName = $course->name;
        $course->fill([
            'name'       => $data['name'],
            'is_active'  => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? $course->sort_order,
        ])->save();

        // If the course was renamed, update all leads that reference it
        if ($oldName !== $course->name) {
            Lead::where('course', $oldName)->update(['course' => $course->name]);
        }

        return back()->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $inUse = Lead::where('course', $course->name)->count();
        if ($inUse > 0) {
            $course->is_active = false;
            $course->save();
            return back()->with('warning', "Course is used by $inUse leads — marked inactive instead of deleting.");
        }
        $course->delete();
        return back()->with('success', 'Course deleted.');
    }
}
