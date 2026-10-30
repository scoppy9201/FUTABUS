# AiAssistant

The support widget belongs to this module. Install dependencies from the repository root:

```powershell
composer install
npm.cmd ci
php artisan migrate
```

Configure the OpenAI settings using `.env.example`. Without an API key, the assistant uses local intent matching.
The migration creates chat sessions; it does not change existing bookings or payments.

```powershell
php artisan test tests/Feature/AiAssistant
node --test tests/js/ai-chat-navigation.test.mjs packages/FuteBus/AiAssistant/tests/js/ai-assistant.test.mjs
npm.cmd run build
```

Customer contact details stay out of the model prompt. Ticket and booking actions require explicit navigation confirmation.
Reports require server-side permission checks and use the reporting service.
