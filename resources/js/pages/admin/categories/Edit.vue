<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CheckCircle2,
    FolderPen,
    Info,
    Save,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    sort_order: number;
}

const props = defineProps<{
    category: Category;
}>();

defineOptions({
    layout: AdminLayout,
});

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const slugEditedManually = ref(true);

const form = useForm({
    name: props.category.name,
    slug: props.category.slug,
    description: props.category.description ?? "",
    is_active: props.category.is_active,
    sort_order: props.category.sort_order,
});

function makeSlug(value: string) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, "")
        .replace(/\s+/g, "-")
        .replace(/-+/g, "-");
}

watch(
    () => form.name,
    (value) => {
        if (!slugEditedManually.value) {
            form.slug = makeSlug(value);
        }
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

const descriptionLength = computed(() => form.description.length);

function submit() {
    form.put(`/admin/categories/${props.category.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit Kategori - ${category.name}`" />

    <div
        class="min-h-full flex-1 bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background"
    >
        <div class="mx-auto max-w-[1200px]">
            <!-- =====================================================
                 HEADER
            ====================================================== -->
            <div
                class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <Link
                        href="/admin/categories"
                        class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Kategori
                    </Link>

                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        Edit Kategori
                    </h1>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                    >
                        Perbarui informasi dan pengaturan kategori
                        {{ category.name }}.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <Link
                        href="/admin/categories"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-border dark:bg-card"
                    >
                        Batal
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="submit"
                    >
                        <Save v-if="!form.processing" class="size-4" />

                        <span
                            v-else
                            class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                        />

                        {{
                            form.processing
                                ? "Menyimpan..."
                                : "Simpan Perubahan"
                        }}
                    </button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <!-- =================================================
                     MAIN FORM
                ================================================== -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
                >
                    <div
                        class="flex items-center gap-3 border-b border-slate-100 px-6 py-5 dark:border-border"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                        >
                            <FolderPen class="size-5" />
                        </div>

                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Informasi Kategori
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-400">
                                Perbarui informasi utama kategori berita.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <!-- NAME -->
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Nama Kategori
                                <span class="text-orange-500">*</span>
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Contoh: Otomotif"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                                :class="
                                    form.errors.name
                                        ? 'border-red-400 focus:border-red-400 focus:ring-red-500/10'
                                        : ''
                                "
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-xs font-medium text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- SLUG -->
                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <label
                                    for="slug"
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    URL / Slug
                                </label>

                                <button
                                    type="button"
                                    class="text-xs font-semibold text-orange-500 transition hover:text-orange-600"
                                    @click="generateSlugFromName"
                                >
                                    Buat dari nama
                                </button>
                            </div>

                            <div
                                class="flex overflow-hidden rounded-xl border border-slate-200 bg-white transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-500/10 dark:border-border dark:bg-background"
                                :class="
                                    form.errors.slug ? 'border-red-400' : ''
                                "
                            >
                                <div
                                    class="flex items-center border-r border-slate-200 bg-slate-50 px-4 text-sm text-slate-400 dark:border-border dark:bg-muted"
                                >
                                    /kategori/
                                </div>

                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="otomotif"
                                    class="h-12 min-w-0 flex-1 bg-transparent px-4 text-sm outline-none"
                                    @input="handleSlugInput"
                                />
                            </div>

                            <p
                                v-if="form.errors.slug"
                                class="mt-2 text-xs font-medium text-red-500"
                            >
                                {{ form.errors.slug }}
                            </p>

                            <p v-else class="mt-2 text-xs text-slate-400">
                                Slug lama dipertahankan sampai Anda mengubahnya
                                atau memilih "Buat dari nama".
                            </p>
                        </div>

                        <!-- DESCRIPTION -->
                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <label
                                    for="description"
                                    class="block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Deskripsi
                                </label>

                                <span class="text-xs text-slate-400">
                                    {{ descriptionLength }} karakter
                                </span>
                            </div>

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="5"
                                placeholder="Tuliskan deskripsi singkat kategori..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                                :class="
                                    form.errors.description
                                        ? 'border-red-400'
                                        : ''
                                "
                            />

                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-xs font-medium text-red-500"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- SORT ORDER -->
                        <div>
                            <label
                                for="sort_order"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Urutan
                            </label>

                            <input
                                id="sort_order"
                                v-model.number="form.sort_order"
                                type="number"
                                min="0"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                                :class="
                                    form.errors.sort_order
                                        ? 'border-red-400'
                                        : ''
                                "
                            />

                            <p
                                v-if="form.errors.sort_order"
                                class="mt-2 text-xs font-medium text-red-500"
                            >
                                {{ form.errors.sort_order }}
                            </p>

                            <p v-else class="mt-2 text-xs text-slate-400">
                                Angka lebih kecil akan ditampilkan lebih dahulu.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- =================================================
                     SIDEBAR
                ================================================== -->
                <div class="space-y-6">
                    <!-- STATUS -->
                    <section
                        class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                    >
                        <h2
                            class="font-semibold text-slate-900 dark:text-white"
                        >
                            Status Kategori
                        </h2>

                        <p class="mt-1 text-xs leading-5 text-slate-400">
                            Tentukan apakah kategori dapat digunakan pada
                            artikel.
                        </p>

                        <label
                            class="mt-5 flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 dark:border-border dark:hover:bg-muted/30"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl"
                                    :class="
                                        form.is_active
                                            ? 'bg-emerald-50 text-emerald-500 dark:bg-emerald-500/10'
                                            : 'bg-slate-100 text-slate-400 dark:bg-muted'
                                    "
                                >
                                    <CheckCircle2 class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                    >
                                        {{
                                            form.is_active
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        {{
                                            form.is_active
                                                ? "Tampil pada pilihan kategori artikel."
                                                : "Tidak tampil pada pilihan kategori artikel."
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- TOGGLE -->
                            <div class="relative h-6 w-11 shrink-0">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="sr-only"
                                />

                                <!-- TRACK -->
                                <div
                                    class="absolute inset-0 rounded-full transition-colors duration-200"
                                    :class="
                                        form.is_active
                                            ? 'bg-orange-500'
                                            : 'bg-slate-200 dark:bg-slate-700'
                                    "
                                ></div>

                                <!-- BULATAN -->
                                <div
                                    class="pointer-events-none absolute top-1 size-4 rounded-full bg-white shadow-sm transition-all duration-200"
                                    :class="
                                        form.is_active ? 'left-6' : 'left-1'
                                    "
                                ></div>
                            </div>
                        </label>

                        <p
                            v-if="form.errors.is_active"
                            class="mt-2 text-xs font-medium text-red-500"
                        >
                            {{ form.errors.is_active }}
                        </p>
                    </section>

                    <!-- INFO -->
                    <section
                        class="rounded-2xl border border-sky-100 bg-sky-50/60 p-5 dark:border-sky-500/10 dark:bg-sky-500/5"
                    >
                        <div class="flex gap-3">
                            <Info class="mt-0.5 size-5 shrink-0 text-sky-500" />

                            <div>
                                <h3
                                    class="text-sm font-semibold text-sky-900 dark:text-sky-300"
                                >
                                    Tentang kategori
                                </h3>

                                <p
                                    class="mt-1.5 text-xs leading-5 text-sky-700/80 dark:text-sky-400"
                                >
                                    Jika kategori dinonaktifkan, artikel lama
                                    tetap menggunakan kategori ini. Namun
                                    kategori tidak akan tersedia untuk dipilih
                                    pada artikel baru.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- CURRENT CATEGORY -->
                    <section
                        class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Kategori saat ini
                        </p>

                        <p
                            class="mt-2 text-base font-semibold text-slate-900 dark:text-white"
                        >
                            {{ category.name }}
                        </p>

                        <p class="mt-1 break-all text-xs text-slate-400">
                            /kategori/{{ category.slug }}
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</template>
