<script setup lang="ts">
import AdminHeader from "@/components/admin/AdminHeader.vue";
import AdminSidebar from "@/components/admin/AdminSidebar.vue";
import { Toaster } from "@/components/ui/sonner";

interface Breadcrumb {
    title: string;
    href?: string;
}

withDefaults(
    defineProps<{
        breadcrumbs?: Breadcrumb[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);
</script>

<template>
    <div class="flex min-h-screen bg-[#f8f9fb] text-slate-950">
        <AdminSidebar />

        <div class="flex min-w-0 flex-1 flex-col">
            <AdminHeader :breadcrumbs="breadcrumbs" />

            <main class="min-w-0 flex-1">
                <slot />
            </main>
        </div>

        <Toaster />
    </div>
</template>
