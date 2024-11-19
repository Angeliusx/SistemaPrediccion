<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreoMasivo extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $mensaje;
    public $firma;

    public function __construct($asunto, $mensaje, $firma)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->firma = $firma;
    }

    public function build()
    {
        return $this->subject($this->asunto)
                    ->markdown('emails.correo')
                    ->with([
                        'mensaje' => $this->mensaje,
                        'firma' => $this->firma,
                    ])
                    ->cc(['angellogch10@gmail.com']);
    }
}
