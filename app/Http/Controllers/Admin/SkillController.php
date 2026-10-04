<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillRequest;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SkillController extends Controller
{
    private const DIRECTORY = 'skills-logo';

    public function index(): View
    {
        return view('Admin.skills.index', [
            'skills' => Skill::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('Admin.skills.form', [
            'categories' => Skill::CATEGORIES,
        ]);
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        $skill = new Skill;
        $skill->fill($this->payload($request));
        $skill->sort_order = (int) ($request->input('sort_order') ?? 0);
        $skill->save();

        if ($request->hasFile('image')) {
            $skill->image = $request->file('image')->store(self::DIRECTORY, 'public');
            $skill->save();
        }

        return $this->backToIndex('Data saved successfully.');
    }

    public function edit(Skill $skill): View
    {
        return view('Admin.skills.form', [
            'data' => $skill,
            'categories' => Skill::CATEGORIES,
        ]);
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $previousImage = $skill->image;

        $skill->fill($this->payload($request));
        $skill->sort_order = (int) ($request->input('sort_order') ?? 0);

        if ($request->boolean('remove_image')) {
            $this->deleteManagedImage($previousImage);
            $skill->image = null;
        }

        if ($request->hasFile('image')) {
            $skill->image = $request->file('image')->store(self::DIRECTORY, 'public');
            $this->deleteManagedImage($previousImage);
        }

        $skill->save();

        return $this->backToIndex('Data updated successfully.');
    }

    public function destroy(Skill $skill): RedirectResponse
    {
        $this->deleteManagedImage($skill->image);
        $skill->delete();

        return $this->backToIndex('Data deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(SkillRequest $request): array
    {
        return $request->safe()->only(['name', 'category', 'url']);
    }

    /**
     * Only ever deletes files written to this controller's own directory.
     */
    private function deleteManagedImage(?string $path): void
    {
        if (blank($path) || ! str_starts_with($path, self::DIRECTORY.'/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function backToIndex(string $message): RedirectResponse
    {
        return redirect(route('skill.index'))->with('message', [
            ['success', $message],
        ]);
    }
}
