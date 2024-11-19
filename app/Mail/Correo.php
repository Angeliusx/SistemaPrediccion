<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Correo extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $asunto;
    public $mensaje;
    public $firma;
    public $imagenPath;
    public $usuario;
    public $correoCC;

    /**
     * Create a new message instance.
     *
     * @param string $asunto
     * @param string $mensaje
     * @param string $firma
     * @param string $imagenPath
     */
    public function __construct($asunto, $mensaje, $firma, $imagenPath, $usuario,$correoCC)
    {
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->firma = $firma;
        $this->imagenPath = $imagenPath;
        $this->usuario = $usuario;
        $this->correoCC = $correoCC;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->usuario->email, $this->usuario->name)
            ->view('emails.correo')    // Define la vista Blade para el correo
            ->subject($this->asunto)           // Establece el asunto del correo
            ->with([                           // Pasa los datos necesarios a la vista
                'mensaje' => $this->mensaje,
                'firma' => $this->firma,
            ])
            ->attach($this->imagenPath)
            ->cc($this->correoCC); 
    }
}
