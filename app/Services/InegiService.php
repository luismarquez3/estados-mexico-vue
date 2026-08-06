<?php

namespace App\Services;

use App\Exceptions\InegiException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InegiService
{
    /**
     * Obtiene el catálogo de estados desde la API del INEGI.
     *
     */
    public function fetchEstados(): array
    {
        return $this->get(config('inegi.inegi_api_estados'));
    }

    /**
     * Obtiene el catálogo de municipios de un estado, cacheado por clave de entidad.
     *
     */
    public function fetchMunicipios(string $claveEstado): array
    {
        return Cache::remember(
            "inegi.municipios.{$claveEstado}",
            config('inegi.cache_ttl_municipios'),
            fn (): array => $this->get(config('inegi.inegi_api_municipios').$claveEstado)
        );
    }

    /**
     * Ejecuta la petición y devuelve el arreglo `datos` de la respuesta.
     */
    private function get(string $url): array
    {
        $payload = Http::acceptJson()
            ->timeout(30)
            ->retry(3, 500)
            ->get($url)
            ->throw()
            ->json();

        if (! is_array($payload) || ! isset($payload['datos'])) {
            throw new InegiException($payload['mensaje'] ?? 'El INEGI no devolvió datos para esta consulta.');
        }

        return $payload['datos'];
    }
}
