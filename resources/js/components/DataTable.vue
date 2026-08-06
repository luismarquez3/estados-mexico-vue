<script setup lang="ts" generic="TData extends RowData">
import type { RowData } from '@tanstack/vue-table';
import { FlexRender, useTable } from '@tanstack/vue-table';
import { computed, toRef } from 'vue';
import { features, formatoNumerico } from '@/lib/tabla';
import type { Columnas } from '@/lib/tabla';

const props = withDefaults(
    defineProps<{
        data: TData[];
        columns: Columnas<TData>;
        /** Ids de columna que se alinean a la derecha con cifras tabulares. */
        columnasNumericas?: string[];
        pageSize?: number;
        placeholderBusqueda?: string;
        filasClicables?: boolean;
        cargando?: boolean;
    }>(),
    {
        columnasNumericas: () => [],
        pageSize: 10,
        placeholderBusqueda: 'Buscar…',
        filasClicables: false,
        cargando: false,
    },
);

const emit = defineEmits<{
    filaSeleccionada: [fila: TData];
}>();

const table = useTable({
    features,
    columns: props.columns,
    data: toRef(props, 'data'),
    globalFilterFn: 'includesString',
    initialState: { pagination: { pageIndex: 0, pageSize: props.pageSize } },
});

const busqueda = computed<string>({
    get: () => (table.atoms.globalFilter.get() as string | undefined) ?? '',
    set: (valor) => table.setGlobalFilter(valor),
});

const paginaActual = computed(() => table.atoms.pagination.get().pageIndex + 1);

const tamanoPagina = computed<number>({
    get: () => table.atoms.pagination.get().pageSize,
    set: (valor) => table.setPageSize(valor),
});

const esColumnaNumerica = (columnId: string): boolean =>
    props.columnasNumericas.includes(columnId);

const seleccionar = (fila: TData): void => {
    if (!props.filasClicables) {
        return;
    }

    emit('filaSeleccionada', fila);
};
</script>

<template>
    <div class="flex flex-col gap-4">
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <label class="flex flex-col gap-1 text-sm">
                <span class="text-[#706f6c] dark:text-[#A1A09A]">Búsqueda</span>
                <input
                    v-model="busqueda"
                    type="search"
                    :placeholder="placeholderBusqueda"
                    class="w-full rounded-md border border-[#e3e3e0] bg-transparent px-3 py-1.5 outline-none focus:border-[#1b1b18] sm:w-72 dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                />
            </label>

            <label class="flex items-center gap-2 text-sm">
                <span class="text-[#706f6c] dark:text-[#A1A09A]"
                    >Por página</span
                >
                <select
                    v-model.number="tamanoPagina"
                    class="rounded-md border border-[#e3e3e0] bg-transparent px-2 py-1.5 outline-none dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                    <option :value="50">50</option>
                </select>
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr
                        v-for="grupo in table.getHeaderGroups()"
                        :key="grupo.id"
                        class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]"
                    >
                        <th
                            v-for="header in grupo.headers"
                            :key="header.id"
                            scope="col"
                            :aria-sort="
                                header.column.getIsSorted() === 'asc'
                                    ? 'ascending'
                                    : header.column.getIsSorted() === 'desc'
                                      ? 'descending'
                                      : 'none'
                            "
                            class="px-3 py-2 font-medium"
                            :class="
                                esColumnaNumerica(header.column.id)
                                    ? 'text-right'
                                    : 'text-left'
                            "
                        >
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-sm hover:text-[#706f6c] dark:hover:text-[#A1A09A]"
                                @click="header.column.toggleSorting()"
                            >
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :header="header"
                                />
                                <span
                                    class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    aria-hidden="true"
                                >
                                    {{
                                        header.column.getIsSorted() === 'asc'
                                            ? '▲'
                                            : header.column.getIsSorted() ===
                                                'desc'
                                              ? '▼'
                                              : '↕'
                                    }}
                                </span>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="cargando" class="animate-pulse">
                        <td :colspan="columns.length" class="px-3 py-2">
                            <div class="flex flex-col gap-2">
                                <div
                                    v-for="linea in 5"
                                    :key="linea"
                                    class="h-4 rounded bg-[#e3e3e0] dark:bg-[#3E3E3A]"
                                />
                            </div>
                        </td>
                    </tr>

                    <template v-else>
                        <tr
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="border-b border-[#e3e3e0] last:border-0 dark:border-[#3E3E3A]"
                            :class="
                                filasClicables
                                    ? 'cursor-pointer hover:bg-[#FDFDFC] dark:hover:bg-[#0a0a0a]'
                                    : ''
                            "
                            :tabindex="filasClicables ? 0 : undefined"
                            @click="seleccionar(row.original)"
                            @keydown.enter="seleccionar(row.original)"
                            @keydown.space.prevent="seleccionar(row.original)"
                        >
                            <td
                                v-for="cell in row.getAllCells()"
                                :key="cell.id"
                                class="px-3 py-2"
                                :class="
                                    esColumnaNumerica(cell.column.id)
                                        ? 'text-right tabular-nums'
                                        : 'text-left'
                                "
                            >
                                <FlexRender :cell="cell" />
                            </td>
                        </tr>

                        <tr v-if="table.getRowModel().rows.length === 0">
                            <td
                                :colspan="columns.length"
                                class="px-3 py-8 text-center text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                <template v-if="busqueda">
                                    Sin resultados para «{{ busqueda }}».
                                </template>
                                <template v-else> Sin registros. </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Página {{ paginaActual }} de
                {{ Math.max(table.getPageCount(), 1) }} ·
                {{ formatoNumerico.format(table.getRowCount()) }} registros
            </p>

            <div class="flex gap-2">
                <button
                    type="button"
                    class="rounded-md border border-[#e3e3e0] px-3 py-1.5 text-sm disabled:opacity-40 dark:border-[#3E3E3A]"
                    :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()"
                >
                    Anterior
                </button>
                <button
                    type="button"
                    class="rounded-md border border-[#e3e3e0] px-3 py-1.5 text-sm disabled:opacity-40 dark:border-[#3E3E3A]"
                    :disabled="!table.getCanNextPage()"
                    @click="table.nextPage()"
                >
                    Siguiente
                </button>
            </div>
        </div>
    </div>
</template>
