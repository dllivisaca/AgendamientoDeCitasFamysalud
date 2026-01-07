<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Comprobante de transferencia' }}</title>
  <style>
    html, body { height: 100%; margin: 0; }
    .wrap { height: 100%; }
    iframe { width: 100%; height: 100%; border: 0; display:block; }
  </style>
</head>
<body>
  <div class="wrap">
    <iframe src="{{ $fileUrl }}"></iframe>
  </div>
</body>
</html>