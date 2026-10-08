<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailTemplateController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index(): View
    {
        return view('admin.email-templates.index', [
            'templates' => EmailTemplate::with('updatedBy:id,name')->orderBy('id')->get(),
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): View
    {
        return view('admin.email-templates.edit', [
            'template'     => $emailTemplate,
            'placeholders' => $emailTemplate->placeholderList(),
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->update($request->safe()->only([
            'subject', 'heading', 'body', 'button_label', 'footer_note',
        ]) + [
            'is_active'  => $request->boolean('is_active', true),
            'updated_by' => Auth::guard('admin')->id(),
        ]);

        $this->logger->record('email_template.updated', 'Edited the "'.$emailTemplate->name.'" email', $emailTemplate);

        return redirect()
            ->route('admin.email-templates.edit', $emailTemplate)
            ->with('status', 'Saved. New emails will use this wording.');
    }

    /** Renders the email exactly as a candidate would receive it, with sample data. */
    public function preview(EmailTemplate $emailTemplate): Response
    {
        $vars = EmailTemplate::sampleVars($emailTemplate->key);

        $tpl = EmailTemplate::render($emailTemplate->key, $vars);

        $html = view('emails.templated', [
            'tpl'          => $tpl,
            'reference'    => $vars['reference_no'],
            'link'         => $emailTemplate->key === EmailTemplate::OTP ? null : $vars['upload_link'],
            'otpCode'      => $emailTemplate->key === EmailTemplate::OTP ? $vars['otp_code'] : null,
            'decisionNote' => $emailTemplate->key === EmailTemplate::REVIEWED ? $vars['remarks'] : null,
            'decisionTone' => 'danger',
            'documents'    => null,
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }

    /** Sends the preview to the signed-in admin so they can check it in a real inbox. */
    public function test(EmailTemplate $emailTemplate): RedirectResponse
    {
        $admin = Auth::guard('admin')->user();
        $vars  = EmailTemplate::sampleVars($emailTemplate->key);
        $tpl   = EmailTemplate::render($emailTemplate->key, $vars);

        try {
            Mail::send('emails.templated', [
                'tpl'          => $tpl,
                'reference'    => $vars['reference_no'],
                'link'         => $emailTemplate->key === EmailTemplate::OTP ? null : $vars['upload_link'],
                'otpCode'      => $emailTemplate->key === EmailTemplate::OTP ? $vars['otp_code'] : null,
                'decisionNote' => $emailTemplate->key === EmailTemplate::REVIEWED ? $vars['remarks'] : null,
                'decisionTone' => 'danger',
                'documents'    => null,
            ], fn ($m) => $m->to($admin->email)->subject('[Test] '.$tpl['subject']));
        } catch (Throwable $e) {
            return back()->with('warning', 'Could not send the test email: '.$e->getMessage());
        }

        return back()->with('status', 'A test copy was sent to '.$admin->email.'.');
    }

    public function restore(EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->update($emailTemplate->defaultWording() + [
            'updated_by' => Auth::guard('admin')->id(),
        ]);

        $this->logger->record('email_template.restored', 'Restored the default "'.$emailTemplate->name.'" wording', $emailTemplate);

        return back()->with('status', 'The original wording is back.');
    }
}
