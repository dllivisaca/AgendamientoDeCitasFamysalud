<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Encuesta de satisfacción - FamySALUD</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111;">
  <div style="max-width:640px;margin:0 auto;padding:24px;">
    <div style="background:#ffffff;border:1px solid #e6eaf2;border-radius:10px;overflow:hidden;">
      <div style="padding:18px 22px;background:#0f172a;color:#fff;">
        <div style="font-size:22px;font-weight:700;line-height:1.2;">
          Encuesta de satisfacción - FamySALUD
        </div>
        <div style="font-size:13px;opacity:.9;margin-top:6px;">
          Tu opinión nos ayuda a mejorar ✨
        </div>
      </div>

      <div style="padding:22px;">
        <p style="margin:0 0 12px;font-size:15px;line-height:1.5;">
          Hola <strong>{{ $patientName }}</strong>, gracias por tu visita.
        </p>

        <p style="margin:0 0 18px;font-size:15px;line-height:1.5;">
          Por favor completa esta breve encuesta:
        </p>

        <a href="{{ $surveyUrl }}"
           style="display:inline-block;background:#2563eb;color:#fff;text-decoration:none;
                  padding:12px 16px;border-radius:8px;font-weight:700;font-size:14px;">
          Completar encuesta
        </a>

        <p style="margin:18px 0 0;font-size:12px;color:#64748b;line-height:1.4;">
          Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
          <span style="word-break:break-all;">{{ $surveyUrl }}</span>
        </p>
      </div>
    </div>

    <div style="text-align:center;font-size:11px;color:#94a3b8;margin-top:14px;">
      FamySALUD en Línea · Este es un correo automático, por favor no respondas.
    </div>
  </div>
</body>
</html>