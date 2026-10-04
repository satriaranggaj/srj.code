<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CertificateRequest;
use App\Models\Certificate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificateController extends Controller
{
    private const DIRECTORY = 'certificates';

    public function index(): View
    {
        return view('Admin.certificates.index', [
            'certificates' => Certificate::ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('Admin.certificates.form');
    }

    public function store(CertificateRequest $request): RedirectResponse
    {
        $certificate = new Certificate;
        $certificate->fill($this->payload($request));
        $certificate->sort_order = (int) ($request->input('sort_order') ?? 0);
        $certificate->save();

        if ($request->hasFile('image')) {
            $certificate->image = $request->file('image')->store(self::DIRECTORY, 'public');
            $certificate->save();
        }

        return $this->backToIndex('Data saved successfully.');
    }

    public function edit(Certificate $certificate): View
    {
        return view('Admin.certificates.form', [
            'data' => $certificate,
        ]);
    }

    public function update(CertificateRequest $request, Certificate $certificate): RedirectResponse
    {
        $previousImage = $certificate->image;

        $certificate->fill($this->payload($request));
        $certificate->sort_order = (int) ($request->input('sort_order') ?? 0);

        if ($request->boolean('remove_image')) {
            $this->deleteManagedImage($previousImage);
            $certificate->image = null;
        }

        if ($request->hasFile('image')) {
            $certificate->image = $request->file('image')->store(self::DIRECTORY, 'public');
            $this->deleteManagedImage($previousImage);
        }

        $certificate->save();

        return $this->backToIndex('Data updated successfully.');
    }

    public function destroy(Certificate $certificate): RedirectResponse
    {
        $this->deleteManagedImage($certificate->image);
        $certificate->delete();

        return $this->backToIndex('Data deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CertificateRequest $request): array
    {
        return $request->safe()->only([
            'title',
            'link',
            'issuer',
            'issued_at',
            'description',
        ]);
    }

    private function deleteManagedImage(?string $path): void
    {
        if (blank($path) || ! str_starts_with($path, self::DIRECTORY.'/')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function backToIndex(string $message): RedirectResponse
    {
        return redirect(route('certificate.index'))->with('message', [
            ['success', $message],
        ]);
    }
}
