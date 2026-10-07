<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head } from "@inertiajs/vue3";
import { Eye, FileText, Newspaper, PenLine, Users } from "lucide-vue-next";

defineOptions({
    layout: AdminLayout,
});

defineProps<{
    stats: {
        total_articles: number;
        published_articles: number;
        draft_articles: number;
        total_users: number;
        total_views: number;
    };
}>();

const numberFormat = new Intl.NumberFormat("id-ID");
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 md:p-6 lg:p-8">
        <div class="mx-auto max-w-[1600px]">
            <!-- Heading -->
            <div>
                <p class="text-sm font-semibold text-orange-500">
                    Lintasara Admin
                </p>

                <h1
                    class="mt-1 text-3xl font-bold tracking-tight text-[#071a36]"
                >
                    Dashboard
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Pantau dan kelola aktivitas portal berita Lintasara.
                </p>
            </div>

            <!-- Statistik -->
            <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-orange-50"
                    >
                        <FileText class="size-5 text-orange-500" />
                    </div>

                    <p class="mt-5 text-sm text-slate-500">Total Artikel</p>

                    <p class="mt-1 text-2xl font-bold text-[#071a36]">
                        {{ numberFormat.format(stats.total_articles) }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-emerald-50"
                    >
                        <Newspaper class="size-5 text-emerald-500" />
                    </div>

                    <p class="mt-5 text-sm text-slate-500">Artikel Terbit</p>

                    <p class="mt-1 text-2xl font-bold text-[#071a36]">
                        {{ numberFormat.format(stats.published_articles) }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-amber-50"
                    >
                        <PenLine class="size-5 text-amber-500" />
                    </div>

                    <p class="mt-5 text-sm text-slate-500">Draft</p>

                    <p class="mt-1 text-2xl font-bold text-[#071a36]">
                        {{ numberFormat.format(stats.draft_articles) }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-sky-50"
                    >
                        <Users class="size-5 text-sky-500" />
                    </div>

                    <p class="mt-5 text-sm text-slate-500">Pengguna</p>

                    <p class="mt-1 text-2xl font-bold text-[#071a36]">
                        {{ numberFormat.format(stats.total_users) }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-violet-50"
                    >
                        <Eye class="size-5 text-violet-500" />
                    </div>

                    <p class="mt-5 text-sm text-slate-500">Total Dilihat</p>

                    <p class="mt-1 text-2xl font-bold text-[#071a36]">
                        {{ numberFormat.format(stats.total_views) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
