# Morrow

Morrow is a realistic Laravel travel-planning application for exercising New Debug Bar. Its main journey workspace is designed to look and behave like a small product while producing deterministic local activity in every debug section.

## Run it locally

Morrow needs PHP 8.4 or newer, Composer, Node.js, and Redis.

```bash
git clone https://github.com/newdebugbar/benchmark.git
cd benchmark
composer setup
php artisan serve
```

Open the URL printed by Laravel and visit `/trips/kyoto-autumn`.

No external service receives traffic. Outbound partner requests use reserved `.test` domains and Laravel's HTTP fake, mail uses the array transport, and all seeded data is fixed.

## What it covers

The canonical journey request activates Overview, Requests, Timeline, Queries, HTTP Client, Queue, Mail, Notifications, Redis, Models, Cache, Views, Events, Authorization, Validation, Messages, Logs, Exceptions, and Livewire.

The application also includes AJAX search, an Inertia partial reload, a validation redirect, a download, a stream, a JSON error, an Artisan command, and a queue-worker profile.

```bash
php artisan morrow:refresh
composer test
```

The automated checks read `X-NewDebugBar-Profile` because New Debug Bar adds that header to profiled responses. Morrow does not set or configure it.

## License

Morrow is available under the [MIT license](LICENSE).
