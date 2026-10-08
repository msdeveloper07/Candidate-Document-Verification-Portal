<?php

namespace App\Providers;

use App\Repositories\Contracts;
use App\Repositories\Eloquent;
use App\Support\Sms\LogSmsGateway;
use App\Support\Sms\SmsGateway;
use App\Support\Sms\TwilioSmsGateway;
use App\Support\Sms\VonageSmsGateway;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /** Contract => concrete implementation. */
    public array $bindings = [
        Contracts\AdminRepositoryInterface::class             => Eloquent\AdminRepository::class,
        Contracts\CandidateRepositoryInterface::class         => Eloquent\CandidateRepository::class,
        Contracts\CandidateDocumentRepositoryInterface::class => Eloquent\CandidateDocumentRepository::class,
        Contracts\DocumentTypeRepositoryInterface::class      => Eloquent\DocumentTypeRepository::class,
        Contracts\ActivityLogRepositoryInterface::class       => Eloquent\ActivityLogRepository::class,
    ];

    public function register(): void
    {
        $this->app->bind(SmsGateway::class, function ($app) {
            return match (config('sms.driver')) {
                'twilio' => $app->make(TwilioSmsGateway::class),
                'vonage' => $app->make(VonageSmsGateway::class),
                default  => $app->make(LogSmsGateway::class),
            };
        });
    }
}
