<?php

namespace App\Jobs;

use App\Mail\Correo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class EnviarCorreoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $destinatario;
    protected $asunto;
    protected $mensaje;
    protected $firma;
    protected $imagenPath;

    public function __construct($destinatario, $asunto, $mensaje, $firma, $imagenPath)
    {
        $this->destinatario = $destinatario;
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->firma = $firma;
        $this->imagenPath = $imagenPath;
    }

    public function handle()
    {
        // Aquí se envía el correo usando el Mailable
        Mail::to($this->destinatario)->send(new Correo($this->asunto, $this->mensaje, $this->firma, $this->imagenPath));
    }
}

