<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CalendarDays,
    Edit3,
    Eye,
    FileText,
    Image as ImageIcon,
    MapPin,
    Newspaper,
    Search,
    Star,
    Tag,
    UserRound,
    Zap,
} from "lucide-vue-next";
import { computed } from "vue";

defineOptions({
    layout: AdminLayout,
});

interface SimpleItem {
    id: number;
    name: string;
}

interface Region {
    id: number;
    name: string;
    type: string;

    parent: {
        id: number;
        name: string;
    } | null;
}

interface Author {
    id: number;
    name: string;
    email: string;
}

interface Article {
    id: number;

    title: string;
    slug: string;

    excerpt: string | null;
    content: string;

    thumbnail: string | null;
    thumbnail_url: string | null;

    thumbnail_alt: string | null;
    image_caption: string | null;
    image_credit: string | null;

    status: "draft" | "published" | "archived";

    is_featured: boolean;
    is_breaking: boolean;

    published_at: string | null;
    created_at: string | null;
    updated_at: string | null;

    views: number;

    meta_title: string | null;
    meta_description: string | null;

    category: SimpleItem | null;
    region: Region | null;
    author: Author | null;

    tags: SimpleItem[];
}

const props = defineProps<{
    article: Article;
}>();

const statusLabel = computed(() => {
    if (props.article.status === "published") {
        return "Terbit";
    }

    if (props.article.status === "archived") {
        return "Arsip";
    }

    return "Draft";
});

const statusClass = computed(() => {
    if (props.article.status === "published") {
        return "border-emerald-200 bg-emerald-50 text-emerald-600";
    }

    if (props.article.status === "archived") {
        return "border-slate-200 bg-slate-100 text-slate-600";
    }

    return "border-amber-200 bg-amber-50 text-amber-600";
});

const regionLabel = computed(() => {
    if (!props.article.region) {
        return "Nasional";
    }

    if (props.article.region.parent) {
        return `${props.article.region.name} — ${props.article.region.parent.name}`;
    }

    return props.article.region.name;
});

const articleDate = computed(() => {
    const source = props.article.published_at ?? props.article.created_at;

    if (!source) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(source));
});

const updatedDate = computed(() => {
    if (!props.article.updated_at) {
        return "-";
    }

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(props.article.updated_at));
});

const seoTitle = computed(() => {
    return props.article.meta_title || props.article.title;
});

const seoDescription = computed(() => {
    return props.article.meta_description || props.article.excerpt || "-";
});
</script>

