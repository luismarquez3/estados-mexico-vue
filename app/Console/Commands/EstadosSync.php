<?php

namespace App\Console\Commands;

use App\Models\Estado;
use App\Services\InegiService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('estados:sync')]
#[Description('Este comando mandará llamar el servicio de INEGI para llenar la base de datos, es primordial que existan las migraciones y hayan sido ejecutadas')]
class EstadosSync extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(InegiService $inegi): int
    {
        try {
            $estados = $inegi->fetchEstados();
        } catch (Throwable $e) {
            $this->error("No se pudo consultar la API del INEGI: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($estados === []) {
            $this->warn('La API del INEGI no devolvió estados.');

            return self::FAILURE;
        }

        $creados = 0;
        $actualizados = 0;

        foreach ($estados as $estado) {
            $registro = Estado::updateOrCreate(
                ['cve_ent' => $estado['cve_ent']],
                [
                    'nomgeo' => $estado['nomgeo'],
                    'pob_total' => (int) $estado['pob_total'],
                ]
            );

            $registro->wasRecentlyCreated ? $creados++ : $actualizados++;
        }

        $this->info("Sincronización terminada: {$creados} creados, {$actualizados} actualizados.");

        return self::SUCCESS;
    }
}
