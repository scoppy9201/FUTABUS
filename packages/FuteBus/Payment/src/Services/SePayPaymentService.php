<?php

declare(strict_types=1);

namespace FuteBus\Payment\Services;

class SePayPaymentService
{
    public function __construct(
        private readonly SePayQrService $qr,
        private readonly SePayIntentService $intents,
        private readonly SePayWebhookProcessor $webhooks,
    ) {}

    public function ready(): bool
    {
        return $this->qr->configured() && filled(config('services.sepay.webhook_key'));
    }

    public function createIntent(array $preview): void
    {
        $this->intents->createIntent($preview);
    }

    public function receive(array $event): void
    {
        $this->webhooks->receive($event);
    }
}
