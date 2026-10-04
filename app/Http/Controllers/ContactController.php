<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('portfolio.contact');
    }

    /**
     * Server-side replacement for the old Google Sheets feedback form.
     *
     * The previous implementation POSTed straight from the browser to a Google
     * Apps Script web-app URL embedded in public JavaScript. That endpoint had no
     * validation, no spam protection and no rate limit, and it exposed the owner's
     * spreadsheet as an open write-only relay. Storing messages in the application
     * database needs no third-party credentials.
     */
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'subject', 'message']);

        try {
            ContactMessage::create($data + [
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            ]);
        } catch (\Throwable $e) {
            // Never surface an infrastructure detail to the visitor.
            report($e);

            Log::warning('Portfolio contact message could not be stored.', [
                'exception' => $e,
            ]);

            return back()
                ->withInput($request->except('message', 'website'))
                ->with('contact_status', 'error');
        }

        return redirect()
            ->route('contact')
            ->with('contact_status', 'success');
    }
}
