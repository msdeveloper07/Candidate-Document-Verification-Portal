# Cyberbells LLC — Candidate Credentialing Portal

A Laravel portal that replaces email attachments for healthcare staffing agencies. The agency invites a candidate; the candidate opens one link,
proves who they are with an OTP, and uploads each requested document. Everything
lands in one reviewable file instead of an inbox.

## The two roles

| | Admin | Candidate |
|---|---|---|
| Signs in with | Email + password | One-time code sent to their mobile |
| Reaches the app via | `/admin/login` | A tokenised link emailed to them |
| Sees | Everything: all candidates, all files, all activity | Only their own checklist and files |
| Interface | Dense sidebar console | Single-column upload page, mobile-first |

Two separate Laravel guards (`admin`, `candidate`) back this — a candidate session
can never reach admin routes and vice versa.

## How the candidate flow works

1. Admin adds a candidate and ticks which documents to request.
2. The app emails a link containing a 64-character single-use token.
3. Candidate opens it and is asked to confirm the mobile number the agency holds.
   The number typed **must match** the record — a wrong number never receives a code.
4. A 6-digit OTP goes out by SMS (and optionally email). It is stored hashed,
   expires in 10 minutes, allows 5 attempts, and is rate-limited per hour.
5. On success the candidate is logged into the `candidate` guard and sees their
   checklist. Uploads go over AJAX with a progress bar.
6. Admin reviews each file: approve, or reject with a note explaining what to fix.
   Rejections email the candidate and reopen that item for re-upload.

Replacing a file never destroys the old one — it is archived into
`document_revisions` so the agency keeps a full audit trail.

## Requirements

