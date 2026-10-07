<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    AlertCircle,
    ArrowLeft,
    CalendarDays,
    Check,
    ChevronDown,
    FileText,
    Image,
    LoaderCircle,
    Newspaper,
    Save,
    Search,
    Star,
    Tag as TagIcon,
    UploadCloud,
    X,
    Zap,
} from "lucide-vue-next";
import { computed, ref } from "vue";

defineOptions({
    layout: AdminLayout,
});

interface Category {
    id: number;
    name: string;
}

interface Region {
    id: number;
    parent_id: number | null;
    name: string;
    type: "province" | "city" | "regency";
    parent?: {
        id: number;
        name: string;
    } | null;
}

interface Tag {
    id: number;
    name: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    category_id: number;
    region_id: number | null;
    excerpt: string | null;
    content: string;

    thumbnail: string | null;
    thumbnail_url: string | null;
    thumbnail_alt: string | null;
    image_caption: string | null;
    image_credit: string | null;

    status: "draft" | "published" | "archived";
    published_at: string | null;

    is_featured: boolean;
    is_breaking: boolean;

    meta_title: string | null;
    meta_description: string | null;

    tags: number[];
}

const props = defineProps<{
    article: Article;
    categories: Category[];
    regions: Region[];
    tags: Tag[];
}>();

const form = useForm({
    _method: "put",

    title: props.article.title ?? "",
    slug: props.article.slug ?? "",

    category_id: props.article.category_id as number | "",
    region_id: (props.article.region_id ?? "") as number | "",

    excerpt: props.article.excerpt ?? "",
    content: props.article.content ?? "",

    thumbnail: null as File | null,
    remove_thumbnail: false,

    thumbnail_alt: props.article.thumbnail_alt ?? "",
    image_caption: props.article.image_caption ?? "",
    image_credit: props.article.image_credit ?? "",

    status: props.article.status,

    published_at: props.article.published_at
        ? props.article.published_at.slice(0, 16)
        : "",

    is_featured: Boolean(props.article.is_featured),
    is_breaking: Boolean(props.article.is_breaking),

    meta_title: props.article.meta_title ?? "",
    meta_description: props.article.meta_description ?? "",

    tags: [...(props.article.tags ?? [])],
});

const thumbnailPreview = ref<string | null>(
    props.article.thumbnail_url ?? null,
);

const thumbnailClientError = ref("");
const tagSearch = ref("");

const slugify = (value: string) => {
    return value
        .toLowerCase()
        .trim()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
};

const updateSlug = (event: Event) => {
    const target = event.target as HTMLInputElement;

    form.slug = slugify(target.value);
};

const filteredTags = computed(() => {
    const search = tagSearch.value.trim().toLowerCase();

    if (!search) {
        return props.tags;
    }

    return props.tags.filter((tag) => tag.name.toLowerCase().includes(search));
});

const selectedTags = computed(() => {
    return props.tags.filter((tag) => form.tags.includes(tag.id));
});

const toggleTag = (id: number) => {
    if (form.tags.includes(id)) {
        form.tags = form.tags.filter((tagId) => tagId !== id);

        return;
    }

    form.tags = [...form.tags, id];
};

const formattedThumbnailSize = computed(() => {
    if (!form.thumbnail) {
        return "";
    }

    const sizeInMb = form.thumbnail.size / 1024 / 1024;

    if (sizeInMb < 1) {
        return `${Math.round(form.thumbnail.size / 1024)} KB`;
    }

    return `${sizeInMb.toFixed(2)} MB`;
});

const handleThumbnail = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    thumbnailClientError.value = "";

    if (!file) {
        return;
    }

    const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

    if (!allowedTypes.includes(file.type)) {
        thumbnailClientError.value =
            "Format gambar harus JPG, JPEG, PNG, atau WEBP.";

        input.value = "";

        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        thumbnailClientError.value = "Ukuran gambar maksimal 5 MB.";

        input.value = "";

        return;
    }

    if (thumbnailPreview.value && thumbnailPreview.value.startsWith("blob:")) {
        URL.revokeObjectURL(thumbnailPreview.value);
    }

    form.thumbnail = file;
    form.remove_thumbnail = false;

    form.clearErrors("thumbnail");

    thumbnailPreview.value = URL.createObjectURL(file);

    input.value = "";
};