<template>
    <Head :title="`Preview - ${article.title}`" />

    <div class="min-h-full bg-[#f8fafc]">
        <div class="mx-auto max-w-[1600px] px-4 py-6 md:px-6 lg:px-8 lg:py-8">
            <!-- =====================================================
                 HEADER
            ====================================================== -->
            <div
                class="mb-7 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between"
            >
                <div class="min-w-0">
                    <Link
                        href="/admin/articles"
                        class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-500"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Artikel
                    </Link>

                    <div>
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-600"
                        >
                            <Eye class="size-3.5" />
                            Preview Artikel
                        </span>
                    </div>

                    <h1
                        class="mt-3 text-3xl font-bold tracking-tight text-[#071a36]"
                    >
                        Preview Artikel
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Tinjau artikel sebelum ditampilkan kepada pembaca
                        Lintasara.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex h-11 items-center rounded-xl border px-4 text-sm font-semibold"
                        :class="statusClass"
                    >
                        {{ statusLabel }}
                    </span>

                    <Link
                        :href="`/admin/articles/${article.id}/edit`"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#071a36] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0d2b54]"
                    >
                        <Edit3 class="size-4" />
                        Edit Artikel
                    </Link>
                </div>
            </div>

            <!-- =====================================================
                 PAGE GRID
            ====================================================== -->
            <div
                class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_320px]"
            >
                <!-- =================================================
                     MAIN
                ================================================== -->
                <main class="min-w-0 space-y-6">
                    <article
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <!-- =========================================
                             ARTICLE HEADER
                        ========================================== -->
                        <div
                            class="px-5 pb-6 pt-7 md:px-8 md:pb-7 md:pt-8 lg:px-10 lg:pt-9"
                        >
                            <!-- BADGES -->
                            <div
                                class=" mt-4 mb-4 flex flex-wrap items-center gap-2.5"
                            >
                                <span
                                    v-if="article.category"
                                    class="inline-flex min-h-7 items-center rounded-full bg-orange-50 px-3.5 py-1.5 text-xs font-semibold text-orange-600"
                                >
                                    {{ article.category.name }}
                                </span>

                                <span
                                    class="inline-flex min-h-7 items-center gap-2 rounded-full bg-slate-100 px-3.5 py-1.5 text-xs font-medium text-slate-500"
                                >
                                    <MapPin class="size-3.5 shrink-0" />
                                    <span>{{ regionLabel }}</span>
                                </span>

                                <span
                                    v-if="article.is_featured"
                                    class="inline-flex min-h-7 items-center gap-1.5 rounded-full bg-amber-50 px-3.5 py-1.5 text-xs font-semibold text-amber-600"
                                >
                                    <Star class="size-3.5" />
                                    Artikel Pilihan
                                </span>

                                <span
                                    v-if="article.is_breaking"
                                    class="inline-flex min-h-7 items-center gap-1.5 rounded-full bg-red-50 px-3.5 py-1.5 text-xs font-semibold text-red-500"
                                >
                                    <Zap class="size-3.5" />
                                    Breaking News
                                </span>
                            </div>

                            <!-- TITLE -->
                            <h2
                                class="max-w-5xl text-2xl font-bold leading-tight tracking-tight text-[#071a36] md:text-3xl lg:text-[36px]"
                            >
                                {{ article.title }}
                            </h2>

                            <!-- EXCERPT -->
                            <p
                                v-if="article.excerpt"
                                class="mt-4 max-w-4xl text-sm leading-7 text-slate-500 md:text-base"
                            >
                                {{ article.excerpt }}
                            </p>

                            <!-- META -->
                            <div class="mt-6 border-t border-slate-100 pt-5">
                                <div
                                    class="flex flex-wrap items-center gap-y-4 text-sm text-slate-500 gap-3"
                                >
                                    <!-- AUTHOR -->
                                    <div
                                        v-if="article.author"
                                        class="flex shrink-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#071a36] text-white"
                                        >
                                            <UserRound class="size-4" />
                                        </div>

                                        <span
                                            class="font-semibold text-slate-700"
                                        >
                                            {{ article.author.name }}
                                        </span>
                                    </div>

                                    <!-- DATE -->
                                    <div
                                        class="ml-6 flex shrink-0 items-center gap-2"
                                    >
                                        <CalendarDays
                                            class="size-4 shrink-0 text-slate-400"
                                        />

                                        <span>
                                            {{ articleDate }}
                                        </span>
                                    </div>

                                    <!-- VIEWS -->
                                    <div
                                        class="ml-6 flex shrink-0 items-center gap-2"
                                    >
                                        <Eye
                                            class="size-4 shrink-0 text-slate-400"
                                        />

                                        <span>
                                            {{ article.views ?? 0 }} dilihat
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================
                             IMAGE PREVIEW
                        ========================================== -->
                        <div
                            v-if="article.thumbnail_url"
                            class="border-t border-slate-100 px-5 py-6 md:px-8 lg:px-10"
                        >
                            <div
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div
                                    class="grid gap-5 md:grid-cols-[180px_minmax(0,1fr)] md:items-start"
                                >
                                    <!-- THUMBNAIL -->
                                    <div
                                        class="flex h-[120px] w-full items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white p-2.5 md:w-[180px]"
                                    >
                                        <img
                                            :src="article.thumbnail_url"
                                            :alt="
                                                article.thumbnail_alt ??
                                                article.title
                                            "
                                            class="h-full w-full object-contain"
                                        />
                                    </div>

                                    <!-- IMAGE INFORMATION -->
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-orange-50"
                                            >
                                                <ImageIcon
                                                    class="size-4 text-orange-500"
                                                />
                                            </div>

                                            <div class="min-w-0">
                                                <p
                                                    class="text-sm font-semibold text-[#071a36]"
                                                >
                                                    Gambar Utama
                                                </p>

                                                <p
                                                    class="mt-0.5 text-xs text-slate-400"
                                                >
                                                    Thumbnail utama artikel.
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="mt-4 grid gap-4 sm:grid-cols-2"
                                        >
                                            <div
                                                v-if="article.thumbnail_alt"
                                                class="min-w-0"
                                            >
                                                <p
                                                    class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                                >
                                                    Alt Gambar
                                                </p>

                                                <p
                                                    class="mt-1 break-words text-xs leading-5 text-slate-600"
                                                >
                                                    {{ article.thumbnail_alt }}
                                                </p>
                                            </div>

                                            <div
                                                v-if="article.image_credit"
                                                class="min-w-0"
                                            >
                                                <p
                                                    class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                                >
                                                    Kredit
                                                </p>

                                                <p
                                                    class="mt-1 break-words text-xs leading-5 text-slate-600"
                                                >
                                                    {{ article.image_credit }}
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            v-if="article.image_caption"
                                            class="mt-4 border-t border-slate-200 pt-4"
                                        >
                                            <p
                                                class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                            >
                                                Caption
                                            </p>

                                            <p
                                                class="mt-1 break-words text-xs leading-5 text-slate-600"
                                            >
                                                {{ article.image_caption }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================
                             NO IMAGE
                        ========================================== -->
                        <div
                            v-else
                            class="border-t border-slate-100 px-5 py-6 md:px-8 lg:px-10"
                        >
                            <div
                                class="flex min-h-[130px] items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50"
                            >
                                <div class="text-center">
                                    <div
                                        class="mx-auto flex size-11 items-center justify-center rounded-xl bg-white shadow-sm"
                                    >
                                        <ImageIcon
                                            class="size-5 text-slate-300"
                                        />
                                    </div>

                                    <p
                                        class="mt-3 text-sm font-medium text-slate-400"
                                    >
                                        Artikel tidak memiliki gambar utama
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- =========================================
                             ARTICLE CONTENT
                        ========================================== -->
                        <div
                            class="border-t border-slate-100 px-5 py-7 md:px-8 md:py-8 lg:px-10"
                        >
                            <div class="max-w-[850px]">
                                <div
                                    class="whitespace-pre-line text-[15px] leading-8 text-slate-700 md:text-base"
                                >
                                    {{ article.content }}
                                </div>

                                <!-- TAGS -->
                                <div
                                    v-if="article.tags && article.tags.length"
                                    class="mt-8 border-t border-slate-100 pt-5"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <Tag
                                            class="mr-1 size-4 text-slate-400"
                                        />

                                        <span
                                            v-for="tag in article.tags"
                                            :key="tag.id"
                                            class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600"
                                        >
                                            #{{ tag.name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- =============================================
                         ADMIN PREVIEW NOTE
                    ============================================== -->
                    <div
                        class="rounded-2xl border border-orange-100 bg-orange-50/60 p-5"
                    >
                        <div class="flex items-start gap-3">
                            <Eye
                                class="mt-0.5 size-5 shrink-0 text-orange-500"
                            />

                            <div>
                                <p
                                    class="text-sm font-semibold text-orange-700"
                                >
                                    Mode Preview Admin
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-orange-600/80"
                                >
                                    Halaman ini hanya untuk meninjau isi artikel
                                    di panel admin. Artikel Draft tetap dapat
                                    dipreview tanpa harus diterbitkan ke halaman
                                    publik.
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <!-- =================================================
                     SIDEBAR
                ================================================== -->
                <aside class="min-w-0 space-y-6 xl:sticky xl:top-24">
                    <!-- INFORMASI -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-orange-50"
                            >
                                <Newspaper class="size-4 text-orange-500" />
                            </div>

                            <div>
                                <h3 class="font-semibold text-[#071a36]">
                                    Informasi Artikel
                                </h3>

                                <p class="text-xs text-slate-400">
                                    Detail publikasi.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 divide-y divide-slate-100">
                            <!-- STATUS -->
                            <div
                                class="flex items-center justify-between gap-4 py-3"
                            >
                                <span class="text-xs text-slate-400">
                                    Status
                                </span>

                                <span
                                    class="rounded-full border px-2.5 py-1 text-xs font-semibold"
                                    :class="statusClass"
                                >
                                    {{ statusLabel }}
                                </span>
                            </div>

                            <!-- CATEGORY -->
                            <div
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <span class="shrink-0 text-xs text-slate-400">
                                    Kategori
                                </span>

                                <span
                                    class="min-w-0 text-right text-xs font-semibold leading-5 text-slate-700"
                                >
                                    {{ article.category?.name ?? "-" }}
                                </span>
                            </div>

                            <!-- REGION -->
                            <div
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <span class="shrink-0 text-xs text-slate-400">
                                    Wilayah
                                </span>

                                <span
                                    class="min-w-0 text-right text-xs font-semibold leading-5 text-slate-700"
                                >
                                    {{ regionLabel }}
                                </span>
                            </div>

                            <!-- AUTHOR -->
                            <div
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <span class="shrink-0 text-xs text-slate-400">
                                    Penulis
                                </span>

                                <span
                                    class="min-w-0 text-right text-xs font-semibold leading-5 text-slate-700"
                                >
                                    {{ article.author?.name ?? "-" }}
                                </span>
                            </div>

                            <!-- VIEWS -->
                            <div
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <span class="text-xs text-slate-400">
                                    Dilihat
                                </span>

                                <span
                                    class="text-xs font-semibold text-slate-700"
                                >
                                    {{ article.views ?? 0 }}
                                </span>
                            </div>

                            <!-- UPDATED -->
                            <div
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <span class="shrink-0 text-xs text-slate-400">
                                    Terakhir diubah
                                </span>

                                <span
                                    class="min-w-0 text-right text-xs font-semibold leading-5 text-slate-700"
                                >
                                    {{ updatedDate }}
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- =============================================
                         PENANDA ARTIKEL
                    ============================================== -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <h3 class="font-semibold text-[#071a36]">
                            Penanda Artikel
                        </h3>

                        <div class="mt-4 space-y-3">
                            <!-- FEATURED -->
                            <div
                                class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        article.is_featured
                                            ? 'bg-amber-100 text-amber-600'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    <Star class="size-4" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Artikel Pilihan
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        {{
                                            article.is_featured
                                                ? "Aktif"
                                                : "Tidak aktif"
                                        }}
                                    </p>
                                </div>
                            </div>

                            <!-- BREAKING -->
                            <div
                                class="flex items-center gap-3 rounded-xl border border-slate-100 p-3"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        article.is_breaking
                                            ? 'bg-red-100 text-red-500'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    <Zap class="size-4" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        Breaking News
                                    </p>

                                    <p class="text-xs text-slate-400">
                                        {{
                                            article.is_breaking
                                                ? "Aktif"
                                                : "Tidak aktif"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- =============================================
                         SEO
                    ============================================== -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-2">
                            <Search class="size-4 text-violet-500" />

                            <h3 class="font-semibold text-[#071a36]">
                                Preview SEO
                            </h3>
                        </div>

                        <div
                            class="mt-4 rounded-xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <p
                                class="break-all text-xs leading-5 text-emerald-600"
                            >
                                lintasara.id › berita › {{ article.slug }}
                            </p>

                            <p
                                class="mt-2 line-clamp-2 text-[15px] font-semibold leading-5 text-blue-700"
                            >
                                {{ seoTitle }}
                            </p>

                            <p
                                class="mt-2 line-clamp-3 text-xs leading-5 text-slate-500"
                            >
                                {{ seoDescription }}
                            </p>
                        </div>
                    </section>

                    <!-- =============================================
                         URL
                    ============================================== -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <div class="flex items-center gap-2">
                            <FileText class="size-4 text-slate-400" />

                            <h3 class="font-semibold text-[#071a36]">
                                URL Artikel
                            </h3>
                        </div>

                        <div
                            class="mt-4 break-all rounded-xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500"
                        >
                            /berita/{{ article.slug }}
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</template>
