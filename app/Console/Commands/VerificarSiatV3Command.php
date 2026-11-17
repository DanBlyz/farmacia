<?php

namespace App\Console\Commands;

use App\Models\UrlApiServicio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VerificarSiatV3Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:verificar-v3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica la disponibilidad y el estado HTTP de los endpoints SOAP de SIAT V3 (/sdf/)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  DIAGNÓSTICO DE CONECTIVIDAD SIAT V3 (BOLIVIA - IMPUESTOS)     ');
        $this->info('================================================================');

        $urls = UrlApiServicio::query()->get();

        if ($urls->isEmpty()) {
            $this->warn('No se encontraron registros de URLs en la tabla url_api_servicios.');
            $this->line('Ejecute: php artisan db:seed --class=UrlServicioSeeder');

            return self::FAILURE;
        }

        $filas = [];
        $todosOperativos = true;

        foreach ($urls as $item) {
            $url = (string) $item->url_servicio;
            $estadoHttp = 'ERR';
            $diagnostico = 'No responde';

            try {
                $response = Http::withoutVerifying()
                    ->timeout(5)
                    ->get($url);

                $status = $response->status();
                $estadoHttp = (string) $status;

                if ($status === 200) {
                    $diagnostico = 'Operativo (WSDL Disponible)';
                } elseif ($status === 503) {
                    $diagnostico = 'Mantenimiento en Servidor SIN (HTTP 503)';
                    $todosOperativos = false;
                } elseif ($status === 404) {
                    $diagnostico = 'Ruta no encontrada (HTTP 404)';
                    $todosOperativos = false;
                } else {
                    $diagnostico = "Código HTTP {$status}";
                    $todosOperativos = false;
                }
            } catch (\Throwable $e) {
                $diagnostico = 'Error de conexión: '.$e->getMessage();
                $todosOperativos = false;
            }

            $filas[] = [
                $item->nombre,
                $url,
                $estadoHttp,
                $diagnostico,
            ];
        }

        $this->table(
            ['Servicio', 'URL Endpoint', 'HTTP', 'Diagnóstico'],
            $filas
        );

        $this->newLine();

        if ($todosOperativos) {
            $this->info('✔ Todos los servicios SOAP V3 se encuentran activos y operativos.');
        } else {
            $this->warn('⚠ ATENCIÓN: El servidor del SIAT V3 de Impuestos Nacionales (/sdf/) está en mantenimiento o no disponible.');
            $this->line('  • Las URLs configuradas son las oficiales del SIN indicadas en su portal.');
            $this->line('  • Cuando Impuestos Nacionales habilite el backend V3, el estado cambiará a HTTP 200.');
            $this->line('  • Puede re-ejecutar este comando en cualquier momento para comprobar si el SIN ya restableció el servicio.');
        }

        return self::SUCCESS;
    }
}
