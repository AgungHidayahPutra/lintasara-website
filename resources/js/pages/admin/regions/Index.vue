<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Globe2,
    MapPin,
    Pencil,
    Plus,
    Search,
    Trash2,
    X,
} from "lucide-vue-next";
import { onBeforeUnmount, ref, watch } from "vue";

defineOptions({
    layout: AdminLayout,
});

interface Region {
    id: number;
    parent_id: number | null;
    name: string;
    slug: string;
    type: "province" | "city" | "regency";
    is_active: boolean;
    sort_order: number;
    parent: {
        id: number;
        name: string;
        type: string;
    } | null;
    children_count: number;
    articles_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedRegions {
    data: Region[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: PaginationLink[];
}

interface RegionStats {
    total: number;
    provinces: number;
    cities: number;
    regencies: number;
    active: number;
    inactive: number;
}

const props = defineProps<{
    regions: PaginatedRegions;
    stats: RegionStats;
    filters: {
        search: string;
        type: string;
        status: string;
    };
}>();

const search = ref(props.filters.search ?? "");
const type = ref(props.filters.type ?? "");
const status = ref(props.filters.status ?? "");

let filterTimer: ReturnType<typeof setTimeout> | undefined;

function applyFilters() {
    clearTimeout(filterTimer);

    router.get(
        "/admin/regions",
        {
            ...(search.value.trim() ? { search: search.value.trim() } : {}),
            ...(type.value ? { type: type.value } : {}),
            ...(status.value ? { status: status.value } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

watch(search, () => {
    clearTimeout(filterTimer);

    filterTimer = setTimeout(() => {
        applyFilters();
    }, 350);
});

watch([type, status], () => {
    applyFilters();
});

onBeforeUnmount(() => {
    clearTimeout(filterTimer);
});

function resetFilters() {
    clearTimeout(filterTimer);

    search.value = "";
    type.value = "";
    status.value = "";

    applyFilters();
}

function typeLabel(value: Region["type"]): string {
    const labels = {
        province: "Provinsi",
        city: "Kota",
        regency: "Kabupaten",
    };

    return labels[value] ?? value;
}

function typeClasses(value: Region["type"]): string {
    const classes = {
        province:
            "bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400",
        city: "bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400",
        regency:
            "bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400",
    };

    return classes[value] ?? "";
}

const regionToDelete = ref<Region | null>(null);
const deleting = ref(false);

function openDeleteModal(region: Region) {
    regionToDelete.value = region;
}

function closeDeleteModal() {
    if (deleting.value) return;

    regionToDelete.value = null;
}

function deleteRegion() {
    if (!regionToDelete.value || deleting.value) return;

    if (
        regionToDelete.value.children_count > 0 ||
        regionToDelete.value.articles_count > 0
    ) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/regions/${regionToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            regionToDelete.value = null;
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
</script>

<template>
    <Head title="Manajemen Wilayah" />

    <div class="min-h-full bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background">
        <div class="mx-auto max-w-[1250px]">
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
                        Manajemen Wilayah
                    </h1>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                    >
                        Kelola provinsi, kota, dan kabupaten untuk pelaporan
                        berita Lintasara.
                    </p>
                </div>

                <Link
                    href="/admin/regions/create"
                    class="inline-flex h-11 items-center justify-center gap-2 self-start rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
                >
                    <Plus class="size-4" />
                    Tambah Wilayah
                </Link>
            </div>

            <!-- STATISTIK -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">
                            Total Wilayah
                        </p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                        >
                            <MapPin class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.total }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Seluruh wilayah terdaftar
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">
                            Provinsi
                        </p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10"
                        >
                            <Globe2 class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.provinces }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Wilayah tingkat provinsi
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">Kota</p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10"
                        >
                            <Building2 class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.cities }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Wilayah tingkat kota
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">
                            Kabupaten
                        </p>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"
                        >
                            <MapPin class="size-5" />
                        </div>
                    </div>

                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ stats.regencies }}
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Wilayah tingkat kabupaten
                    </p>
                </div>
            </div>

            <!-- TABLE -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
            >
                <!-- FILTER -->
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 p-5 dark:border-border"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Daftar Wilayah
                            </h2>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ regions.total }} wilayah ditemukan
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10"
                            >
                                <CheckCircle2 class="size-3.5" />
                                {{ stats.active }} Aktif
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 dark:bg-muted dark:text-slate-300"
                            >
                                {{ stats.inactive }} Nonaktif
                            </span>
                        </div>
                    </div>

