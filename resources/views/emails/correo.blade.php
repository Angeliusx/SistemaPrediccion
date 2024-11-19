<p style="font-family: Arial, sans-serif; line-height: 1.5;">
    {!! nl2br(e($mensaje)) !!}
</p>

<table style="font-family: Arial, sans-serif; border-spacing: 0; border-collapse: collapse; margin-top: 10px;">
    <tr>
        <!-- Celda de la imagen del logo, alineada a la izquierda de la firma -->
        <td style="vertical-align: top; padding-right: 10px;">
            <img src="{{ $message->embed($imagenPath) }}" alt="Logo" style="max-height: 100px; max-width: 100px; display: block;">
        </td>
        <!-- Celda de la firma, alineada a la derecha del logo -->
        <td style="vertical-align: top;">
            <p style="margin: 0; padding: 0; line-height: 1.5;">
                {!! nl2br(e($firma)) !!}
            </p>
        </td>
    </tr>
</table>
