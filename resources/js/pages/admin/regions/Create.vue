<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    Globe2,
    Info,
    MapPin,
    Plus,
    Save,
    X,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";

defineOptions({
    layout: AdminLayout,
});

interface Province {
    id: number;
    name: string;
}

const props = defineProps<{
    provinces: Province[];
}>();

type RegionType = "province" | "city" | "regency";

const form = useForm({
    name: "",
    slug: "",
    type: "province" as RegionType,
    parent_id: null as number | null,
    is_active: true,
    sort_order: 0,
});

const slugEditedManually = ref(false);

function makeSlug(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-")
        .replace(/^-|-$/g, "");
}

watch(
    () => form.name,
    (value) => {
        if (!slugEditedManually.value) {
            form.slug = makeSlug(value);
        }
    },
);

watch(
    () => form.type,
    () => {
        form.parent_id = null;
    },
);

function handleSlugInput() {
    slugEditedManually.value = true;
    form.slug = makeSlug(form.slug);
}

function generateSlugFromName() {
    slugEditedManually.value = false;
    form.slug = makeSlug(form.name);
}

const requiresParent = computed(() => form.type !== "province");

const selectedProvince = computed(() =>
    props.provinces.find((province) => province.id === Number(form.parent_id)),
);

const typeLabel = computed(() => {
    switch (form.type) {
        case "province":
            return "Provinsi";
        case "city":
            return "Kota";
        case "regency":
            return "Kabupaten";
    }
});

const canSubmit = computed(() => {
    return (
        form.name.trim().length > 0 &&
        form.slug.trim().length > 0 &&
        (!requiresParent.value || form.parent_id !== null) &&
        !form.processing
    );
});

