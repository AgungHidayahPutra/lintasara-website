<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleOff,
    FileText,
    FolderOpen,
    Pencil,
    Plus,
    RotateCcw,
    Search,
    Trash2,
    TriangleAlert,
    X,
} from "lucide-vue-next";
import { ref, watch } from "vue";

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
    articles_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface CategoriesPagination {
    data: Category[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

const props = defineProps<{
    categories: CategoriesPagination;

    stats: {
        total: number;
        active: number;
        inactive: number;
        used: number;
    };

    filters: {
        search: string;
        status: string;
    };
}>();

defineOptions({
    layout: AdminLayout,
});

const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "");

let searchTimeout: ReturnType<typeof setTimeout> | undefined;

function applyFilters() {
    router.get(
        "/admin/categories",
        {
            search: search.value || undefined,
            status: status.value || undefined,
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

watch(status, () => {
    applyFilters();
});

function resetFilters() {
    search.value = "";
    status.value = "";

    router.get(
        "/admin/categories",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteModalOpen = ref(false);
const categoryToDelete = ref<Category | null>(null);
const deleting = ref(false);

function openDeleteModal(category: Category) {
    categoryToDelete.value = category;
    deleteModalOpen.value = true;
}

function closeDeleteModal() {
    if (deleting.value) {
        return;
    }

    deleteModalOpen.value = false;
    categoryToDelete.value = null;
}

function deleteCategory() {
    if (!categoryToDelete.value || deleting.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/categories/${categoryToDelete.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            deleteModalOpen.value = false;
            categoryToDelete.value = null;
        },

        onFinish: () => {
            deleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Kategori" />

    <div
        class="min-h-full flex-1 bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background"
    >
        <div class="mx-auto flex max-w-[1600px] flex-col gap-6">
            <!-- =====================================================
                 HERO
            ====================================================== -->
            <section
                class="relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white px-6 py-7 shadow-sm md:px-8 dark:border-border dark:bg-card"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-32 size-80 rounded-full bg-orange-100/60 blur-3xl dark:bg-orange-500/5"
                />

                <div
                    class="pointer-events-none absolute right-40 top-10 size-32 rounded-full bg-amber-100/40 blur-3xl dark:bg-amber-500/5"
                />

                <div
                    class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-600 dark:bg-orange-500/10 dark:text-orange-400"
                        >
                            <FolderOpen class="size-3.5" />
                            Master Konten
                        </div>

                        <h1
                            class="text-3xl font-bold tracking-tight text-slate-950 md:text-4xl dark:text-white"
                        >
                            Kategori
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 md:text-base dark:text-muted-foreground"
                        >
                            Kelola kategori yang digunakan untuk mengelompokkan
                            berita dan artikel Lintasara.
                        </p>
                    </div>

                    <Link
                        href="/admin/categories/create"
                        class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition-all hover:-translate-y-0.5 hover:bg-orange-600 hover:shadow-xl hover:shadow-orange-500/25"
                    >
                        <Plus class="size-5" />
                        Tambah Kategori
                    </Link>
                </div>
            </section>

            <!-- =====================================================
                 STATISTICS
            ====================================================== -->
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <!-- TOTAL -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 transition group-hover:scale-105 dark:bg-orange-500/10"
                        >
                            <FolderOpen class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Total Kategori
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.total }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Seluruh kategori
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ACTIVE -->
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
                                Kategori Aktif
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.active }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Dapat digunakan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- INACTIVE -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 transition group-hover:scale-105 dark:bg-slate-500/10"
                        >
                            <CircleOff class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Nonaktif
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.inactive }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Tidak dapat dipilih
                            </p>
                        </div>
                    </div>
                </div>

                <!-- USED -->
                <div
                    class="group rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-border dark:bg-card"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-sky-50 text-sky-500 transition group-hover:scale-105 dark:bg-sky-500/10"
                        >
                            <FileText class="size-6" />
                        </div>

                        <div>
                            <p
                                class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                            >
                                Digunakan
                            </p>

                            <p class="mt-1 text-2xl font-bold tracking-tight">
                                {{ stats.used }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Memiliki artikel
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =====================================================
                 FILTER
            ====================================================== -->
            <section
                class="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm dark:border-border dark:bg-card"
            >
                <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_260px_auto]">
                    <div class="relative">
                        <Search
                            class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="search"
                            placeholder="Cari nama, slug, atau deskripsi..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                        />
                    </div>

                    <select
                        v-model="status"
                        class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                    >
                        <option value="">Semua status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>

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

            <!-- =====================================================
                 TABLE
            ====================================================== -->
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
                                    class="min-w-[280px] px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Kategori
                                </th>

                                <th
                                    class="min-w-[280px] px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Deskripsi
                                </th>

                                <th
                                    class="whitespace-nowrap px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Artikel
                                </th>

                                <th
                                    class="whitespace-nowrap px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Urutan
                                </th>

                                <th
                                    class="whitespace-nowrap px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Status
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
                                v-for="categoryItem in categories.data"
                                :key="categoryItem.id"
                                class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50/70 dark:border-border dark:hover:bg-muted/20"
                            >
                                <!-- CATEGORY -->
                                <td class="px-5 py-4">
                                    <div
                                        class="font-semibold text-slate-900 dark:text-foreground"
                                    >
                                        {{ categoryItem.name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        /{{ categoryItem.slug }}
                                    </div>
                                </td>

                                <!-- DESCRIPTION -->
                                <td
                                    class="max-w-[380px] px-5 py-4 text-slate-500"
                                >
                                    <p class="line-clamp-2 leading-6">
                                        {{
                                            categoryItem.description ??
                                            "Belum ada deskripsi."
                                        }}
                                    </p>
                                </td>

                                <!-- ARTICLE COUNT -->
                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-9 items-center justify-center rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600 dark:bg-muted dark:text-slate-300"
                                    >
                                        {{ categoryItem.articles_count }}
                                    </span>
                                </td>

                                <!-- ORDER -->
                                <td
                                    class="px-5 py-4 text-center font-medium text-slate-500"
                                >
                                    {{ categoryItem.sort_order }}
                                </td>

                                <!-- STATUS -->
                                <td class="px-5 py-4">
                                    <span
                                        v-if="categoryItem.is_active"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-500/10 dark:text-emerald-400"
                                    >
                                        <span
                                            class="size-1.5 rounded-full bg-emerald-500"
                                        />
                                        Aktif
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10 dark:bg-slate-500/10 dark:text-slate-300"
                                    >
                                        <span
                                            class="size-1.5 rounded-full bg-slate-400"
                                        />
                                        Nonaktif
                                    </span>
                                </td>

                                <!-- ACTION -->
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-1">
                                        <Link
                                            :href="`/admin/categories/${categoryItem.id}/edit`"
                                            title="Edit kategori"
                                            class="flex size-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-orange-50 hover:text-orange-600 dark:hover:bg-orange-500/10"
                                        >
                                            <Pencil class="size-4" />
                                        </Link>

                                        <button
                                            type="button"
                                            title="Hapus kategori"
                                            class="flex size-9 cursor-pointer items-center justify-center rounded-lg text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                                            @click="
                                                openDeleteModal(categoryItem)
                                            "
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->
                            <tr v-if="categories.data.length === 0">
                                <td colspan="6">
                                    <div
                                        class="flex min-h-[360px] flex-col items-center justify-center px-6 py-16 text-center"
                                    >
                                        <div
                                            class="relative flex size-20 items-center justify-center rounded-3xl bg-slate-50 dark:bg-muted"
                                        >
                                            <FolderOpen
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
                                            Tidak ada kategori
                                        </h3>

                                        <p
                                            class="mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                                        >
                                            Tambahkan kategori untuk
                                            mengelompokkan artikel dan
                                            mempermudah pembaca menemukan
                                            berita.
                                        </p>

                                        <Link
                                            href="/admin/categories/create"
                                            class="mt-6 inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:-translate-y-0.5 hover:bg-orange-600"
                                        >
                                            <Plus class="size-4" />
                                            Tambah Kategori
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- FOOTER -->
                <div
                    class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-border"
                >
                    <p class="text-sm text-slate-500">
                        <template v-if="categories.total > 0">
                            Menampilkan
                            <span
                                class="font-medium text-slate-700 dark:text-foreground"
                            >
                                {{ categories.from }}–{{ categories.to }}
                            </span>
                            dari
                            <span
                                class="font-medium text-slate-700 dark:text-foreground"
                            >
                                {{ categories.total }}
                            </span>
                            kategori
                        </template>

                        <template v-else> Belum ada data kategori </template>
                    </p>

                    <!-- PAGINATION -->
                    <div
                        v-if="categories.last_page > 1"
                        class="flex items-center gap-1"
                    >
                        <Link
                            v-for="link in categories.links"
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
         DELETE MODAL
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
                <button
                    type="button"
                    aria-label="Tutup modal"
                    class="absolute inset-0 cursor-default bg-slate-950/50 backdrop-blur-[2px]"
                    @click="closeDeleteModal"
                />

                <div
                    class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-border dark:bg-card"
                >
                    <button
                        type="button"
                        aria-label="Tutup"
                        :disabled="deleting"
                        class="absolute right-4 top-4 flex size-9 cursor-pointer items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-muted"
                        @click="closeDeleteModal"
                    >
                        <X class="size-4" />
                    </button>

                    <div class="p-6">
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-500 dark:bg-red-500/10"
                        >
                            <TriangleAlert class="size-6" />
                        </div>

                        <div class="mt-5">
                            <h2
                                class="text-xl font-bold tracking-tight text-slate-950 dark:text-white"
                            >
                                Hapus Kategori?
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                            >
                                Kategori
                                <span
                                    class="font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    “{{ categoryToDelete?.name }}”
                                </span>
                                akan dihapus secara permanen.
                            </p>

                            <div
                                v-if="
                                    categoryToDelete &&
                                    categoryToDelete.articles_count > 0
                                "
                                class="mt-4 rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3 dark:border-amber-500/10 dark:bg-amber-500/5"
                            >
                                <p
                                    class="text-xs leading-5 text-amber-700 dark:text-amber-400"
                                >
                                    Kategori ini digunakan oleh
                                    {{ categoryToDelete.articles_count }}
                                    artikel. Kategori yang masih digunakan tidak
                                    dapat dihapus.
                                </p>
                            </div>

                            <div
                                v-else
                                class="mt-4 rounded-xl border border-red-100 bg-red-50/70 px-4 py-3 dark:border-red-500/10 dark:bg-red-500/5"
                            >
                                <p
                                    class="text-xs leading-5 text-red-600 dark:text-red-400"
                                >
                                    Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4 dark:border-border dark:bg-muted/20"
                    >
                        <button
                            type="button"
                            :disabled="deleting"
                            class="inline-flex h-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-border dark:bg-background dark:text-slate-300 dark:hover:bg-muted"
                            @click="closeDeleteModal"
                        >
                            Batal
                        </button>

                        <button
                            type="button"
                            :disabled="
                                deleting ||
                                (categoryToDelete?.articles_count ?? 0) > 0
                            "
                            class="inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-xl bg-red-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                            @click="deleteCategory"
                        >
                            <Trash2 v-if="!deleting" class="size-4" />

                            <span
                                v-else
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            {{ deleting ? "Menghapus..." : "Hapus Kategori" }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
