<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ArrowLeft, CheckCircle2, Info, Save, Tag } from "lucide-vue-next";
import { computed, ref, watch } from "vue";

defineOptions({
    layout: AdminLayout,
});

const slugEditedManually = ref(false);

const form = useForm({
    name: "",
    slug: "",
});

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

function handleSlugInput() {
    slugEditedManually.value = true;
    form.slug = makeSlug(form.slug);
}

function generateSlugFromName() {
    slugEditedManually.value = false;
    form.slug = makeSlug(form.name);
}

const canSubmit = computed(() => {
    return form.name.trim().length > 0 && !form.processing;
});

function submit() {
    if (!canSubmit.value) return;

    form.post("/admin/tags", {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Tambah Tag" />

    <div class="min-h-full bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background">
        <div class="mx-auto max-w-[1100px]">
            <!-- HEADER -->
            <div
                class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <Link
                        href="/admin/tags"
                        class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Daftar Tag
                    </Link>

                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        Tambah Tag
                    </h1>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                    >
                        Buat kata kunci atau topik baru untuk artikel Lintasara.
                    </p>
                </div>

                <button
                    type="button"
                    :disabled="!canSubmit"
                    class="inline-flex h-11 items-center justify-center gap-2 self-start rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50"
                    @click="submit"
                >
                    <Save class="size-4" />
                    {{ form.processing ? "Menyimpan..." : "Simpan Tag" }}
                </button>
            </div>

            <form
                class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]"
                @submit.prevent="submit"
            >
                <!-- FORM UTAMA -->
                <section
                    class="rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
                >
                    <div
                        class="flex items-center gap-3 border-b border-slate-100 px-6 py-5 dark:border-border"
                    >
                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-orange-50 text-orange-500 dark:bg-orange-500/10"
                        >
                            <Tag class="size-5" />
                        </div>

                        <div>
                            <h2
                                class="text-base font-semibold text-slate-900 dark:text-white"
                            >
                                Informasi Tag
                            </h2>

                            <p class="mt-0.5 text-xs text-slate-400">
                                Isi nama dan alamat URL tag.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <!-- NAMA -->
                        <div>
                            <label
                                for="name"
                                class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200"
                            >
                                Nama Tag
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                maxlength="255"
                                required
                                autofocus
                                placeholder="Contoh: Artificial Intelligence"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background dark:text-white"
                                :class="{
                                    'border-red-400': form.errors.name,
                                }"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-2 text-xs text-red-500"
                            >
                                {{ form.errors.name }}
                            </p>

                            <p v-else class="mt-2 text-xs text-slate-400">
                                Gunakan nama singkat dan mudah dipahami pembaca.
                            </p>
                        </div>

                        <!-- SLUG -->
                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <label
                                    for="slug"
                                    class="text-sm font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Slug URL
                                </label>

                                <button
                                    type="button"
                                    class="text-xs font-semibold text-orange-600 transition hover:text-orange-700"
                                    @click="generateSlugFromName"
                                >
                                    Buat dari nama
                                </button>
                            </div>

                            <div
                                class="flex overflow-hidden rounded-xl border border-slate-200 bg-white transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-500/10 dark:border-border dark:bg-background"
                            >
                                <span
                                    class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-4 text-sm text-slate-400 dark:border-border dark:bg-muted"
                                >
                                    /
                                </span>

                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    maxlength="255"
                                    placeholder="artificial-intelligence"
                                    class="h-12 min-w-0 flex-1 bg-transparent px-4 font-mono text-sm text-slate-900 outline-none dark:text-white"
                                    @input="handleSlugInput"
                                />
                            </div>

                            <p
                                v-if="form.errors.slug"
                                class="mt-2 text-xs text-red-500"
                            >
                                {{ form.errors.slug }}
                            </p>

                            <p v-else class="mt-2 text-xs text-slate-400">
                                Terisi otomatis berdasarkan nama. Bisa diubah
                                secara manual.
                            </p>
                        </div>

                        <!-- PREVIEW -->
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-border dark:bg-muted/30"
                        >
                            <p
                                class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Pratinjau Tag
                            </p>

                            <div class="flex flex-wrap items-center gap-3">
                                <span
                                    class="inline-flex items-center gap-2 rounded-lg bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-600 dark:bg-orange-500/10"
                                >
                                    <Tag class="size-3.5" />
                                    {{ form.name.trim() || "Nama Tag" }}
                                </span>

                                <span
                                    class="break-all font-mono text-xs text-slate-400"
                                >
                                    /tag/{{ form.slug || "slug-tag" }}
                                </span>
                            </div>

                            <p class="mt-3 text-xs text-slate-400">
                                Pratinjau alamat URL. Halaman publik tag akan
                                tersedia setelah modul portal berita dibuat.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- SIDEBAR -->
                <div class="space-y-6">
                    <section
                        class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm dark:border-border dark:bg-card"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10"
                            >
                                <CheckCircle2 class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="text-sm font-semibold text-slate-900 dark:text-white"
                                >
                                    Status Tag
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Ketersediaan tag
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                        >
                            <CheckCircle2 class="size-4" />
                            Langsung tersedia
                        </div>

                        <p
                            class="mt-3 text-xs leading-6 text-slate-500 dark:text-muted-foreground"
                        >
                            Tag yang disimpan dapat langsung dipilih ketika
                            membuat atau mengedit artikel.
                        </p>
                    </section>

                    <section
                        class="rounded-2xl border border-slate-200/70 bg-white p-6 shadow-sm dark:border-border dark:bg-card"
                    >
                        <div class="flex items-center gap-2">
                            <Info class="size-4 text-orange-500" />

                            <h2
                                class="text-sm font-semibold text-slate-900 dark:text-white"
                            >
                                Panduan Tag
                            </h2>
                        </div>

                        <div
                            class="mt-4 space-y-3 text-xs leading-6 text-slate-500"
                        >
                            <p>
                                Tag membantu pembaca menemukan artikel dengan
                                topik yang saling berkaitan.
                            </p>

                            <p>
                                Satu artikel dapat memiliki lebih dari satu tag.
                            </p>

                            <p>
                                Hindari membuat tag berbeda untuk topik yang
                                sebenarnya sama.
                            </p>
                        </div>
                    </section>

                    <div class="flex gap-3 lg:hidden">
                        <Link
                            href="/admin/tags"
                            class="inline-flex h-11 flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-600 dark:border-border dark:bg-card"
                        >
                            Batal
                        </Link>

                        <button
                            type="submit"
                            :disabled="!canSubmit"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl bg-orange-500 text-sm font-semibold text-white disabled:opacity-50"
                        >
                            <Save class="size-4" />
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
