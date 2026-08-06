<?php

namespace App\Http\Controllers;

use App\Models\Estado;
use App\Services\InegiService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EstadosController extends Controller
{
    public function index(): Response
    {
        $estados = Estado::orderBy('cve_ent')->get(['cve_ent', 'nomgeo', 'pob_total']);

        return Inertia::render('Index', [
            'estados' => $estados,
        ]);
    }

    /**
     * Devuelve los municipios del estado consultados a la API del INEGI.
     */
    public function municipios(Estado $estado, InegiService $inegi): JsonResponse
    {
        try {
            $municipios = $inegi->fetchMunicipios($estado->cve_ent);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'mensaje' => 'No se pudieron consultar los municipios en el INEGI.',
            ], 502);
        }

        return response()->json([
            'estado' => $estado->only(['cve_ent', 'nomgeo']),
            'municipios' => collect($municipios)->map(fn (array $municipio): array => [
                'cve_mun' => $municipio['cve_mun'],
                'nomgeo' => $municipio['nomgeo'],
                'nom_cab' => $municipio['nom_cab'] ?? null,
                'pob_total' => (int) $municipio['pob_total'],
            ])->all(),
        ]);
    }
}
