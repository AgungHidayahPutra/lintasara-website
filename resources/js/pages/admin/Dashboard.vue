<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
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

const page = usePage();

const isAdmin = computed(() => {
    const auth = page.props.auth as
        | {
              user?: {
                  role?: string;
              } | null;
          }
        | undefined;

    return auth?.user?.role === "admin";
});

const numberFormat = new Intl.NumberFormat("id-ID");
</script>

<template>
    <Head title="Dashboard" />

    <div class="p-4 md:p-6 lg:p-8">
        <div class="mx-auto max-w-[1600px]">
            <!-- HEADING -->
            <div>
                <p class="text-sm font-semibold text-orange-500">
                    {{ isAdmin ? "Lintasara Admin" : "Lintasara Penulis" }}
                </p>

                <h1
                    class="mt-1 text-3xl font-bold tracking-tight text-[#071a36]"
                >
                    Dashboard
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    {{
                        isAdmin
                            ? "Pantau dan kelola aktivitas portal berita Lintasara."
                            : "Pantau dan kelola artikel yang Anda tulis di Lintasara."
                    }}
                </p>
            </div>

            <!-- STATISTIK -->
            <div
                class="mt-7 grid gap-4 sm:grid-cols-2"
                :class="isAdmin ? 'xl:grid-cols-5' : 'xl:grid-cols-4'"
            >
                <!-- TOTAL ARTIKEL -->
                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-orange-50"
                    >
                        <FileText class="size-5 text-orange-500" />
                    </div>

                    <p class="mt-5 text-sm text-slate-500">
                        {{ isAdmin ? "Total Artikel" : "Artikel Saya" }}
                    </p>

                    <p class="mt-1 text-2xl font-bold text-[#071a36]">
                        {{ numberFormat.format(stats.total_articles) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        {{
                            isAdmin
                                ? "Seluruh artikel"
                                : "Artikel yang Anda buat"
                        }}
                    </p>
                </div>

                <!-- ARTIKEL TERBIT -->
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

                    <p class="mt-1 text-xs text-slate-400">
                        Sudah dipublikasikan
                    </p>
                </div>

                <!-- DRAFT -->
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

                    <p class="mt-1 text-xs text-slate-400">Masih dalam draft</p>
                </div>

                <!-- PENGGUNA: HANYA ADMIN -->
                <div
                    v-if="isAdmin"
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

                    <p class="mt-1 text-xs text-slate-400">
                        Seluruh akun terdaftar
                    </p>
                </div>

                <!-- TOTAL DILIHAT -->
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

                    <p class="mt-1 text-xs text-slate-400">
                        {{
                            isAdmin
                                ? "Total tayangan seluruh artikel"
                                : "Total tayangan artikel Anda"
                        }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
