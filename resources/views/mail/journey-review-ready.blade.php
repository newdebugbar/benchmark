<!doctype html>
<html lang="en">
<body style="margin:0;background:#f4f0e8;color:#243126;font-family:Arial,sans-serif">
    <div style="max-width:620px;margin:0 auto;padding:48px 28px">
        <p style="font-size:12px;letter-spacing:.18em;text-transform:uppercase;color:#7d503b">Morrow</p>
        <h1 style="font-family:Georgia,serif;font-size:38px;font-weight:400;margin:24px 0 12px">Your Kyoto journey is ready.</h1>
        <p style="font-size:17px;line-height:1.7;color:#5d655d">{{ $trip->summary }}</p>
        <p style="margin-top:28px"><a href="{{ route('trips.show', $trip) }}" style="display:inline-block;padding:13px 18px;background:#243126;color:white;text-decoration:none;border-radius:999px">Review the journey</a></p>
    </div>
</body>
</html>