function submit() {
    if (!canSubmit.value) return;

    form.post("/admin/regions", {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Tambah Wilayah" />

    <div class="min-h-full bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background">
        <div class="mx-auto max-w-[1150px]">
            <!-- HEADER -->
            <div
                class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <Link
                        href="/admin/regions"
                        class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Manajemen Wilayah
                    </Link>

                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        Tambah Wilayah
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Tambahkan provinsi, kota, atau kabupaten untuk lokasi
                        pemberitaan Lintasara.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/regions"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-border dark:bg-card dark:text-slate-300"
                    >
                        <X class="size-4" />
                        Batal
                    </Link>

                    <button
                        type="button"
                        :disabled="!canSubmit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="submit"
                    >
                        <Save class="size-4" />
                        {{
                            form.processing ? "Menyimpan..." : "Simpan Wilayah"
                        }}
                    </button>
                </div>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]"
                @submit.prevent="submit"
            >
                <!-- MAIN FORM -->
                <div class="space-y-6">
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 p-6 dark:border-border"
                        >
                            <div
                                class="flex size-11 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                            >
                                <MapPin class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="text-base font-bold text-slate-950 dark:text-white"
                                >
                                    Informasi Wilayah
                                </h2>

                                <p class="mt-1 text-xs text-slate-500">
                                    Lengkapi identitas dan jenis wilayah.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-6 p-6">
                            <!-- JENIS WILAYAH -->
                            <div>
                                <label
                                    class="mb-3 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Jenis Wilayah
                                    <span class="text-red-500">*</span>
                                </label>

                                <div class="grid gap-3 sm:grid-cols-3">
                                    <button
                                        v-for="option in [
                                            {
                                                value: 'province',
                                                label: 'Provinsi',
                                                description: 'Wilayah induk',
                                            },
                                            {
                                                value: 'city',
                                                label: 'Kota',
                                                description:
                                                    'Di bawah provinsi',
                                            },
                                            {
                                                value: 'regency',
                                                label: 'Kabupaten',
                                                description:
                                                    'Di bawah provinsi',
                                            },
                                        ]"
                                        :key="option.value"
                                        type="button"
                                        class="rounded-xl border p-4 text-left transition"
                                        :class="
                                            form.type === option.value
                                                ? 'border-orange-500 bg-orange-50 ring-2 ring-orange-500/10 dark:bg-orange-500/10'
                                                : 'border-slate-200 bg-white hover:border-orange-200 dark:border-border dark:bg-background'
                                        "
                                        @click="
                                            form.type =
                                                option.value as RegionType
                                        "
                                    >
                                        <div
                                            class="mb-3 flex size-9 items-center justify-center rounded-lg"
                                            :class="
                                                form.type === option.value
                                                    ? 'bg-orange-500 text-white'
                                                    : 'bg-slate-100 text-slate-500 dark:bg-muted'
                                            "
                                        >
                                            <Globe2
                                                v-if="
                                                    option.value === 'province'
                                                "
                                                class="size-4"
                                            />
                                            <Building2
                                                v-else-if="
                                                    option.value === 'city'
                                                "
                                                class="size-4"
                                            />
                                            <MapPin v-else class="size-4" />
                                        </div>

                                        <p
                                            class="text-sm font-semibold text-slate-900 dark:text-white"
                                        >
                                            {{ option.label }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ option.description }}
                                        </p>
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.type"
                                    class="mt-2 text-xs text-red-500"
                                >
                                    {{ form.errors.type }}
                                </p>
                            </div>

                            <!-- PROVINSI INDUK -->
                            <div v-if="requiresParent">
                                <label
                                    for="parent_id"
                                    class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Provinsi Induk
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="parent_id"
                                    v-model.number="form.parent_id"
                                    required
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background dark:text-slate-200"
                                >
                                    <option :value="null" disabled>
                                        Pilih provinsi induk
                                    </option>

                                    <option
                                        v-for="province in provinces"
                                        :key="province.id"
                                        :value="province.id"
                                    >
                                        {{ province.name }}
                                    </option>
                                </select>

                                <p class="mt-2 text-xs text-slate-400">
                                    Kota dan kabupaten harus terhubung ke
                                    provinsi.
                                </p>

                                <p
                                    v-if="form.errors.parent_id"
                                    class="mt-2 text-xs text-red-500"
                                >
                                    {{ form.errors.parent_id }}
                                </p>

                                <div
                                    v-if="provinces.length === 0"
                                    class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-800"
                                >
                                    Belum ada provinsi aktif. Tambahkan provinsi
                                    terlebih dahulu sebelum membuat kota atau
                                    kabupaten.
                                </div>
                            </div>

                            <!-- NAMA -->
                            <div>
                                <label
                                    for="name"
                                    class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Nama Wilayah
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    maxlength="255"
                                    required
                                    :placeholder="
                                        form.type === 'province'
                                            ? 'Contoh: Sumatera Barat'
                                            : form.type === 'city'
                                              ? 'Contoh: Padang'
                                              : 'Contoh: Agam'
                                    "
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background dark:text-white"
                                />

                                <p
                                    v-if="form.errors.name"
                                    class="mt-2 text-xs text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- SLUG -->
                            <div>
                                <label
                                    for="slug"
                                    class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Slug Wilayah
                                </label>

                                <div class="flex gap-2">
                                    <input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        maxlength="255"
                                        placeholder="slug-wilayah"
                                        class="h-12 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 font-mono text-sm text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background dark:text-slate-200"
                                        @input="handleSlugInput"
                                    />

                                    <button
                                        type="button"
                                        class="shrink-0 rounded-xl border border-slate-200 px-4 text-xs font-semibold text-slate-600 transition hover:border-orange-300 hover:text-orange-600 dark:border-border"
                                        @click="generateSlugFromName"
                                    >
                                        Buat dari nama
                                    </button>
                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    Dibuat otomatis dari nama wilayah. Slug
                                    duplikat akan disesuaikan oleh sistem.
                                </p>

                                <p
                                    v-if="form.errors.slug"
                                    class="mt-2 text-xs text-red-500"
                                >
                                    {{ form.errors.slug }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- PENGATURAN -->
                    <section
                        class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm dark:border-border dark:bg-card"
                    >
                        <h2
                            class="text-base font-bold text-slate-950 dark:text-white"
                        >
                            Pengaturan Wilayah
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Atur urutan dan ketersediaan wilayah.
                        </p>

                        <div class="mt-6 space-y-5">
                            <div>
                                <label
                                    for="sort_order"
                                    class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    Urutan Tampilan
                                </label>

                                <input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    required
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none focus:border-orange-400 dark:border-border dark:bg-background"
                                />

                                <p class="mt-2 text-xs text-slate-400">
                                    Angka lebih kecil ditampilkan lebih dahulu.
                                </p>

                                <p
                                    v-if="form.errors.sort_order"
                                    class="mt-2 text-xs text-red-500"
                                >
                                    {{ form.errors.sort_order }}
                                </p>
                            </div>

                            <label
                                class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 p-4 dark:border-border"
                            >
                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-900 dark:text-white"
                                    >
                                        Status Aktif
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Wilayah tersedia untuk digunakan dalam
                                        artikel.
                                    </p>
                                </div>

                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="size-5 accent-orange-500"
                                />
                            </label>
                        </div>
                    </section>

                    <!-- MOBILE SUBMIT -->
                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-orange-500 text-sm font-semibold text-white transition hover:bg-orange-600 disabled:opacity-50 lg:hidden"
                    >
                        <Plus class="size-4" />
                        {{
                            form.processing ? "Menyimpan..." : "Tambah Wilayah"
                        }}
                    </button>
                </div>

                <!-- SIDEBAR -->
                <aside class="space-y-5">
                    <!-- PREVIEW -->
                    <section
                        class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                    >
                        <h2
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Preview Wilayah
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Pratinjau informasi sebelum disimpan.
                        </p>

                        <div
                            class="mt-5 rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-border dark:bg-muted/20"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-11 items-center justify-center rounded-xl bg-orange-100 text-orange-600 dark:bg-orange-500/10"
                                >
                                    <MapPin class="size-5" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ form.name || "Nama Wilayah" }}
                                    </p>

                                    <p
                                        class="mt-1 truncate font-mono text-xs text-slate-400"
                                    >
                                        {{ form.slug || "slug-wilayah" }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-5 space-y-3 border-t border-slate-200 pt-4 text-xs dark:border-border"
                            >
                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-500">Jenis</span>
                                    <strong
                                        class="text-slate-800 dark:text-white"
                                    >
                                        {{ typeLabel }}
                                    </strong>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-500">Induk</span>
                                    <strong
                                        class="text-right text-slate-800 dark:text-white"
                                    >
                                        {{
                                            requiresParent
                                                ? selectedProvince?.name ||
                                                  "Belum dipilih"
                                                : "Tidak ada"
                                        }}
                                    </strong>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-500">Urutan</span>
                                    <strong
                                        class="text-slate-800 dark:text-white"
                                    >
                                        {{ form.sort_order }}
                                    </strong>
                                </div>

                                <div class="flex justify-between gap-3">
                                    <span class="text-slate-500">Status</span>
                                    <span
                                        class="font-semibold"
                                        :class="
                                            form.is_active
                                                ? 'text-emerald-600'
                                                : 'text-slate-500'
                                        "
                                    >
                                        {{
                                            form.is_active
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- INFORMASI -->
                    <section
                        class="rounded-2xl border border-orange-100 bg-orange-50/60 p-5 dark:border-orange-500/20 dark:bg-orange-500/5"
                    >
                        <div class="flex items-center gap-2">
                            <Info class="size-4 text-orange-500" />

                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                Panduan Wilayah
                            </h3>
                        </div>

                        <div
                            class="mt-4 space-y-3 text-xs leading-6 text-slate-600 dark:text-slate-300"
                        >
                            <p>
                                <strong>Provinsi</strong> merupakan wilayah
                                induk dan tidak memerlukan parent.
                            </p>

                            <p>
                                <strong>Kota</strong> dan
                                <strong>Kabupaten</strong> harus berada di bawah
                                provinsi yang sudah terdaftar.
                            </p>

                            <p>
                                Nama wilayah digunakan untuk mengelompokkan
                                artikel berdasarkan lokasi pemberitaan.
                            </p>
                        </div>
                    </section>

                    <!-- STATUS -->
                    <section
                        class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-border dark:bg-card"
                    >
                        <div class="flex items-center gap-3">
                            <CheckCircle2 class="size-5 text-emerald-500" />

                            <div>
                                <p
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Validasi Otomatis
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-slate-500"
                                >
                                    Sistem memeriksa jenis, provinsi induk, dan
                                    keunikan slug saat penyimpanan.
                                </p>
                            </div>
                        </div>
                    </section>
                </aside>
            </form>
        </div>
    </div>
</template>