- PHP 8.2+ (with `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `gd`)
- MySQL 8.0+ (or MariaDB 10.6+)
- Composer 2
- No Node.js — there is no front-end build step

## Install

This is a complete project — do **not** run `composer create-project` first.
Unzip it, then from inside the folder:

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Create an empty MySQL database and point `.env` at it:

```env
DB_DATABASE=hireflow
DB_USERNAME=root
DB_PASSWORD=
```

Then build the schema and start the server:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Sign in at `http://localhost:8000/admin/login`:

- **admin@hireflow.test** / `Password@123` — super admin
- **recruiter@hireflow.test** / `Password@123` — recruiter

Change these before going anywhere near production.

There is no npm or Vite step. Bootstrap loads from a CDN and everything else is
plain CSS and jQuery in `public/assets/`.

### If `composer install` complains about `artisan`

`Could not open input file: artisan` means the skeleton files are missing from
the folder — you have `app/` and `config/` but not the entry points. Make sure
`artisan`, `public/index.php`, `bootstrap/cache/` and the `storage/framework/`
subfolders all exist, then run:

```bash
composer dump-autoload
php artisan key:generate
```

### Permissions (Linux / macOS only)

```bash
chmod -R 775 storage bootstrap/cache
```

## Testing the OTP without an SMS account

Out of the box `SMS_DRIVER=log` writes the message to `storage/logs/laravel.log`
instead of sending it. With `OTP_EXPOSE=true` the code is also printed on the
verification screen so you can click straight through.

**Set `OTP_EXPOSE=false` in production.** It is the one setting that would
completely defeat the verification step.

To send real messages, set `SMS_DRIVER=twilio` (or `vonage`) and fill in the
credentials. Adding another provider means writing one class implementing
`App\Support\Sms\SmsGateway` and adding a line to `RepositoryServiceProvider`.

## Architecture

```
app/
├── Enums/              CandidateStatus, DocumentStatus, AdminRole
├── Http/
│   ├── Controllers/
│   │   ├── Admin/      Auth, Dashboard, Candidate, DocumentReview, DocumentType, AdminUser, Profile
│   │   └── Candidate/  Invite, OtpAuth, Portal
│   ├── Middleware/     admin.active, admin.role, candidate.invite, guest.as
│   └── Requests/       One form request per write action
├── Mail/               Invitation, OTP, ReviewOutcome
├── Models/
├── Repositories/
│   ├── Contracts/      Interfaces — what the app depends on
│   └── Eloquent/       Implementations — bound in RepositoryServiceProvider
├── Services/           CandidateService, DocumentService, OtpService, ActivityLogger
└── Support/Sms/        SmsGateway interface + Log / Twilio / Vonage drivers
```

Controllers stay thin: they validate through a form request, call a service, and
return a view. Services hold the business rules and are the only place that writes
across more than one table. Repositories own the queries, so swapping a data source
or adding caching touches one file.

### Database

| Table | Holds |
|---|---|
| `admins` | Agency staff, with role and active flag |
| `candidates` | One row per person, with invite token and status |
| `document_types` | The configurable checklist (name, formats, size cap, instructions) |
| `candidate_references` | Three reference slots per candidate, filled in by the candidate |
| `email_templates` | Editable wording for every message the portal sends |
| `candidate_requirements` | Which documents each candidate must send |
| `candidate_documents` | The current live file per requirement |
| `document_revisions` | Superseded uploads, kept for audit |
| `otp_codes` | Hashed codes with attempts, expiry and origin IP |
| `activity_logs` | Every meaningful action, by admin, candidate or system |
| `settings` | Agency branding and workflow values editable at runtime |

### Security decisions worth knowing

- Uploaded files go to a **private disk** (`storage/app/private/documents`) and are
  only ever served through a controller that checks ownership. There is no
  guessable public URL.
- OTPs are stored as hashes, never plaintext.
- Extensions are checked against an allowlist per document type, plus a global
  blocklist (`php`, `svg`, `html`, …) that applies regardless of configuration.
- Both login routes are rate-limited; OTP sending is additionally throttled per
  number with a resend cooldown and an hourly ceiling.
- Invite tokens are rotated every time a link is re-sent, so an old email stops working.
- The candidate guard is dropped automatically the moment the invite expires.

## Configuring the checklist

Everything under **Configuration → Document checklist** is data, not code. Add a
document type, set its accepted formats, size limit and instructions, and mark it
as a default if it should be pre-ticked for new candidates. The seeder ships with
five: RN License, BLS (AHA), COVID-19 Vaccination Record, Resume and Photo ID.

References are **not** documents — they are up to three sets of contact details
the candidate types into their portal (name, relationship, facility, email/phone).
They are **optional**: a candidate can submit without them, and they do not count
towards the clearance total. A slot left blank is skipped; a slot that has been
started needs a name and either an email or a phone number, because a reference
nobody can contact is of no use to a recruiter. Clearing a slot deletes it.

### Document status flow

| Status | Meaning |
|---|---|
| Pending | Requested, nothing received yet |
| Uploaded | File received, not yet in the recruiter's queue |
| Under review | Sitting with the recruiter |
| Approved | Accepted |
| Rejected | Sent back with a required note explaining what to fix |

`REVIEW_AUTO_UNDER_REVIEW=true` (the default) sends uploads straight into the
review queue. Set it to `false` and files stay at Uploaded until the candidate
presses Submit, which moves the whole dossier over at once.

The per-candidate selection is independent, so a driver and an accountant can be
asked for different things without touching the code.

### Upgrading an existing database

New columns ship in two places: the original `create_candidates_table` migration
(for fresh installs) and a guarded `add_profile_fields_to_candidates_table`
migration that only adds what is missing. So on an existing database a plain
`php artisan migrate` is enough — no `migrate:fresh`, no data loss.

Re-running `db:seed` also switches off the generic document types from earlier
builds so they stop appearing next to the clinical checklist. Rows are
deactivated rather than deleted, because candidates may already have been asked
for them.

## Agency name and branding

The agency name, tagline, support contacts and the two timing rules live under
**Configuration → Agency settings**. Values saved there are written to the
`settings` table and **override** `AGENCY_NAME` and friends in `.env`, so the
name can be changed from the browser without touching the server.

That precedence matters: editing only `.env` will appear to do nothing once a
value exists in the database.

## Email

Three messages go out: the upload invitation, the verification code, and the
review outcome. All of them are editable under **Configuration → Email templates**
without touching code.

An admin edits the subject, heading, message, button text and closing note.
The branded layout, the document list, the OTP block and the link fallback are
owned by the code, so the wording can be changed freely without anyone being
able to break the email. Placeholders like `{{ first_name }}` and
`{{ upload_link }}` are inserted from a click-list beside the editor, and any
tag that is not valid for that template is rejected on save rather than being
mailed out raw.

Each template has a **Preview** (opens the rendered email with sample data) and
**Send me a test**, which mails a copy to the signed-in admin. **Restore default
wording** puts the shipped text back.

### When email does not arrive

Mail failures never lose work. If the mailer throws while sending an invitation,
the candidate and their link are still saved — the portal flags the record,
explains that delivery failed and offers the link for copying. The same link can
be copied at any time from the candidate's record or from the row menu on the
candidates list, so a recruiter can send it by WhatsApp or SMS instead.

Verification-code and review emails fail quietly for the same reason: SMS is the
real channel for codes, and a review decision is already saved before the email
is attempted. Failures are written to `storage/logs/laravel.log`.

## Front end

Bootstrap 5.3 plus about 900 lines of hand-written CSS, no build step. jQuery
handles the OTP boxes, the drag-and-drop uploader and the review dialogs.

The two portals deliberately do not look alike. Both draw on a navy-and-brass
palette with Sora for headings, Public Sans for body text and IBM Plex Mono for
reference numbers, codes and file sizes — the admin side uses it for a dense
console, the candidate side for a calm single column.

Two elements carry the design:

- **Collection bar** (admin) — a segmented rule on every candidate row showing
  approved / in review / rejected / not sent at a glance.
- **Document sleeve** (candidate) — each requested item is a labelled sleeve that
  opens to reveal instructions, the current file and the drop zone, with the
  reviewer's decision stamped at the corner.

## Things left deliberately open

- Phone numbers are validated by shape, not by carrier. If you need real
  validation, add `giggsey/libphonenumber-for-php` inside `StoreCandidateRequest`.
- Mail sends synchronously. Set `QUEUE_CONNECTION=database`, run
  `php artisan queue:work`, and add `implements ShouldQueue` to the mailables.
- Files are stored on the local disk. Switching to S3 is a one-line change in
  `config/filesystems.php` — nothing in the code assumes local paths.
