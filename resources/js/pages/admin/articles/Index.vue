<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    Archive,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock3,
    FileText,
    Plus,
    RotateCcw,
    Search,
    Pencil,
    Eye,
    Trash2,
    TriangleAlert,
    X,
} from "lucide-vue-next";
import { ref, watch } from "vue";

interface Category {
    id: number;
    name: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    status: "draft" | "published" | "archived";
    is_featured: boolean;
    is_breaking: boolean;
    published_at: string | null;
    created_at: string;

    category: Category | null;

    region: {
        id: number;
        name: string;
    } | null;

    author: {
        id: number;
        name: string;
    } | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface ArticlesPagination {
    data: Article[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

const props = defineProps<{
    articles: ArticlesPagination;
    categories: Category[];

    stats: {
        total: number;
        published: number;
        draft: number;
        archived: number;
    };

    filters: {
        search: string;
        status: string;
        category: number | null;
    };
}>();

defineOptions({
    layout: AdminLayout,
});

const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "");
const category = ref(
    props.filters.category ? String(props.filters.category) : "",
);

const deleteModalOpen = ref(false);
const articleToDelete = ref<Article | null>(null);
const deleting = ref(false);

function openDeleteModal(article: Article) {
    articleToDelete.value = article;
    deleteModalOpen.value = true;
}

function closeDeleteModal() {
    if (deleting.value) {
        return;
    }

    deleteModalOpen.value = false;
    articleToDelete.value = null;
}

function deleteArticle() {
    if (!articleToDelete.value || deleting.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/articles/${articleToDelete.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            deleteModalOpen.value = false;
            articleToDelete.value = null;
        },

        onFinish: () => {
            deleting.value = false;
        },
    });
}

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

function applyFilters() {
    router.get(
        "/admin/articles",
        {
            search: search.value || undefined,
            status: status.value || undefined,
            category: category.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

watch(search, () => {
    clearTimeout(searchTimeout);

    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
});

watch([status, category], () => {
    applyFilters();
});

function resetFilters() {
    search.value = "";
    status.value = "";
    category.value = "";

    router.get(
        "/admin/articles",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function statusLabel(value: Article["status"]) {
    switch (value) {
        case "published":
            return "Terbit";

        case "archived":
            return "Arsip";

        default:
            return "Draft";
    }
}

function statusClass(value: Article["status"]) {
    switch (value) {
        case "published":
            return "bg-emerald-50 text-emerald-700 ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400";

        case "archived":
            return "bg-slate-100 text-slate-600 ring-slate-500/10 dark:bg-slate-500/10 dark:text-slate-300";

        default:
            return "bg-amber-50 text-amber-700 ring-amber-600/10 dark:bg-amber-500/10 dark:text-amber-400";
    }
}

function formatDate(date: string | null) {
    if (!date) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(date));
}
</script>

<template>
    <Head title="Artikel" />

    <div
        class="min-h-full flex-1 bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background"
    >
        <div class="mx-auto flex max-w-[1600px] flex-col gap-6">
            <!-- HERO HEADER -->
            <section
                class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white px-6 py-7 shadow-sm md:px-8 dark:border-border dark:bg-card"
            >
                <!-- Decorative background -->
                <div
                    class="pointer-events-none absolute -right-24 -top-32 size-80 rounded-full bg-orange-100/60 blur-3xl dark:bg-orange-500/5"
                ></div>

                <div
                    class="pointer-events-none absolute right-40 top-10 size-32 rounded-full bg-amber-100/40 blur-3xl dark:bg-amber-500/5"
                ></div>

                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-600 dark:bg-orange-500/10 dark:text-orange-400"
                        >
                            <FileText class="size-3.5" />
                            Manajemen Konten
                        </div>

                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-950 md:text-4xl dark:text-white"
                        >
                            Artikel
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 md:text-base dark:text-muted-foreground"
                        >
                            Kelola berita dan artikel Lintasara. Tulis, edit,
                            dan terbitkan informasi terbaru untuk pembaca.
                        </p>
                    </div>

                    <Link
                        href="/admin/articles/create"
                        class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition-all hover:-translate-y-0.5 hover:bg-orange-600 hover:shadow-xl hover:shadow-orange-500/25"
                    >
                        <Plus class="size-5" />
                        Tambah Artikel
                    </Link>
                </div>
            </section>

            <!-- STATISTICS -->
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <!-- Total -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 transition group-hover:scale-105 dark:bg-orange-500/10"
                        >
                            <FileText class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Total Artikel
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.total }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Seluruh artikel
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Published -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-500 transition group-hover:scale-105 dark:bg-emerald-500/10"
                        >
                            <CheckCircle2 class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Artikel Terbit
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.published }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Sudah dipublikasi
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Draft -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 transition group-hover:scale-105 dark:bg-amber-500/10"
                        >
                            <Clock3 class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Draft
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.draft }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Masih dalam draft
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Archive -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-500 transition group-hover:scale-105 dark:bg-sky-500/10"
                        >
                            <Archive class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Arsip
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.archived }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Tidak ditampilkan
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FILTER -->
            <section
                class="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm dark:border-border dark:bg-card"
            >
                <div
                    class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.5fr_1fr_1fr_auto]"
                >
                    <!-- Search -->
                    <div class="relative">
                        <Search
                            class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari judul atau slug..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                        />
                    </div>

                    <!-- Status -->
                    <select
                        v-model="status"
                        class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                    >
                        <option value="">Semua status</option>

                        <option value="draft">Draft</option>

                        <option value="published">Terbit</option>

                        <option value="archived">Arsip</option>
                    </select>

                    <!-- Category -->
                    <select
                        v-model="category"
                        class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                    >
                        <option value="">Semua kategori</option>

                        <option
                            v-for="item in categories"
                            :key="item.id"
                            :value="String(item.id)"
                        >
                            {{ item.name }}
                        </option>
                    </select>

                    <!-- Reset -->
                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium transition hover:bg-slate-50 dark:border-border dark:bg-background dark:hover:bg-muted"
                        @click="resetFilters"
                    >
                        <RotateCcw class="size-4" />
                        Reset
                    </button>
                </div>
            </section>

            <!-- TABLE -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/70 dark:border-border dark:bg-muted/30"
                            >
                                <th
                                    class="min-w-[300px] px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Artikel
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Kategori
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Wilayah
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Penulis
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Status
                                </th>

                                <th
                                    class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Tanggal
                                </th>

                                <th
                                    class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="article in articles.data"
                                :key="article.id"
                                class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50/70 dark:border-border dark:hover:bg-muted/20"
                            >
                                <!-- Article -->
                                <td class="px-5 py-4">
                                    <div
                                        class="max-w-[380px] font-semibold text-slate-900 dark:text-foreground"
                                    >
                                        {{ article.title }}
                                    </div>

                                    <div
                                        class="mt-1 truncate text-xs text-slate-400"
                                    >
                                        /{{ article.slug }}
                                    </div>

                                    <div
                                        v-if="
                                            article.is_featured ||
                                            article.is_breaking
                                        "
                                        class="mt-2 flex flex-wrap gap-1.5"
                                    >
                                        <span
                                            v-if="article.is_featured"
                                            class="rounded-md bg-violet-50 px-2 py-1 text-[11px] font-semibold text-violet-600 dark:bg-violet-500/10 dark:text-violet-400"
                                        >
                                            Pilihan
                                        </span>

                                        <span
                                            v-if="article.is_breaking"
                                            class="rounded-md bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600 dark:bg-red-500/10 dark:text-red-400"
                                        >
                                            Breaking
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ article.category?.name ?? "-" }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ article.region?.name ?? "Nasional" }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ article.author?.name ?? "-" }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="statusClass(article.status)"
                                    >
                                        {{ statusLabel(article.status) }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-slate-500"
                                >
                                    {{
                                        formatDate(
                                            article.published_at ??
                                                article.created_at,
                                        )
                                    }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1">
                                        <Link
                                            :href="`/admin/articles/${article.id}`"
                                            title="Lihat artikel"
                                            class="flex size-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-muted dark:hover:text-white"
                                        >
                                            <Eye class="size-4" />
                                        </Link>

                                        <Link
                                            :href="`/admin/articles/${article.id}/edit`"
                                            title="Edit artikel"
                                            class="flex size-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-orange-50 hover:text-orange-600 dark:hover:bg-orange-500/10"
                                        >
                                            <Pencil class="size-4" />
                                        </Link>

                                        <button
                                            type="button"
                                            title="Hapus artikel"
                                            class="flex size-9 cursor-pointer items-center justify-center rounded-lg text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                            @click="openDeleteModal(article)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY STATE -->
                            <tr v-if="articles.data.length === 0">
                                <td colspan="7">
                                    <div
                                        class="flex min-h-[360px] flex-col items-center justify-center px-6 py-16 text-center"
                                    >
                                        <div
                                            class="relative flex size-20 items-center justify-center rounded-3xl bg-slate-50 dark:bg-muted"
                                        >
                                            <FileText
                                                class="size-9 text-slate-300 dark:text-slate-500"
                                            />

                                            <div
                                                class="absolute -bottom-2 -right-2 flex size-8 items-center justify-center rounded-full bg-orange-500 text-white shadow-lg shadow-orange-500/20"
                                            >
                                                <Plus class="size-4" />
                                            </div>
                                        </div>

                                        <h3
                                            class="mt-6 text-base font-semibold text-slate-900 dark:text-white"
                                        >
                                            Belum ada artikel
                                        </h3>

                                        <p
                                            class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                                        >
                                            Mulai tulis artikel pertama untuk
                                            Lintasara dan bagikan informasi
                                            menarik kepada pembaca.
                                        </p>

                                        <Link
                                            href="/admin/articles/create"
                                            class="mt-6 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:-translate-y-0.5 hover:bg-orange-600"
                                        >
                                            <Plus class="size-4" />
                                            Tambah Artikel
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TABLE FOOTER -->
                <div
                    class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-border"
                >
                    <p class="text-sm text-slate-500">
                        <template v-if="articles.total > 0">
                            Menampilkan
                            <span
                                class="font-medium text-slate-700 dark:text-foreground"
                            >
                                {{ articles.from }}–{{ articles.to }}
                            </span>
                            dari
                            <span
                                class="font-medium text-slate-700 dark:text-foreground"
                            >
                                {{ articles.total }}
                            </span>
                            artikel
                        </template>

                        <template v-else> Belum ada data artikel </template>
                    </p>

                    <!-- Pagination -->
                    <div
                        v-if="articles.last_page > 1"
                        class="flex items-center gap-1"
                    >
                        <Link
                            v-for="link in articles.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            preserve-scroll
                            preserve-state
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border border-slate-200 px-3 text-sm transition dark:border-border"
                            :class="[
                                link.active
                                    ? 'border-orange-200 bg-orange-50 font-semibold text-orange-600 dark:border-orange-500/20 dark:bg-orange-500/10'
                                    : 'bg-white hover:bg-slate-50 dark:bg-background dark:hover:bg-muted',
                                !link.url
                                    ? 'pointer-events-none opacity-40'
                                    : '',
                            ]"
                        >
                            <ChevronLeft
                                v-if="link.label.includes('Previous')"
                                class="size-4"
                            />

                            <ChevronRight
                                v-else-if="link.label.includes('Next')"
                                class="size-4"
                            />

                            <span v-else v-html="link.label" />
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- =========================================================
     DELETE ARTICLE MODAL
========================================================== -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="deleteModalOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <!-- BACKDROP -->
                <button
                    type="button"
                    aria-label="Tutup modal"
                    class="absolute inset-0 cursor-default bg-slate-950/50 backdrop-blur-[2px]"
                    @click="closeDeleteModal"
                />

                <!-- MODAL -->
                <div
                    class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-border dark:bg-card"
                >
                    <!-- CLOSE -->
                    <button
                        type="button"
                        aria-label="Tutup"
                        class="absolute right-4 top-4 flex size-9 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-muted"
                        :disabled="deleting"
                        @click="closeDeleteModal"
                    >
                        <X class="size-4" />
                    </button>

                    <div class="p-6">
                        <!-- ICON -->
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-500 dark:bg-red-500/10"
                        >
                            <TriangleAlert class="size-6" />
                        </div>

                        <!-- CONTENT -->
                        <div class="mt-5">
                            <h2
                                class="text-xl font-bold tracking-tight text-slate-950 dark:text-white"
                            >
                                Hapus Artikel?
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                            >
                                Artikel
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    “{{ articleToDelete?.title }}”
                                </span>
                                akan dihapus secara permanen.
                            </p>

                            <div
                                class="mt-4 rounded-xl border border-red-100 bg-red-50/70 px-4 py-3 dark:border-red-500/10 dark:bg-red-500/5"
                            >
                                <p
                                    class="text-xs leading-5 text-red-600 dark:text-red-400"
                                >
                                    Tindakan ini tidak dapat dibatalkan.
                                    Thumbnail artikel dan relasi tag juga akan
                                    dibersihkan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION -->
                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4 dark:border-border dark:bg-muted/20"
                    >
                        <button
                            type="button"
                            class="inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-border dark:bg-background dark:text-slate-300 dark:hover:bg-muted"
                            :disabled="deleting"
                            @click="closeDeleteModal"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-xl bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="deleting"
                            @click="deleteArticle"
                        >
                            <Trash2 v-if="!deleting" class="size-4" />

                            <span
                                v-else
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            {{ deleting ? "Menghapus..." : "Hapus Artikel" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
