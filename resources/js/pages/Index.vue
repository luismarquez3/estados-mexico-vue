<script setup lang="ts">
import { Head, useHttp } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import Modal from '@/components/Modal.vue';
import type { Columnas } from '@/lib/tabla';
import { crearHelperDeColumnas, formatoNumerico } from '@/lib/tabla';
import rutasEstados from '@/routes/estados';
import type {
    Estado,
    Municipio,
    RespuestaErrorInegi,
    RespuestaMunicipios,
} from '@/types';

const props = defineProps<{
    estados: Estado[];
}>();

const helperEstado = crearHelperDeColumnas<Estado>();

const columnasEstado: Columnas<Estado> = helperEstado.columns([
    helperEstado.accessor('cve_ent', { header: 'Clave', sortFn: 'text' }),
    helperEstado.accessor('nomgeo', { header: 'Estado', sortFn: 'text' }),
    helperEstado.accessor('pob_total', {
        header: 'Población total',
        sortFn: 'basic',
        cell: ({ getValue }) => formatoNumerico.format(getValue()),
    }),
]);

const helperMunicipio = crearHelperDeColumnas<Municipio>();

const columnasMunicipio: Columnas<Municipio> = helperMunicipio.columns([
    helperMunicipio.accessor('cve_mun', { header: 'Clave', sortFn: 'text' }),
    helperMunicipio.accessor('nomgeo', { header: 'Municipio', sortFn: 'text' }),
    helperMunicipio.accessor('pob_total', {
        header: 'Población total',
        sortFn: 'basic',
        cell: ({ getValue }) => formatoNumerico.format(getValue()),
    }),
]);

const modalAbierto = ref(false);
const estadoSeleccionado = ref<Estado | null>(null);
const municipios = ref<Municipio[]>([]);
const mensajeError = ref<string | null>(null);

const peticion = useHttp<Record<string, never>, RespuestaMunicipios>();

const subtituloModal = computed(() => {
    if (mensajeError.value || peticion.processing) {
        return undefined;
    }

    return `${formatoNumerico.format(municipios.value.length)} municipios`;
});

/**
 * Lee el mensaje de error que devuelve el controlador cuando el INEGI falla.
 */
const leerMensajeDeError = (cuerpo: string): string => {
    try {
        const contenido = JSON.parse(cuerpo) as RespuestaErrorInegi;

        return contenido.mensaje ?? 'No se pudieron consultar los municipios.';
    } catch {
        return 'No se pudieron consultar los municipios.';
    }
};

const abrirMunicipios = (estado: Estado): void => {
    estadoSeleccionado.value = estado;
    municipios.value = [];
    mensajeError.value = null;
    modalAbierto.value = true;

    peticion
        .get(rutasEstados.municipios.url(estado), {
            onSuccess: (respuesta) => {
                // Descarta respuestas de un estado que ya no es el seleccionado.
                if (
                    respuesta.estado.cve_ent !==
                    estadoSeleccionado.value?.cve_ent
                ) {
                    return;
                }

                municipios.value = respuesta.municipios;
            },
            onHttpException: (respuesta) => {
                mensajeError.value = leerMensajeDeError(respuesta.data);
            },
            onNetworkError: () => {
                mensajeError.value = 'No se pudo conectar con el servidor.';
            },
        })
        .catch(() => {
            // Los callbacks ya registraron el mensaje; se absorbe el rechazo
            // para no dejar una promesa sin manejar.
        });
};
</script>

<template>
    <Head title="Estados" />

    <div
        class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <div class="mx-auto flex max-w-3xl flex-col gap-6">
            <header class="flex flex-col gap-1">
                <h1 class="text-xl font-medium">Estados de México</h1>
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ formatoNumerico.format(props.estados.length) }} entidades
                    federativas · haz clic en una fila para ver sus municipios
                </p>
            </header>

            <div
                class="rounded-lg border border-[#e3e3e0] bg-white p-4 dark:border-[#3E3E3A] dark:bg-[#161615]"
            >
                <DataTable
                    :data="props.estados"
                    :columns="columnasEstado"
                    :columnas-numericas="['pob_total']"
                    placeholder-busqueda="Clave, estado o población…"
                    filas-clicables
                    @fila-seleccionada="abrirMunicipios"
                />
            </div>
        </div>

        <Modal
            v-model:open="modalAbierto"
            :titulo="
                estadoSeleccionado
                    ? `Municipios de ${estadoSeleccionado.nomgeo}`
                    : 'Municipios'
            "
            :subtitulo="subtituloModal"
        >
            <p
                v-if="mensajeError"
                role="alert"
                class="rounded-md border border-[#e3e3e0] px-3 py-8 text-center text-sm dark:border-[#3E3E3A]"
            >
                {{ mensajeError }}
            </p>

            <DataTable
                v-else
                :data="municipios"
                :columns="columnasMunicipio"
                :columnas-numericas="['pob_total']"
                placeholder-busqueda="Clave, municipio o población…"
                :cargando="peticion.processing"
            />
        </Modal>
    </div>
</template>