                    <div
                        class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto]"
                    >
                        <div class="relative">
                            <Search
                                class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Cari wilayah atau provinsi induk..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                            />
                        </div>

                        <select
                            v-model="type"
                            aria-label="Filter jenis wilayah"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-orange-400 dark:border-border dark:bg-background dark:text-slate-300"
                        >
                            <option value="">Semua Jenis</option>
                            <option value="province">Provinsi</option>
                            <option value="city">Kota</option>
                            <option value="regency">Kabupaten</option>
                        </select>

                        <select
                            v-model="status"
                            aria-label="Filter status wilayah"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-orange-400 dark:border-border dark:bg-background dark:text-slate-300"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>

                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-border dark:text-slate-300"
                            @click="resetFilters"
                        >
                            <X class="size-4" />
                            Reset
                        </button>
                    </div>
                </div>

                <!-- TABEL -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead
                            class="border-b border-slate-100 bg-slate-50/70 dark:border-border dark:bg-muted/20"
                        >
                            <tr
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                <th class="px-5 py-4">Wilayah</th>
                                <th class="px-5 py-4">Jenis</th>
                                <th class="px-5 py-4">Induk</th>
                                <th class="px-5 py-4 text-center">Turunan</th>
                                <th class="px-5 py-4 text-center">Artikel</th>
                                <th class="px-5 py-4 text-center">Status</th>
                                <th class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100 dark:divide-border"
                        >
                            <tr
                                v-for="region in regions.data"
                                :key="region.id"
                                class="transition hover:bg-slate-50/70 dark:hover:bg-muted/20"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                                        >
                                            <MapPin class="size-4" />
                                        </div>

                                        <div>
                                            <p
                                                class="text-sm font-semibold text-slate-900 dark:text-white"
                                            >
                                                {{ region.name }}
                                            </p>

                                            <p
                                                class="mt-1 font-mono text-xs text-slate-400"
                                            >
                                                {{ region.slug }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-lg px-2.5 py-1.5 text-xs font-semibold"
                                        :class="typeClasses(region.type)"
                                    >
                                        {{ typeLabel(region.type) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="text-sm text-slate-600 dark:text-slate-300"
                                    >
                                        {{ region.parent?.name ?? "—" }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        {{ region.children_count }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        {{ region.articles_count }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-lg px-2.5 py-1.5 text-xs font-semibold"
                                        :class="
                                            region.is_active
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                                                : 'bg-slate-100 text-slate-500 dark:bg-muted'
                                        "
                                    >
                                        {{
                                            region.is_active
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/regions/${region.id}/edit`"
                                            :aria-label="`Edit ${region.name}`"
                                            title="Edit Wilayah"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-600 dark:border-border"
                                        >
                                            <Pencil class="size-4" />
                                        </Link>

                                        <button
                                            type="button"
                                            :aria-label="`Hapus ${region.name}`"
                                            title="Hapus Wilayah"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-border"
                                            @click="openDeleteModal(region)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="regions.data.length === 0">
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div
                                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                                    >
                                        <MapPin class="size-7" />
                                    </div>

                                    <h3
                                        class="mt-4 text-base font-semibold text-slate-900 dark:text-white"
                                    >
                                        Belum ada wilayah
                                    </h3>

                                    <p
                                        class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500"
                                    >
                                        Tambahkan provinsi terlebih dahulu,
                                        kemudian buat kota atau kabupaten di
                                        bawahnya.
                                    </p>

                                    <Link
                                        href="/admin/regions/create"
                                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600"
                                    >
                                        <Plus class="size-4" />
                                        Tambah Wilayah
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="regions.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-border"
                >
                    <p class="text-xs text-slate-500">
                        Menampilkan
                        <strong
                            >{{ regions.from ?? 0 }}–{{
                                regions.to ?? 0
                            }}</strong
                        >
                        dari
                        <strong>{{ regions.total }}</strong>
                        wilayah
                    </p>

                    <div
                        v-if="regions.last_page > 1"
                        class="flex flex-wrap items-center gap-1.5"
                    >
                        <button
                            v-for="(link, index) in regions.links"
                            :key="index"
                            type="button"
                            :disabled="!link.url || link.active"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition disabled:cursor-default"
                            :class="
                                link.active
                                    ? 'border-orange-500 bg-orange-500 text-white'
                                    : link.url
                                      ? 'border-slate-200 bg-white text-slate-600 hover:border-orange-300 hover:text-orange-500 dark:border-border dark:bg-card'
                                      : 'border-slate-100 bg-slate-50 text-slate-300 dark:border-border dark:bg-muted'
                            "
                            @click="navigateTo(link.url)"
                        >
                            <ChevronLeft v-if="index === 0" class="size-4" />
                            <ChevronRight
                                v-else-if="index === regions.links.length - 1"
                                class="size-4"
                            />
                            <span v-else>{{ link.label }}</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- MODAL HAPUS -->
        <div
            v-if="regionToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            @click.self="closeDeleteModal"
        >
            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-region-title"
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-border dark:bg-card"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-500 dark:bg-red-500/10"
                >
                    <Trash2 class="size-6" />
                </div>

                <h2
                    id="delete-region-title"
                    class="mt-4 text-xl font-bold text-slate-950 dark:text-white"
                >
                    Hapus Wilayah?
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Anda akan menghapus wilayah
                    <strong class="text-slate-900 dark:text-white">
                        {{ regionToDelete.name }} </strong
                    >.
                </p>

                <div
                    v-if="
                        regionToDelete.children_count > 0 ||
                        regionToDelete.articles_count > 0
                    "
                    class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
                >
                    Wilayah ini tidak dapat dihapus karena masih memiliki
                    <strong v-if="regionToDelete.children_count > 0">
                        {{ regionToDelete.children_count }} wilayah turunan
                    </strong>
                    <span
                        v-if="
                            regionToDelete.children_count > 0 &&
                            regionToDelete.articles_count > 0
                        "
                    >
                        dan
                    </span>
                    <strong v-if="regionToDelete.articles_count > 0">
                        {{ regionToDelete.articles_count }} artikel </strong
                    >.
                </div>

                <p v-else class="mt-3 text-xs text-slate-400">
                    Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        :disabled="deleting"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-border"
                        @click="closeDeleteModal"
                    >
                        Batal
                    </button>

                    <button
                        v-if="
                            regionToDelete.children_count === 0 &&
                            regionToDelete.articles_count === 0
                        "
                        type="button"
                        :disabled="deleting"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-red-500 px-4 text-sm font-semibold text-white transition hover:bg-red-600 disabled:opacity-60"
                        @click="deleteRegion"
                    >
                        <Trash2 v-if="!deleting" class="size-4" />
                        {{ deleting ? "Menghapus..." : "Hapus Wilayah" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
