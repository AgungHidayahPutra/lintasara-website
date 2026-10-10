<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    FileText,
    Pencil,
    Plus,
    Search,
    Tags,
    Trash2,
    X,
} from "lucide-vue-next";
import { ref, watch, onBeforeUnmount } from "vue";

defineOptions({
    layout: AdminLayout,
});

interface Tag {
    id: number;
    name: string;
    slug: string;
    articles_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTags {
    data: Tag[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface TagStats {
    total: number;
    used: number;
    unused: number;
}

const props = defineProps<{
    tags: PaginatedTags;
    stats: TagStats;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search ?? "");

let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(() => {
        router.get(
            "/admin/tags",
            {
                ...(value.trim() ? { search: value.trim() } : {}),
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 350);
});

onBeforeUnmount(() => {
    clearTimeout(searchTimer);
});

function resetFilters() {
    clearTimeout(searchTimer);
    search.value = "";

    router.get(
        "/admin/tags",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

const tagToDelete = ref<Tag | null>(null);
const deleting = ref(false);

function openDeleteModal(tag: Tag) {
    tagToDelete.value = tag;
}

function closeDeleteModal() {
    if (deleting.value) return;
    tagToDelete.value = null;
}

function deleteTag() {
    if (!tagToDelete.value || deleting.value) return;

    if (tagToDelete.value.articles_count > 0) return;

    deleting.value = true;

    router.delete(`/admin/tags/${tagToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            tagToDelete.value = null;
        },
        onFinish: () => {
            deleting.value = false;
        },
    });
}

function navigateTo(url: string | null) {
    if (!url) return;

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function paginationLabel(label: string) {
    return label
        .replace(/&laquo;/g, "«")
        .replace(/&raquo;/g, "»")
        .replace(/&amp;/g, "&");
}
</script>

<template>
    <Head title="Manajemen Tag" />

    <div
        class="min-h-full flex-1 bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background"
    >
        <div class="mx-auto max-w-[1200px]">
            <!-- HEADER -->
            <div
                class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <Link
                        href="/admin"
                        class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Dashboard
                    </Link>

                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        Manajemen Tag
                    </h1>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                    >
                        Kelola topik dan kata kunci untuk mengorganisasi artikel
                        Lintasara.
                    </p>
                </div>

                <Link
                    href="/admin/tags/create"
                    class="inline-flex h-11 items-center justify-center gap-2 self-start rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
                >
                    <Plus class="size-4" />
                    Tambah Tag
                </Link>
            </div>

            <!-- STATS -->
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                        >
                            Total Tag
                        </p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                        >
                            <Tags class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.total }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Seluruh tag terdaftar
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                        >
                            Digunakan Artikel
                        </p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10"
                        >
                            <FileText class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.used }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Tag yang dipakai minimal satu artikel
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p
                            class="text-sm font-medium text-slate-500 dark:text-muted-foreground"
                        >
                            Belum Digunakan
                        </p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-muted"
                        >
                            <Tags class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.unused }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Tag yang belum terhubung ke artikel
                    </p>
                </div>
            </div>

            <!-- MAIN TABLE -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
            >
                <!-- TABLE HEADER -->
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 p-5 md:flex-row md:items-center md:justify-between dark:border-border"
                >
                    <div>
                        <h2
                            class="font-semibold text-slate-900 dark:text-white"
                        >
                            Daftar Tag
                        </h2>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ tags.total }} tag ditemukan
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative min-w-0 flex-1 sm:w-72">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari nama atau slug..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                            />
                        </div>

                        <button
                            v-if="search"
                            type="button"
                            class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-medium text-slate-600 transition hover:bg-slate-50 dark:border-border dark:text-slate-300"
                            @click="resetFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>
                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[650px] text-left">
                        <thead
                            class="border-b border-slate-100 bg-slate-50/70 dark:border-border dark:bg-muted/20"
                        >
                            <tr
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                <th class="px-6 py-4">Nama Tag</th>

                                <th class="px-6 py-4">Slug</th>

                                <th class="px-6 py-4 text-center">Artikel</th>

                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-border"
                        >
                            <tr
                                v-for="tag in tags.data"
                                :key="tag.id"
                                class="transition hover:bg-slate-50/70 dark:hover:bg-muted/20"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                                        >
                                            <Tags class="size-4" />
                                        </div>

                                        <div>
                                            <p
                                                class="text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ tag.name }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-xs text-slate-400"
                                            >
                                                ID #{{ tag.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="rounded-lg bg-slate-100 px-2.5 py-1.5 font-mono text-xs text-slate-600 dark:bg-muted dark:text-slate-300"
                                    >
                                        {{ tag.slug }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex min-w-9 items-center justify-center rounded-lg px-2.5 py-1 text-xs font-semibold"
                                        :class="
                                            tag.articles_count > 0
                                                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-500 dark:bg-muted'
                                        "
                                    >
                                        {{ tag.articles_count }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/tags/${tag.id}/edit`"
                                            :aria-label="`Edit tag ${tag.name}`"
                                            title="Edit Tag"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-600 dark:border-border"
                                        >
                                            <Pencil class="size-4" />
                                        </Link>

                                        <button
                                            type="button"
                                            :aria-label="`Hapus tag ${tag.name}`"
                                            title="Hapus Tag"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-border"
                                            @click="openDeleteModal(tag)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY STATE -->
                            <tr v-if="tags.data.length === 0">
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                                    >
                                        <Tags class="size-7" />
                                    </div>

                                    <h3
                                        class="mt-4 text-base font-semibold text-slate-900 dark:text-white"
                                    >
                                        {{
                                            search
                                                ? "Tag tidak ditemukan"
                                                : "Belum ada tag"
                                        }}
                                    </h3>

                                    <p
                                        class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500"
                                    >
                                        {{
                                            search
                                                ? "Coba gunakan kata kunci pencarian lain."
                                                : "Tambahkan tag pertama untuk mulai mengelompokkan topik artikel."
                                        }}
                                    </p>

                                    <Link
                                        v-if="!search"
                                        href="/admin/tags/create"
                                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                                    >
                                        <Plus class="size-4" />
                                        Tambah Tag
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="tags.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-border"
                >
                    <p class="text-xs text-slate-500">
                        Menampilkan
                        <span class="font-semibold">
                            {{ tags.from ?? 0 }}–{{ tags.to ?? 0 }}
                        </span>
                        dari
                        <span class="font-semibold">
                            {{ tags.total }}
                        </span>
                        tag
                    </p>

                    <div
                        v-if="tags.last_page > 1"
                        class="flex flex-wrap items-center gap-1.5"
                    >
                        <button
                            v-for="(link, index) in tags.links"
                            :key="index"
                            type="button"
                            :disabled="!link.url || link.active"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition disabled:cursor-default"
                            :class="
                                link.active
                                    ? 'border-orange-500 bg-orange-500 text-white'
                                    : link.url
                                      ? 'border-slate-200 bg-white text-slate-600 hover:border-orange-300 hover:text-orange-500 dark:border-border dark:bg-card dark:text-slate-300'
                                      : 'border-slate-100 bg-slate-50 text-slate-300 dark:border-border dark:bg-muted'
                            "
                            @click="navigateTo(link.url)"
                        >
                            <ChevronLeft v-if="index === 0" class="size-4" />

                            <ChevronRight
                                v-else-if="index === tags.links.length - 1"
                                class="size-4"
                            />

                            <span v-else>
                                {{ paginationLabel(link.label) }}
                            </span>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- DELETE MODAL -->
        <div
            v-if="tagToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            @click.self="closeDeleteModal"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-tag-title"
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-border dark:bg-card"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-500 dark:bg-red-500/10"
                >
                    <Trash2 class="size-6" />
                </div>

                <h2
                    id="delete-tag-title"
                    class="mt-4 text-xl font-bold text-slate-950 dark:text-white"
                >
                    Hapus Tag?
                </h2>

                <p
                    class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                >
                    Anda akan menghapus tag
                    <strong class="text-slate-900 dark:text-white">
                        {{ tagToDelete.name }} </strong
                    >.
                </p>

                <div
                    v-if="tagToDelete.articles_count > 0"
                    class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
                >
                    Tag ini sedang digunakan oleh
                    <strong> {{ tagToDelete.articles_count }} artikel </strong>
                    sehingga tidak dapat dihapus.
                </div>

                <p v-else class="mt-3 text-xs leading-5 text-slate-400">
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        :disabled="deleting"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-border dark:text-slate-300"
                        @click="closeDeleteModal"
                    >
                        Batal
                    </button>

                    <button
                        v-if="tagToDelete.articles_count === 0"
                        type="button"
                        :disabled="deleting"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-red-500 px-4 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-60"
                        @click="deleteTag"
                    >
                        <Trash2 v-if="!deleting" class="size-4" />

                        {{ deleting ? "Menghapus..." : "Hapus Tag" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
