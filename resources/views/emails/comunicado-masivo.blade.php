<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Georgia,'Times New Roman',serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="padding:24px 28px;">
                            <div style="color:#0f172a;font-size:15px;line-height:1.6;">
                                {!! $comunicado->cuerpo_html !!}
                            </div>
                            <p style="margin-top:28px;font-size:11px;color:#94a3b8;">
                                Comunicación oficial de su Hermandad. Si no esperaba este mensaje, puede ignorarlo.
                            </p>
                        </td>
                    </tr>
                </table>
                <img src="{{ $trackingUrl }}" alt="" width="1" height="1" style="display:block;width:1px;height:1px;border:0;opacity:0;" />
            </td>
        </tr>
    </table>
</body>
</html>
