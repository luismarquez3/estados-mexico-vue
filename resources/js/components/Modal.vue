<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    titulo?: string;
    subtitulo?: string;
}>();

const emit = defineEmits<{
    'update:open': [valor: boolean];
}>();

const dialogo = ref<HTMLDialogElement | null>(null);

watch(
    () => props.open,
    (abierto) => {
        const elemento = dialogo.value;

        if (!elemento) {
            return;
        }

        if (abierto && !elemento.open) {
            elemento.showModal();

            return;
        }

        if (!abierto && elemento.open) {
            elemento.close();
        }
    },
    { flush: 'post' },
);

onBeforeUnmount(() => {
    dialogo.value?.close();
});

const cerrar = (): void => {
    emit('update:open', false);
};
</script>

<template>
    <dialog
        ref="dialogo"
        class="m-auto w-[min(56rem,calc(100vw-2rem))] rounded-lg border border-[#e3e3e0] bg-white p-0 text-[#1b1b18] backdrop:bg-black/50 dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC]"
        @close="cerrar"
    >
        <div class="flex flex-col gap-4 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h2 v-if="titulo" class="text-lg font-medium">
                        {{ titulo }}
                    </h2>
                    <p
                        v-if="subtitulo"
                        class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        {{ subtitulo }}
                    </p>
                </div>

                <button
                    type="button"
                    class="rounded-md border border-[#e3e3e0] px-2 py-1 text-sm leading-none dark:border-[#3E3E3A]"
                    aria-label="Cerrar"
                    @click="cerrar"
                >
                    ✕
                </button>
            </div>

            <slot />
        </div>
    </dialog>
</template>
