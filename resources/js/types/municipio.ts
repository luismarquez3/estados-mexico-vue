export type Municipio = {
    cve_mun: string;
    nomgeo: string;
    nom_cab: string | null;
    pob_total: number;
};

export type RespuestaMunicipios = {
    estado: {
        cve_ent: string;
        nomgeo: string;
    };
    municipios: Municipio[];
};

export type RespuestaErrorInegi = {
    mensaje?: string;
};
