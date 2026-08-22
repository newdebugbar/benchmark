<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $trip->title }} · Morrow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main>
        <h1>{{ $trip->title }}</h1>
        <p>{{ $trip->summary }}</p>
        <p>{{ $days->count() }} days · {{ $bookings->count() }} bookings</p>
        <livewire:trip-review-panel :trip-id="$trip->id" />
        <livewire:journey-pulse />
    </main>
</body>
</html>
