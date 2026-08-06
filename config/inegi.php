<?php

return [
    'inegi_api_estados' => env('INEGI_API_ESTADOS', 'https://gaia.inegi.org.mx/wscatgeo/v2/mgee/'),

    'inegi_api_municipios' => env('INEGI_API_MUNICIPIOS', 'https://gaia.inegi.org.mx/wscatgeo/v2/mgem/'),

    /**
     * Segundos que se cachea el catálogo de municipios de un estado.
     */
    'cache_ttl_municipios' => (int) env('INEGI_CACHE_TTL_MUNICIPIOS', 86400),
];
