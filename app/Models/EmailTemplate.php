<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    public const INVITATION = 'candidate-invitation';
    public const OTP        = 'otp-code';
    public const REVIEWED   = 'document-reviewed';

    protected $fillable = [
        'key', 'name', 'description', 'subject', 'heading',
        'body', 'button_label', 'footer_note', 'is_active', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('email_templates'));
        static::deleted(fn () => Cache::forget('email_templates'));
    }

    /**
     * Look up a template, falling back to the shipped default so an email can
     * never fail to send just because a row is missing or was switched off.
     */
    public static function resolve(string $key): array
    {
        $rows = Cache::remember('email_templates', 600, fn () => self::all()->keyBy('key'));
        $row  = $rows->get($key);

        if (! $row || ! $row->is_active) {
            return self::defaults()[$key] ?? [];
        }

        return [
            'subject'      => $row->subject,
            'heading'      => $row->heading,
            'body'         => $row->body,
            'button_label' => $row->button_label,
            'footer_note'  => $row->footer_note,
        ];
    }

    /** Swap {{ placeholder }} for real values across every field. */
    public static function render(string $key, array $vars): array
    {
        $tpl = self::resolve($key);

        $replace = function (?string $text) use ($vars): string {
            if (blank($text)) {
                return '';
            }

            foreach ($vars as $name => $value) {
                $text = preg_replace(
                    '/\{\{\s*'.preg_quote($name, '/').'\s*\}\}/',
                    (string) $value,
                    $text
                );
            }

            // Anything left unmatched is dropped rather than shown to a candidate.
            return trim(preg_replace('/\{\{\s*[\w.]+\s*\}\}/', '', $text));
        };

        return [
            'subject'      => $replace($tpl['subject'] ?? ''),
            'heading'      => $replace($tpl['heading'] ?? ''),
            'body'         => $replace($tpl['body'] ?? ''),
            'button_label' => $replace($tpl['button_label'] ?? ''),
            'footer_note'  => $replace($tpl['footer_note'] ?? ''),
        ];
    }

    /** Placeholders offered in the editor, per template. */
    public static function placeholders(string $key): array
    {
        $shared = [
            'agency_name'  => 'Your agency name',
            'first_name'   => 'Candidate first name',
            'full_name'    => 'Candidate full name',
            'reference_no' => 'Candidate reference number',
        ];

        return match ($key) {
            self::INVITATION => $shared + [
                'upload_link'   => 'The candidate’s upload link',
                'expiry_date'   => 'Date the link stops working',
                'document_list' => 'Bullet list of requested documents',
            ],
            self::OTP => $shared + [
                'otp_code'    => 'The six-digit code',
                'otp_minutes' => 'Minutes until the code expires',
            ],
            self::REVIEWED => $shared + [
                'document_name' => 'Name of the reviewed document',
                'decision'      => 'Approved or Rejected',
                'remarks'       => 'The reviewer’s note',
                'upload_link'   => 'The candidate’s upload link',
            ],
            default => $shared,
        };
    }

    /** Sample values so the preview screen shows something realistic. */
    public static function sampleVars(string $key): array
    {
        return [
            'agency_name'   => config('portal.agency_name'),
            'first_name'    => 'Maria',
            'full_name'     => 'Maria Elena Santos',
            'reference_no'  => 'CND-26-4XK9QP',
            'upload_link'   => url('/upload/sample-token-for-preview-only'),
            'expiry_date'   => now()->addDays((int) config('portal.invite.valid_days'))->format('d F Y'),
            'document_list' => "RN License\nBLS (AHA)\nPhoto ID",
            'otp_code'      => '481902',
            'otp_minutes'   => (int) config('portal.otp.ttl_minutes'),
            'document_name' => 'RN License',
            'decision'      => 'Rejected',
            'remarks'       => 'The expiry date is cut off. Please re-scan the whole card.',
        ];
    }

    /** The wording the app ships with — also what "Restore default" writes back. */
    public static function defaults(): array
    {
        return [
            self::INVITATION => [
                'name'         => 'Upload invitation',
                'description'  => 'Sent when you invite a candidate, and again on every re-send.',
                'subject'      => 'Upload your documents for {{ agency_name }}',
                'heading'      => '{{ first_name }}, please send us your documents',
                'body'         => "Rather than emailing attachments back and forth, upload everything through our secure page.\n\nYou will confirm your mobile number with a one-time code, then upload each item. It usually takes about ten minutes.\n\nThis link is meant only for you — please do not forward it.",
                'button_label' => 'Upload my documents',
                'footer_note'  => 'This link works until {{ expiry_date }}.',
            ],
            self::OTP => [
                'name'         => 'Verification code',
                'description'  => 'Sent alongside the SMS when a candidate asks for a code.',
                'subject'      => 'Your {{ agency_name }} verification code',
                'heading'      => 'Your verification code',
                'body'         => "Enter this code on the verification screen to reach your upload page.\n\nIf you did not request it, you can ignore this email — nobody can use the code without it.",
                'button_label' => null,
                'footer_note'  => 'The code expires in {{ otp_minutes }} minutes.',
            ],
            self::REVIEWED => [
                'name'         => 'Document reviewed',
                'description'  => 'Sent when a recruiter approves or rejects a document.',
                'subject'      => '{{ document_name }} — {{ decision }}',
                'heading'      => 'We have reviewed your {{ document_name }}',
                'body'         => "Our team has looked at the file you sent.\n\nIf we have asked for a new copy, the note below explains exactly what to change. Everything else you uploaded stays as it is.",
                'button_label' => 'Open my upload page',
                'footer_note'  => null,
            ],
        ];
    }

    /**
     * Placeholders prepared for display. The token is assembled here rather than
     * in a Blade view: a literal double-brace inside a Blade echo closes it early.
     *
     * @return array<int, array{tag:string, token:string, meaning:string}>
     */
    public static function placeholderTokens(string $key): array
    {
        $open  = str_repeat('{', 2);
        $close = str_repeat('}', 2);

        $out = [];

        foreach (self::placeholders($key) as $tag => $meaning) {
            $out[] = [
                'tag'     => $tag,
                'token'   => $open.' '.$tag.' '.$close,
                'meaning' => $meaning,
            ];
        }

        return $out;
    }

    public function placeholderList(): array
    {
        return self::placeholderTokens($this->key);
    }

    public function defaultWording(): array
    {
        return self::defaults()[$this->key] ?? [];
    }

    public function differsFromDefault(): bool
    {
        $d = $this->defaultWording();

        return collect(['subject', 'heading', 'body', 'button_label', 'footer_note'])
            ->contains(fn ($f) => trim((string) $this->$f) !== trim((string) ($d[$f] ?? '')));
    }

    public function excerpt(): string
    {
        return Str::limit(strip_tags($this->body), 90);
    }
}
