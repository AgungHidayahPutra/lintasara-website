<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    ArrowLeft,
    ChevronRight,
    ShieldCheck,
    UserRound,
} from "lucide-vue-next";

import { useCurrentUrl } from "@/composables/useCurrentUrl";
import { toUrl } from "@/lib/utils";
import { edit as editProfile } from "@/routes/profile";
import { edit as editSecurity } from "@/routes/security";

const sidebarNavItems = [
    {
        title: "Profil",
        description: "Informasi akun redaksi",
        href: editProfile(),
        icon: UserRound,
    },
    {
        title: "Keamanan",
        description: "Kata sandi dan verifikasi",
        href: editSecurity(),
        icon: ShieldCheck,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();

const currentSection = computed(() => {
    return (
        sidebarNavItems.find((item) => isCurrentOrParentUrl(item.href)) ??
        sidebarNavItems[0]
    );
});
</script>

<template>
    <div class="min-h-full bg-[#F8FAFC] px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-7xl space-y-7">
            <!-- Header -->
            <header
                class="relative overflow-hidden rounded-2xl bg-[#071A36] px-6 py-8 text-white sm:px-8"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-32 size-80 rounded-full border border-white/10"
                ></div>

                <div
                    class="pointer-events-none absolute -right-12 -top-16 size-56 rounded-full border border-white/10"
                ></div>

                <div class="relative z-10">
                    <Link
                        href="/admin"
                        class="mb-6 inline-flex items-center gap-2 text-xs font-medium text-slate-300 transition hover:text-orange-400"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Dashboard
                    </Link>

                    <div
                        class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-orange-400"
                    >
                        <span class="size-2 rounded-full bg-orange-500"></span>
                        LINTASARA WORKSPACE
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        Pengaturan Akun
                    </h1>

                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">
                        Kelola informasi pribadi dan keamanan akun dalam satu
                        ruang pengaturan redaksi.
                    </p>
                </div>
            </header>

            <!-- Settings content -->
            <div
                class="grid items-start gap-6 lg:grid-cols-[250px_minmax(0,1fr)]"
            >
                <!-- Sidebar settings -->
                <aside
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-5">
                        <p
                            class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400"
                        >
                            PENGATURAN AKUN
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#071A36]">
                            Menu Pengaturan
                        </p>
                    </div>

                    <nav class="space-y-1 p-3" aria-label="Pengaturan akun">
                        <Link
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            :href="item.href"
                            :aria-current="
                                isCurrentOrParentUrl(item.href)
                                    ? 'page'
                                    : undefined
                            "
                            :class="[
                                'group flex items-center gap-3 rounded-xl px-3 py-3 transition',
                                isCurrentOrParentUrl(item.href)
                                    ? 'bg-orange-50 text-[#071A36]'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-[#071A36]',
                            ]"
                        >
                            <div
                                :class="[
                                    'flex size-10 shrink-0 items-center justify-center rounded-xl transition',
                                    isCurrentOrParentUrl(item.href)
                                        ? 'bg-orange-500 text-white'
                                        : 'bg-slate-100 text-slate-500 group-hover:bg-orange-50 group-hover:text-orange-500',
                                ]"
                            >
                                <component
                                    :is="item.icon"
                                    class="size-[18px]"
                                />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold">
                                    {{ item.title }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ item.description }}
                                </p>
                            </div>

                            <ChevronRight
                                class="size-4 shrink-0 text-slate-300"
                            />
                        </Link>
                    </nav>
                </aside>

                <!-- Content -->
                <main class="min-w-0">
                    <div class="mb-5 flex items-center gap-2 px-1">
                        <span class="text-xs text-slate-400"> Pengaturan </span>

                        <ChevronRight class="size-3.5 text-slate-300" />

                        <span class="text-xs font-semibold text-[#071A36]">
                            {{ currentSection.title }}
                        </span>
                    </div>

                    <slot />
                </main>
            </div>

            <!-- Footer -->
            <footer
                class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-5 text-xs text-slate-400"
            >
                <span>© {{ new Date().getFullYear() }} Lintasara</span>
                <span>Pengaturan Akun Redaksi</span>
            </footer>
        </div>
    </div>
</template>
