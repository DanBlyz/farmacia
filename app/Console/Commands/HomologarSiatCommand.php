<?php

namespace App\Console\Commands;

use App\Services\SiatHomologacionService;
use Illuminate\Console\Command;

class HomologarSiatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siat:homologar 
                            {etapa : Etapa a ejecutar (catalogos | cufd | emision | eventos)} 
                            {--pv= : Filtrar por punto de venta (0 o 1)} 
                            {--repeticiones= : Cantidad de repeticiones por registro (por defecto 50 en catálogos, 100 en cufd, 125 en emision, 5 en eventos)} 
                            {--motivo= : Filtrar por código de motivo (1 al 7) para eventos significativos}
                            {--metodo= : Método SOAP específico para la etapa de catálogos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta en lote las pruebas oficiales de homologación ante el SIAT (Ambiente Piloto 2)';

    /**
     * Execute the console command.
     */
    public function handle(SiatHomologacionService $servicio): int
    {
        $etapa = strtolower(trim((string) $this->argument('etapa')));
        $pvOption = $this->option('pv');
        $pv = $pvOption !== null ? (int) $pvOption : null;
        $repeticiones = $this->option('repeticiones') ? (int) $this->option('repeticiones') : null;
        $motivo = $this->option('motivo') ? (int) $this->option('motivo') : null;
        $metodo = $this->option('metodo');

        $this->info('===============================================================');
        $this->info('   SIAT BOLIVIA - SUITE DE HOMOLOGACIÓN OFICIAL (PILOTO)       ');
        $this->info('===============================================================');

        if ($etapa === 'catalogos') {
            return $this->ejecutarCatalogos($servicio, $pv, $repeticiones ?? 50, $metodo);
        }

        if ($etapa === 'cufd') {
            return $this->ejecutarCufd($servicio, $pv, $repeticiones ?? 100);
        }

        if ($etapa === 'emision' || $etapa === 'etapa4') {
            return $this->ejecutarEmision($servicio, $pv, $repeticiones ?? 125);
        }

        if ($etapa === 'eventos' || $etapa === 'etapa5') {
            return $this->ejecutarEventos($servicio, $pv, $repeticiones ?? 5, $motivo);
        }

        $this->error("Etapa '{$etapa}' no reconocida. Opciones disponibles: 'catalogos', 'cufd', 'emision', 'eventos'.");

        return Command::FAILURE;
    }

    protected function ejecutarCatalogos(
        SiatHomologacionService $servicio,
        ?int $pv,
        int $repeticiones,
        ?string $metodo
    ): int {
        $this->comment('Iniciando ETAPA II: Sincronización de Catálogos');
        $this->line("• Repeticiones por registro : {$repeticiones}");
        $this->line('• Punto de Venta            : '.($pv !== null ? "PV {$pv}" : 'Ambos (PV 1 y PV 0)'));
        if ($metodo) {
            $this->line("• Método filtrado           : {$metodo}");
        }
        $this->newLine();

        $barra = null;
        $currentRegistro = null;

        $resultado = $servicio->ejecutarEtapaCatalogos(
            $repeticiones,
            $pv,
            $metodo,
            function ($regIdx, $totalRegs, $info) use (&$barra, &$currentRegistro) {
                $claveReg = "{$info['metodo']}_pv{$info['pv']}";
                if ($currentRegistro !== $claveReg) {
                    $currentRegistro = $claveReg;
                    $this->newLine();
                    $this->line("<fg=cyan>[{$regIdx}/{$totalRegs}]</> {$info['nombre']} (<options=bold>PV {$info['pv']}</>) -> {$info['metodo']}");
                    $barra = $this->output->createProgressBar($info['total_iteraciones']);
                    $barra->setFormat(' %current%/%max% [%bar%] %percent:3s%% - <fg=green>✔ %message%</>');
                    $barra->setMessage('0 exitosos');
                    $barra->start();
                }

                if ($barra) {
                    $barra->setMessage("{$info['exitosos']} exitosos".($info['fallidos'] > 0 ? " | <fg=red>✖ {$info['fallidos']} fallos</>" : ''));
                    $barra->advance();
                }
            }
        );

        $this->newLine(2);
        $this->info('---------------------------------------------------------------');
        $this->info('RESUMEN DE ETAPA II (CATÁLOGOS):');
        $this->line("• Total llamadas SOAP : {$resultado['total_intentos']}");
        $this->line("• Exitosas (SIN)      : <fg=green>{$resultado['exitosos']}</>");
        $this->line('• Fallidas            : '.($resultado['fallidos'] > 0 ? "<fg=red>{$resultado['fallidos']}</>" : '0'));
        $this->info('---------------------------------------------------------------');

        return $resultado['fallidos'] === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    protected function ejecutarCufd(
        SiatHomologacionService $servicio,
        ?int $pv,
        int $repeticiones
    ): int {
        $this->comment('Iniciando ETAPA III: Obtención de CUFD');
        $this->line("• Repeticiones por registro : {$repeticiones}");
        $this->line('• Punto de Venta            : '.($pv !== null ? "PV {$pv}" : 'Ambos (PV 1 y PV 0)'));
        $this->newLine();

        $barra = null;
        $currentRegistro = null;

        $resultado = $servicio->ejecutarEtapaCufd(
            $repeticiones,
            $pv,
            function ($regIdx, $totalRegs, $info) use (&$barra, &$currentRegistro) {
                $claveReg = "cufd_pv{$info['pv']}";
                if ($currentRegistro !== $claveReg) {
                    $currentRegistro = $claveReg;
                    $this->newLine();
                    $this->line("<fg=cyan>[{$regIdx}/{$totalRegs}]</> Solicitud CUFD (<options=bold>Punto de Venta {$info['pv']}</>)");
                    $barra = $this->output->createProgressBar($info['total_iteraciones']);
                    $barra->setFormat(' %current%/%max% [%bar%] %percent:3s%% - <fg=green>✔ %message%</>');
                    $barra->setMessage('0 exitosos');
                    $barra->start();
                }

                if ($barra) {
                    $barra->setMessage("{$info['exitosos']} exitosos".($info['fallidos'] > 0 ? " | <fg=red>✖ {$info['fallidos']} fallos</>" : ''));
                    $barra->advance();
                }
            }
        );

        $this->newLine(2);
        $this->info('---------------------------------------------------------------');
        $this->info('RESUMEN DE ETAPA III (CUFD):');
        $this->line("• Total solicitudes CUFD : {$resultado['total_intentos']}");
        $this->line("• Exitosas (SIN)         : <fg=green>{$resultado['exitosos']}</>");
        $this->line('• Fallidas               : '.($resultado['fallidos'] > 0 ? "<fg=red>{$resultado['fallidos']}</>" : '0'));
        $this->info('---------------------------------------------------------------');

        return $resultado['fallidos'] === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    protected function ejecutarEmision(
        SiatHomologacionService $servicio,
        ?int $pv,
        int $repeticiones
    ): int {
        $this->comment('Iniciando ETAPA IV: Consumo de Métodos de Emisión Individual (Factura Compra-Venta)');
        $this->line("• Repeticiones por registro : {$repeticiones}");
        $this->line('• Punto de Venta            : '.($pv !== null ? "PV {$pv}" : 'Ambos (PV 1 y PV 0)'));
        $this->newLine();

        $barra = null;
        $currentRegistro = null;

        $resultado = $servicio->ejecutarEtapaEmision(
            $repeticiones,
            $pv,
            function ($regIdx, $totalRegs, $info) use (&$barra, &$currentRegistro) {
                $claveReg = "emision_pv{$info['pv']}";
                if ($currentRegistro !== $claveReg) {
                    $currentRegistro = $claveReg;
                    $this->newLine();
                    $this->line("<fg=cyan>[{$regIdx}/{$totalRegs}]</> Emisión Factura Individual (<options=bold>PV {$info['pv']}</>) -> recepcionFactura");
                    $barra = $this->output->createProgressBar($info['total_iteraciones']);
                    $barra->setFormat(' %current%/%max% [%bar%] %percent:3s%% - <fg=green>✔ %message%</>');
                    $barra->setMessage('0 exitosos');
                    $barra->start();
                }

                if ($barra) {
                    $barra->setMessage("{$info['exitosos']} exitosos".($info['fallidos'] > 0 ? " | <fg=red>✖ {$info['fallidos']} fallos</>" : ''));
                    $barra->advance();
                }
            }
        );

        $this->newLine(2);
        $this->info('---------------------------------------------------------------');
        $this->info('RESUMEN DE ETAPA IV (EMISIÓN INDIVIDUAL):');
        $this->line("• Total envíos XML    : {$resultado['total_intentos']}");
        $this->line("• Aprobadas por SIN   : <fg=green>{$resultado['exitosos']}</>");
        $this->line('• Fallidas            : '.($resultado['fallidos'] > 0 ? "<fg=red>{$resultado['fallidos']}</>" : '0'));
        $this->info('---------------------------------------------------------------');

        return $resultado['fallidos'] === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    protected function ejecutarEventos(
        SiatHomologacionService $servicio,
        ?int $pv,
        int $repeticiones,
        ?int $motivo
    ): int {
        $this->comment('Iniciando ETAPA V: Registro de Eventos Significativos');
        $this->line("• Repeticiones por registro : {$repeticiones}");
        $this->line('• Punto de Venta            : '.($pv !== null ? "PV {$pv}" : 'Ambos (PV 1 y PV 0)'));
        if ($motivo !== null) {
            $this->line("• Motivo filtrado           : Motivo {$motivo}");
        }

        $currentRegistro = null;
        $barra = null;

        $resultado = $servicio->ejecutarEtapaEventosSignificativos(
            $repeticiones,
            $pv,
            $motivo,
            function ($regIdx, $totalRegs, $info) use (&$currentRegistro, &$barra) {
                $claveReg = "mot{$info['motivo']}_pv{$info['pv']}";
                if ($currentRegistro !== $claveReg) {
                    $currentRegistro = $claveReg;
                    $this->newLine();
                    $this->line("<fg=cyan>[{$regIdx}/{$totalRegs}]</> Motivo {$info['motivo']} (<options=bold>PV {$info['pv']}</>) - {$info['descripcion']}");
                    $barra = $this->output->createProgressBar($info['total_iteraciones']);
                    $barra->setFormat(' %current%/%max% [%bar%] %percent:3s%% - <fg=green>✔ %message%</>');
                    $barra->setMessage('0 exitosos');
                    $barra->start();
                }

                if ($barra) {
                    $msgExito = "{$info['exitosos']} exitosos";
                    if ($info['codigo_recepcion']) {
                        $msgExito .= " [Cód: {$info['codigo_recepcion']}]";
                    }
                    $barra->setMessage($msgExito.($info['fallidos'] > 0 ? " | <fg=red>✖ {$info['fallidos']} fallos</>" : ''));
                    $barra->advance();
                }
            }
        );

        $this->newLine(2);
        $this->info('---------------------------------------------------------------');
        $this->info('RESUMEN DE ETAPA V (EVENTOS SIGNIFICATIVOS):');
        $this->line("• Total solicitudes   : {$resultado['total_intentos']}");
        $this->line("• Aprobadas por SIN   : <fg=green>{$resultado['exitosos']}</>");
        $this->line('• Fallidas            : '.($resultado['fallidos'] > 0 ? "<fg=red>{$resultado['fallidos']}</>" : '0'));
        if ($resultado['fallidos'] > 0) {
            $this->newLine();
            $this->warn('Detalle de observaciones del SIN:');
            foreach ($resultado['detalle'] as $det) {
                if ($det['fallidos'] > 0 && ! empty($det['error'])) {
                    $this->line(" <fg=yellow>• Motivo {$det['motivo']} (PV {$det['pv']}):</> {$det['error']}");
                }
            }
        }
        $this->info('---------------------------------------------------------------');

        return $resultado['fallidos'] === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
