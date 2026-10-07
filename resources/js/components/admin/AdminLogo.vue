<script setup lang="ts">
withDefaults(
    defineProps<{
        collapsed?: boolean;
    }>(),
    {
        collapsed: false,
    },
);
</script>

<template>
    <div class="flex min-w-0 items-center">
        <!-- Logo horizontal -->
        <img
            v-if="!collapsed"
            src="/images/brand/lintasara-logo.png"
            alt="Lintasara"
            class="h-[72px] w-[205px] object-contain object-left"
        />

        <!-- Logo simbol ketika sidebar collapse -->
        <img
            v-else
            src="/images/brand/lintasara-symbol.png"
            alt="Lintasara"
            class="size-10 object-contain"
        />
    </div>
</template>