const removeThumbnail = () => {
    if (thumbnailPreview.value && thumbnailPreview.value.startsWith("blob:")) {
        URL.revokeObjectURL(thumbnailPreview.value);
    }

    form.thumbnail = null;
    form.remove_thumbnail = true;

    thumbnailPreview.value = null;

    thumbnailClientError.value = "";

    form.clearErrors("thumbnail");
};

const scrollToFirstError = () => {
    setTimeout(() => {
        const firstError = document.querySelector("[data-form-error]");

        firstError?.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });
    }, 100);
};

const submit = () => {
    form.post(`/admin/articles/${props.article.id}`, {
        forceFormData: true,
        preserveScroll: true,

        onError: (errors) => {
            console.error("Gagal memperbarui artikel:", errors);

            scrollToFirstError();
        },
    });
};

const saveAsDraft = () => {
    form.status = "draft";
    submit();
};

const publishArticle = () => {
    form.status = "published";
    submit();
};
</script>

<template>
    <Head title="Edit Artikel" />

    <div class="min-h-full bg-[#f8fafc]">
        <div class="mx-auto max-w-[1600px] px-4 py-6 md:px-6 lg:px-8 lg:py-8">
            <!-- HEADER -->
            <div
                class="mb-7 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between"
            >
                <div>
                    <Link
                        href="/admin/articles"
                        class="mb-4 inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-500"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Artikel
                    </Link>

                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-600"
                        >
                            <Newspaper class="size-3.5" />
                            Manajemen Konten
                        </span>
                    </div>

                    <h1
                        class="mt-3 text-3xl font-bold tracking-tight text-[#071a36]"
                    >
                        Edit Artikel
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm text-slate-500">
                        Perbarui informasi dan konten artikel Lintasara.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/admin/articles"
                        class="inline-flex h-11 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    >
                        Batal
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-[#071a36] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0d2b54] disabled:cursor-not-allowed disabled:opacity-60"
                        @click="saveAsDraft"
                    >
                        <LoaderCircle
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />

                        <Save v-if="!form.processing" class="size-4" />

                        {{
                            form.processing
                                ? "Menyimpan..."
                                : "Simpan Perubahan"
                        }}
                    </button>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="inline-flex h-11 cursor-pointer items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="publishArticle"
                    >
                        <LoaderCircle
                            v-if="form.processing"
                            class="size-4 animate-spin"
                        />

                        <Check v-if="!form.processing" class="size-4" />

                        {{ form.processing ? "Menyimpan..." : "Terbitkan" }}
                    </button>
                </div>
            </div>

            <form
                class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]"
                @submit.prevent="submit"
            >
                <!-- ERROR GLOBAL -->
                <div
                    v-if="Object.keys(form.errors).length"
                    data-form-error
                    class="rounded-2xl border border-red-200 bg-red-50 p-4 xl:col-span-2"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-red-100"
                        >
                            <AlertCircle class="size-5 text-red-500" />
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-red-700">
                                Artikel belum dapat diperbarui
                            </p>

                            <p class="mt-1 text-xs leading-5 text-red-500">
                                Periksa kembali kolom yang ditandai. Ada data
                                yang belum lengkap atau tidak valid.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KIRI -->
                <div class="space-y-6">
                    <!-- KONTEN ARTIKEL -->
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div
                            class="border-b border-slate-100 px-5 py-4 md:px-6"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-orange-50"
                                >
                                    <FileText
                                        class="size-4.5 text-orange-500"
                                    />
                                </div>

                                <div>
                                    <h2 class="font-semibold text-[#071a36]">
                                        Konten Artikel
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Informasi utama yang akan dibaca
                                        pengunjung.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6 p-5 md:p-6">
                            <!-- JUDUL -->
                            <div>
                                <label
                                    for="title"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Judul Artikel
                                    <span class="text-orange-500"> * </span>
                                </label>

                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Masukkan judul artikel..."
                                    class="h-12 w-full rounded-xl border bg-white px-4 text-[15px] text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                    :class="
                                        form.errors.title
                                            ? 'border-red-300'
                                            : 'border-slate-200'
                                    "
                                />

                                <p
                                    v-if="form.errors.title"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.title }}
                                </p>
                            </div>

                            <!-- SLUG -->
                            <div>
                                <label
                                    for="slug"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    URL / Slug
                                </label>

                                <div
                                    class="flex overflow-hidden rounded-xl border border-slate-200 bg-white transition focus-within:border-orange-400 focus-within:ring-4 focus-within:ring-orange-500/10"
                                >
                                    <div
                                        class="flex items-center border-r border-slate-200 bg-slate-50 px-4 text-sm text-slate-400"
                                    >
                                        /berita/
                                    </div>

                                    <input
                                        id="slug"
                                        :value="form.slug"
                                        type="text"
                                        placeholder="judul-artikel"
                                        class="h-12 min-w-0 flex-1 border-0 bg-transparent px-4 text-sm text-slate-700 outline-none"
                                        @input="updateSlug"
                                    />
                                </div>

                                <p class="mt-2 text-xs text-slate-400">
                                    Slug artikel saat ini dipertahankan. Anda
                                    tetap dapat mengubahnya secara manual.
                                </p>

                                <p
                                    v-if="form.errors.slug"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.slug }}
                                </p>
                            </div>

                            <!-- RINGKASAN -->
                            <div>
                                <div
                                    class="mb-2 flex items-center justify-between gap-4"
                                >
                                    <label
                                        for="excerpt"
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Ringkasan
                                    </label>

                                    <span class="text-xs text-slate-400">
                                        {{ form.excerpt.length }}
                                        karakter
                                    </span>
                                </div>

                                <textarea
                                    id="excerpt"
                                    v-model="form.excerpt"
                                    rows="3"
                                    placeholder="Tulis ringkasan singkat artikel..."
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <p
                                    v-if="form.errors.excerpt"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.excerpt }}
                                </p>
                            </div>

                            <!-- ISI ARTIKEL -->
                            <div>
                                <label
                                    for="content"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Isi Artikel
                                    <span class="text-orange-500"> * </span>
                                </label>

                                <textarea
                                    id="content"
                                    v-model="form.content"
                                    rows="18"
                                    placeholder="Mulai tulis isi berita di sini..."
                                    class="w-full resize-y rounded-xl border bg-white px-5 py-4 text-[15px] leading-7 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                    :class="
                                        form.errors.content
                                            ? 'border-red-300'
                                            : 'border-slate-200'
                                    "
                                />

                                <p
                                    v-if="form.errors.content"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.content }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- GAMBAR UTAMA -->
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div
                            class="border-b border-slate-100 px-5 py-4 md:px-6"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-sky-50"
                                >
                                    <Image class="size-4.5 text-sky-500" />
                                </div>

                                <div>
                                    <h2 class="font-semibold text-[#071a36]">
                                        Gambar Utama
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Thumbnail utama untuk halaman berita,
                                        daftar artikel, dan konten unggulan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-5 md:p-6">
                            <!-- UPLOAD -->
                            <div
                                v-if="!thumbnailPreview"
                                class="relative flex min-h-[240px] cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/70 transition hover:border-orange-300 hover:bg-orange-50/30"
                            >
                                <input
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="absolute inset-0 z-10 cursor-pointer opacity-0"
                                    @change="handleThumbnail"
                                />

                                <div
                                    class="pointer-events-none px-6 text-center"
                                >
                                    <div
                                        class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-white shadow-sm"
                                    >
                                        <UploadCloud
                                            class="size-5 text-orange-500"
                                        />
                                    </div>

                                    <p
                                        class="mt-4 text-sm font-semibold text-slate-700"
                                    >
                                        Upload gambar utama
                                    </p>

                                    <p
                                        class="mt-1 text-xs leading-5 text-slate-400"
                                    >
                                        Klik atau pilih gambar dari perangkat
                                        <br />
                                        JPG, JPEG, PNG atau WEBP • Maksimal 5 MB
                                    </p>
                                </div>
                            </div>

                            <!-- PREVIEW -->
                            <div
                                v-if="thumbnailPreview"
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-white"
                            >
                                <div
                                    class="relative overflow-hidden bg-slate-100"
                                >
                                    <img
                                        :src="thumbnailPreview ?? undefined"
                                        alt="Preview gambar utama"
                                        class="aspect-video w-full object-cover"
                                    />

                                    <div
                                        class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-3 py-1.5 text-xs font-semibold text-white shadow-lg"
                                    >
                                        <Check class="size-3.5" />
                                        Gambar tersedia
                                    </div>

                                    <button
                                        type="button"
                                        title="Hapus gambar"
                                        class="absolute right-3 top-3 flex size-9 cursor-pointer items-center justify-center rounded-full bg-white/95 text-slate-700 shadow-md transition hover:bg-red-50 hover:text-red-500"
                                        @click="removeThumbnail"
                                    >
                                        <X class="size-4" />
                                    </button>
                                </div>

                                <div
                                    class="flex flex-col gap-3 border-t border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-semibold text-slate-700"
                                        >
                                            {{
                                                form.thumbnail?.name ??
                                                "Gambar utama saat ini"
                                            }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            <template v-if="form.thumbnail">
                                                {{ formattedThumbnailSize }}
                                                •
                                            </template>

                                            Gambar utama artikel
                                        </p>
                                    </div>

                                    <label
                                        class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600"
                                    >
                                        <UploadCloud class="size-3.5" />

                                        Ganti gambar

                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            @change="handleThumbnail"
                                        />
                                    </label>
                                </div>
                            </div>

                            <p
                                v-if="
                                    thumbnailClientError ||
                                    form.errors.thumbnail
                                "
                                data-form-error
                                class="text-xs font-medium text-red-500"
                            >
                                {{
                                    thumbnailClientError ||
                                    form.errors.thumbnail
                                }}
                            </p>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        Alt Gambar
                                    </label>

                                    <input
                                        v-model="form.thumbnail_alt"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Contoh: Peresmian taman baru di Palembang"
                                        class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                    />

                                    <p
                                        v-if="form.errors.thumbnail_alt"
                                        data-form-error
                                        class="mt-2 text-xs font-medium text-red-500"
                                    >
                                        {{ form.errors.thumbnail_alt }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold text-slate-700"
                                    >
                                        Kredit Gambar
                                    </label>

                                    <input
                                        v-model="form.image_credit"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Contoh: Dokumentasi Lintasara"
                                        class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                    />

                                    <p
                                        v-if="form.errors.image_credit"
                                        data-form-error
                                        class="mt-2 text-xs font-medium text-red-500"
                                    >
                                        {{ form.errors.image_credit }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Caption Gambar
                                </label>

                                <input
                                    v-model="form.image_caption"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Keterangan gambar..."
                                    class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <p
                                    v-if="form.errors.image_caption"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.image_caption }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-sky-100 bg-sky-50/70 px-4 py-3"
                            >
                                <p class="text-xs leading-5 text-sky-700">
                                    <strong> Gambar Utama </strong>
                                    dibatasi satu gambar. Jika diganti, gambar
                                    baru akan menjadi thumbnail utama artikel.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SEO -->
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div
                            class="border-b border-slate-100 px-5 py-4 md:px-6"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-violet-50"
                                >
                                    <Search class="size-4.5 text-violet-500" />
                                </div>

                                <div>
                                    <h2 class="font-semibold text-[#071a36]">
                                        SEO
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Optimasi tampilan artikel pada mesin
                                        pencari.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-5 md:p-6">
                            <div>
                                <div class="mb-2 flex justify-between gap-4">
                                    <label
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Meta Title
                                    </label>

                                    <span
                                        class="text-xs"
                                        :class="
                                            form.meta_title.length > 60
                                                ? 'text-orange-500'
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{ form.meta_title.length }}/60
                                    </span>
                                </div>

                                <input
                                    v-model="form.meta_title"
                                    type="text"
                                    maxlength="255"
                                    placeholder="Judul untuk mesin pencari..."
                                    class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <p
                                    v-if="form.errors.meta_title"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.meta_title }}
                                </p>
                            </div>

                            <div>
                                <div class="mb-2 flex justify-between gap-4">
                                    <label
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Meta Description
                                    </label>

                                    <span
                                        class="text-xs"
                                        :class="
                                            form.meta_description.length > 160
                                                ? 'text-orange-500'
                                                : 'text-slate-400'
                                        "
                                    >
                                        {{ form.meta_description.length }}/160
                                    </span>
                                </div>

                                <textarea
                                    v-model="form.meta_description"
                                    rows="3"
                                    placeholder="Deskripsi singkat untuk hasil pencarian..."
                                    class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm leading-6 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <p
                                    v-if="form.errors.meta_description"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.meta_description }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- KOLOM KANAN -->
                <aside class="space-y-6 xl:sticky xl:top-24">
                    <!-- PUBLIKASI -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-emerald-50"
                            >
                                <CalendarDays
                                    class="size-4.5 text-emerald-500"
                                />
                            </div>

                            <div>
                                <h2 class="font-semibold text-[#071a36]">
                                    Publikasi
                                </h2>

                                <p class="text-xs text-slate-400">
                                    Atur status artikel.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Status
                                </label>

                                <div class="relative">
                                    <select
                                        v-model="form.status"
                                        class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                    >
                                        <option value="draft">Draft</option>

                                        <option value="published">
                                            Terbit
                                        </option>

                                        <option value="archived">Arsip</option>
                                    </select>

                                    <ChevronDown
                                        class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.status"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <div v-if="form.status === 'published'">
                                <label
                                    class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Waktu Terbit
                                </label>

                                <input
                                    v-model="form.published_at"
                                    type="datetime-local"
                                    class="h-11 w-full rounded-xl border border-slate-200 px-3 text-sm text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <p
                                    class="mt-2 text-xs leading-5 text-slate-400"
                                >
                                    Kosongkan untuk menerbitkan sekarang.
                                </p>

                                <p
                                    v-if="form.errors.published_at"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.published_at }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- KLASIFIKASI -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <h2 class="font-semibold text-[#071a36]">
                            Klasifikasi
                        </h2>

                        <p class="mt-1 text-xs text-slate-400">
                            Tentukan kategori dan wilayah berita.
                        </p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Kategori
                                    <span class="text-orange-500"> * </span>
                                </label>

                                <div class="relative">
                                    <select
                                        v-model="form.category_id"
                                        class="h-11 w-full cursor-pointer appearance-none rounded-xl border bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                        :class="
                                            form.errors.category_id
                                                ? 'border-red-300'
                                                : 'border-slate-200'
                                        "
                                    >
                                        <option value="">Pilih kategori</option>

                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>

                                    <ChevronDown
                                        class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.category_id"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.category_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Wilayah
                                </label>

                                <div class="relative">
                                    <select
                                        v-model="form.region_id"
                                        class="h-11 w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                    >
                                        <option value="">
                                            Nasional / Tanpa wilayah
                                        </option>

                                        <option
                                            v-for="region in regions"
                                            :key="region.id"
                                            :value="region.id"
                                        >
                                            {{
                                                region.parent
                                                    ? `${region.name} — ${region.parent.name}`
                                                    : region.name
                                            }}
                                        </option>
                                    </select>

                                    <ChevronDown
                                        class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.region_id"
                                    data-form-error
                                    class="mt-2 text-xs font-medium text-red-500"
                                >
                                    {{ form.errors.region_id }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- PENANDA -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <h2 class="font-semibold text-[#071a36]">
                            Penanda Artikel
                        </h2>

                        <div class="mt-4 space-y-3">
                            <label
                                class="flex cursor-pointer items-center gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-orange-200 hover:bg-orange-50/40"
                            >
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="sr-only"
                                />

                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        form.is_featured
                                            ? 'bg-amber-100 text-amber-600'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    <Star class="size-5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Artikel Pilihan
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Tampilkan sebagai konten unggulan.
                                    </p>
                                </div>

                                <div
                                    class="relative h-6 w-11 shrink-0 rounded-full transition"
                                    :class="
                                        form.is_featured
                                            ? 'bg-orange-500'
                                            : 'bg-slate-200'
                                    "
                                >
                                    <div
                                        class="absolute top-1 size-4 rounded-full bg-white shadow transition-all"
                                        :class="
                                            form.is_featured
                                                ? 'left-6'
                                                : 'left-1'
                                        "
                                    />
                                </div>
                            </label>

                            <label
                                class="flex cursor-pointer items-center gap-4 rounded-xl border border-slate-200 p-4 transition hover:border-red-200 hover:bg-red-50/40"
                            >
                                <input
                                    v-model="form.is_breaking"
                                    type="checkbox"
                                    class="sr-only"
                                />

                                <div
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        form.is_breaking
                                            ? 'bg-red-100 text-red-500'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    <Zap class="size-5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Breaking News
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Tandai sebagai berita penting.
                                    </p>
                                </div>

                                <div
                                    class="relative h-6 w-11 shrink-0 rounded-full transition"
                                    :class="
                                        form.is_breaking
                                            ? 'bg-red-500'
                                            : 'bg-slate-200'
                                    "
                                >
                                    <div
                                        class="absolute top-1 size-4 rounded-full bg-white shadow transition-all"
                                        :class="
                                            form.is_breaking
                                                ? 'left-6'
                                                : 'left-1'
                                        "
                                    />
                                </div>
                            </label>
                        </div>
                    </section>

                    <!-- TAG -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-2">
                            <TagIcon class="size-4 text-orange-500" />

                            <h2 class="font-semibold text-[#071a36]">Tag</h2>
                        </div>

                        <div
                            v-if="selectedTags.length"
                            class="mt-4 flex flex-wrap gap-2"
                        >
                            <button
                                v-for="tag in selectedTags"
                                :key="tag.id"
                                type="button"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-600 transition hover:bg-orange-100"
                                @click="toggleTag(tag.id)"
                            >
                                {{ tag.name }}

                                <X class="size-3" />
                            </button>
                        </div>

                        <div class="relative mt-4">
                            <Search
                                class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="tagSearch"
                                type="text"
                                placeholder="Cari tag..."
                                class="h-10 w-full rounded-xl border border-slate-200 pl-9 pr-3 text-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                            />
                        </div>

                        <div class="mt-3 max-h-48 space-y-1 overflow-y-auto">
                            <button
                                v-for="tag in filteredTags"
                                :key="tag.id"
                                type="button"
                                class="flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition"
                                :class="
                                    form.tags.includes(tag.id)
                                        ? 'bg-orange-50 font-medium text-orange-600'
                                        : 'text-slate-600 hover:bg-slate-50'
                                "
                                @click="toggleTag(tag.id)"
                            >
                                {{ tag.name }}

                                <Check
                                    v-if="form.tags.includes(tag.id)"
                                    class="size-4"
                                />
                            </button>

                            <p
                                v-if="filteredTags.length === 0"
                                class="px-3 py-5 text-center text-xs text-slate-400"
                            >
                                Tag tidak ditemukan.
                            </p>
                        </div>

                        <p
                            v-if="form.errors.tags"
                            data-form-error
                            class="mt-3 text-xs font-medium text-red-500"
                        >
                            {{ form.errors.tags }}
                        </p>
                    </section>
                </aside>
            </form>
        </div>
    </div>
</template>
