<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { ChevronRight, Menu, Search, X } from "lucide-vue-next";
import { onMounted, ref } from "vue";

import AdminLogo from "./AdminLogo.vue";
import AdminUserMenu from "./AdminUserMenu.vue";

interface Breadcrumb {
    title: string;
    href?: string;
}

defineProps<{
    breadcrumbs?: Breadcrumb[];
}>();

const page = usePage();

const mobileOpen = ref(false);

/*
|--------------------------------------------------------------------------
| Client Ready
|--------------------------------------------------------------------------
|
| Teleport tidak dirender saat SSR.
| Ini mencegah hydration mismatch antara server dan browser.
|
*/

const clientReady = ref(false);

onMounted(() => {
    clientReady.value = true;
});

/*
|--------------------------------------------------------------------------
| Active Menu
|--------------------------------------------------------------------------
*/

function isActive(href: string) {
    const url = page.url.split("?")[0];

    if (href === "/admin") {
        return url === "/admin";
    }

    return url.startsWith(href);
}

/*
|--------------------------------------------------------------------------
| Mobile Menu
|--------------------------------------------------------------------------
*/

const menus = [
    {
        title: "Dashboard",
        href: "/admin",
    },
    {
        title: "Artikel",
        href: "/admin/articles",
    },
];
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-[76px] shrink-0 items-center border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl sm:px-6 lg:px-8"
    >
        <!-- MOBILE MENU BUTTON -->
        <button
            type="button"
            aria-label="Buka sidebar"
            class="mr-3 flex size-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 lg:hidden"
            @click="mobileOpen = true"
        >
            <Menu class="size-5" />
        </button>

        <!-- BREADCRUMB -->
        <div class="min-w-0 flex-1">
            <div
                v-if="breadcrumbs?.length"
                class="flex min-w-0 items-center gap-2"
            >
                <template
                    v-for="(item, index) in breadcrumbs"
                    :key="`${item.title}-${index}`"
                >
                    <ChevronRight
                        v-if="index > 0"
                        class="size-3.5 shrink-0 text-slate-300"
                    />

                    <Link
                        v-if="item.href && index !== breadcrumbs.length - 1"
                        :href="item.href"
                        class="truncate text-sm text-slate-400 transition hover:text-orange-500"
                    >
                        {{ item.title }}
                    </Link>

                    <span
                        v-else
                        class="truncate text-sm font-semibold text-slate-700"
                    >
                        {{ item.title }}
                    </span>
                </template>
            </div>

            <span v-else class="text-sm font-semibold text-slate-700">
                Lintasara
            </span>
        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                aria-label="Cari"
                class="hidden h-10 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 text-sm text-slate-400 transition hover:border-slate-300 hover:bg-slate-50 md:flex"
            >
                <Search class="size-4" />

                <span class="hidden xl:inline"> Cari... </span>
            </button>

            <div class="mx-1 hidden h-7 w-px bg-slate-200 sm:block" />

            <AdminUserMenu />
        </div>
    </header>

    <!--
    |--------------------------------------------------------------------------
    | MOBILE SIDEBAR
    |--------------------------------------------------------------------------
    |
    | Hanya dirender setelah Vue selesai mount di browser.
    |
    -->

    <Teleport v-if="clientReady" to="body">
        <div v-if="mobileOpen" class="fixed inset-0 z-[100] lg:hidden">
            <!-- OVERLAY -->
            <button
                type="button"
                aria-label="Tutup sidebar"
                class="absolute inset-0 cursor-pointer bg-slate-950/40 backdrop-blur-[2px]"
                @click="mobileOpen = false"
            />

            <!-- SIDEBAR -->
            <aside
                class="absolute inset-y-0 left-0 flex w-[290px] flex-col bg-white shadow-2xl"
            >
                <!-- HEADER -->
                <div
                    class="flex h-[76px] items-center justify-between border-b border-slate-100 px-5"
                >
                    <AdminLogo />

                    <button
                        type="button"
                        aria-label="Tutup sidebar"
                        class="flex size-9 cursor-pointer items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="mobileOpen = false"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <!-- MENU -->
                <div class="flex-1 overflow-y-auto p-4">
                    <p
                        class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                    >
                        Menu
                    </p>

                    <nav class="space-y-1">
                        <Link
                            v-for="item in menus"
                            :key="item.href"
                            :href="item.href"
                            class="flex h-11 cursor-pointer items-center rounded-xl px-3 text-sm font-medium transition"
                            :class="
                                isActive(item.href)
                                    ? 'bg-orange-50 text-orange-600'
                                    : 'text-slate-600 hover:bg-slate-50'
                            "
                            @click="mobileOpen = false"
                        >
                            {{ item.title }}
                        </Link>
                    </nav>
                </div>
            </aside>
        </div>
    </Teleport>
</template>
