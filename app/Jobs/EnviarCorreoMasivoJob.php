<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\CorreoMasivo;

class EnviarCorreoMasivoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $correo;
    protected $dataCorreo;

    public function __construct($correo, $dataCorreo)
    {
        $this->correo = $correo;
        $this->dataCorreo = $dataCorreo;
    }

    public function handle()
    {
        try {
            // Enviar el correo usando la clase Mailable CorreoMasivo
            Mail::to($this->correo)->send(new CorreoMasivo(
                $this->dataCorreo['asunto'],
                $this->dataCorreo['mensaje'],
                $this->dataCorreo['firma'],
                $this->dataCorreo['imagenPath']
                
            ));

        } catch (\Exception $e) {
            // Registrar errores en el log para seguimiento
            \Log::error('Error al enviar el correo: ' . $e->getMessage());
        }
    }
}
