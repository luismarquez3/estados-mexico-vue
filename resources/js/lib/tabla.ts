import type { ColumnDef, RowData } from '@tanstack/vue-table';
import {
    columnFilteringFeature,
    createColumnHelper,
    createFilteredRowModel,
    createPaginatedRowModel,
    createSortedRowModel,
    filterFn_includesString,
    globalFilteringFeature,
    rowPaginationFeature,
    rowSortingFeature,
    sortFn_basic,
    sortFn_text,
    tableFeatures,
} from '@tanstack/vue-table';

/**
 * Features compartidas por todas las tablas de la aplicación.
 *
 * En TanStack Table v9 cada capacidad se registra explícitamente junto a su row
 * model; si falta el feature, su estado y sus métodos no se instalan.
 */
export const features = tableFeatures({
    columnFilteringFeature,
    globalFilteringFeature,
    filteredRowModel: createFilteredRowModel(),
    filterFns: { includesString: filterFn_includesString },
    rowSortingFeature,
    sortedRowModel: createSortedRowModel(),
    sortFns: { text: sortFn_text, basic: sortFn_basic },
    rowPaginationFeature,
    paginatedRowModel: createPaginatedRowModel(),
});

/**
 * Definiciones de columna ligadas al conjunto de features compartido.
 */
export type Columnas<TData extends RowData> = ColumnDef<
    typeof features,
    TData,
    any
>[];

export const crearHelperDeColumnas = <TData extends RowData>() =>
    createColumnHelper<typeof features, TData>();

export const formatoNumerico = new Intl.NumberFormat('es-MX');
