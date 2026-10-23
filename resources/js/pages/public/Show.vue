<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    ArrowLeft,
    CalendarDays,
    Check,
    ChevronRight,
    Clock3,
    Copy,
    Eye,
    Mail,
    MapPin,
    Menu,
    Newspaper,
    Share2,
    Tag,
    TrendingUp,
    UserRound,
    X,
} from "lucide-vue-next";

type ArticleCard = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    thumbnail_url: string | null;
    thumbnail_alt: string | null;
    published_at: string | null;
    views: number;
    category: {
        name: string;
        slug: string;
    } | null;
};

type Article = ArticleCard & {
    content: string | null;
    image_caption: string | null;
    image_credit: string | null;
    meta_title: string | null;
    meta_description: string | null;
    region: {
        name: string;
        slug: string;
    } | null;
    author: {
        name: string;
    } | null;
    tags: {
        name: string;
        slug: string;
    }[];
};

const props = defineProps<{
    article: Article;
    related: ArticleCard[];
    popular: ArticleCard[];
}>();

const mobileMenuOpen = ref(false);
const copied = ref(false);

const contactEmail = "lintasara@gmail.com";
const contactLocation = "Lubuklinggau, Sumatera Selatan, Indonesia";
const currentYear = new Date().getFullYear();

const articleUrl = (article: ArticleCard): string =>
    `/berita/${encodeURIComponent(article.slug)}`;

const canonicalUrl = computed(() => {
    if (typeof window === "undefined") {
        return articleUrl(props.article);
    }

    return new URL(articleUrl(props.article), window.location.origin).href;
});

const pageTitle = computed(
    () => props.article.meta_title?.trim() || props.article.title,
);

const pageDescription = computed(
    () =>
        props.article.meta_description?.trim() ||
        props.article.excerpt?.trim() ||
        props.article.title,
);

const articleContent = computed(() => {
    const content = props.article.content || "";

    if (!content.trim()) {
        return "";
    }

    // Konten Tiptap disimpan sebagai HTML.
    // Konten lama berupa teks biasa tetap didukung.
    if (/<(?:p|h2|h3|ul|ol|blockquote|br)\b/i.test(content)) {
        return content;
    }

    const escapeHtml = (value: string) =>
        value
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");

    return content
        .replace(/\r\n?/g, "\n")
        .split(/\n\s*\n/)
        .filter((paragraph) => paragraph.trim())
        .map(
            (paragraph) =>
                `<p>${escapeHtml(paragraph.trim()).replace(/\n/g, "<br>")}</p>`,
        )
        .join("\n");
});

const hasArticleContent = computed(
    () => articleContent.value.trim().length > 0,
);

const readingMinutes = computed(() => {
    const plainText = (props.article.content || "")
        .replace(/<[^>]*>/g, " ")
        .replace(/&nbsp;/gi, " ")
        .trim();

    const words = plainText.split(/\s+/).filter(Boolean).length;

    return Math.max(1, Math.ceil(words / 200));
});

function formatDate(date: string | null): string {
    if (!date) return "";

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        timeZone: "Asia/Jakarta",
    }).format(new Date(date));
}

function formatViews(views: number): string {
    return Number(views || 0).toLocaleString("id-ID");
}

function shareUrl(platform: "whatsapp" | "facebook" | "x"): string {
    const url = encodeURIComponent(canonicalUrl.value);
    const title = encodeURIComponent(props.article.title);

    if (platform === "whatsapp") {
        return `https://api.whatsapp.com/send?text=${title}%20${url}`;
    }

    if (platform === "facebook") {
        return `https://www.facebook.com/sharer/sharer.php?u=${url}`;
    }

    return `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
}

async function copyLink(): Promise<void> {
    if (typeof navigator === "undefined" || !navigator.clipboard) {
        return;
    }

    try {
        await navigator.clipboard.writeText(canonicalUrl.value);
        copied.value = true;
    } catch {
        copied.value = false;
    }
}
</script>

<template>
    <Head :title="`${pageTitle} | Lintasara`">
        <meta name="description" :content="pageDescription" />

        <meta property="og:type" content="article" />

        <meta property="og:title" :content="pageTitle" />

        <meta property="og:description" :content="pageDescription" />

        <meta
            v-if="article.thumbnail_url"
            property="og:image"
            :content="article.thumbnail_url"
        />

        <meta property="og:url" :content="canonicalUrl" />

        <link rel="canonical" :href="canonicalUrl" />
    </Head>

    <div class="min-h-screen bg-[#fafaf8] font-sans text-slate-900">
        <!-- TOP BAR -->
        <div class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-[1320px] items-center justify-between px-5 py-2.5 text-[11px] text-slate-500 lg:px-8"
            >
                <span>
                    Informasi yang relevan. Perspektif yang lebih luas.
                </span>

                <span class="hidden sm:inline"> Portal Berita Independen </span>
            </div>
        </div>

        <!-- HEADER -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur-xl"
        >
            <div class="mx-auto max-w-[1320px] px-5 lg:px-8">
                <div class="flex h-[76px] items-center justify-between gap-5">
                    <Link href="/" class="flex shrink-0 items-center gap-3">
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-orange-500 shadow-lg shadow-orange-500/15"
                        >
                            <Newspaper class="size-5 text-white" />
                        </div>

                        <div>
                            <span
                                class="block text-[25px] font-black tracking-[-0.07em]"
                            >
                                lintas<span class="text-orange-500">ara.</span>
                            </span>

                            <span
                                class="block text-[9px] font-bold uppercase tracking-[0.22em] text-slate-400"
                            >
                                Portal Berita
                            </span>
                        </div>
                    </Link>

                    <nav
                        class="hidden items-center gap-7 lg:flex"
                        aria-label="Navigasi utama"
                    >
                        <Link
                            href="/"
                            class="text-[13px] font-semibold text-slate-600 transition hover:text-orange-600"
                        >
                            Beranda
                        </Link>

                        <span class="text-[13px] font-bold text-orange-600">
                            Detail Berita
                        </span>
                    </nav>

                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 p-2.5 lg:hidden"
                        :aria-expanded="mobileMenuOpen"
                        :aria-label="
                            mobileMenuOpen ? 'Tutup menu' : 'Buka menu'
                        "
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <X v-if="mobileMenuOpen" class="size-5" />

                        <Menu v-else class="size-5" />
                    </button>
                </div>

                <!-- MOBILE MENU -->
                <div
                    v-if="mobileMenuOpen"
                    class="space-y-1 border-t border-slate-100 py-4 lg:hidden"
                >
                    <Link
                        href="/"
                        class="block rounded-lg px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-orange-50"
                        @click="mobileMenuOpen = false"
                    >
                        Beranda
                    </Link>

                    <span
                        class="block rounded-lg bg-orange-50 px-4 py-3 text-sm font-bold text-orange-600"
                    >
                        Detail Berita
                    </span>
                </div>
            </div>
        </header>

        <!-- MAIN -->
        <main class="mx-auto max-w-[1320px] px-5 pb-20 lg:px-8">
            <!-- BREADCRUMB -->
            <nav
                aria-label="Breadcrumb"
                class="flex flex-wrap items-center gap-2 py-7 text-xs text-slate-500"
            >
                <Link href="/" class="transition hover:text-orange-600">
                    Beranda
                </Link>

                <ChevronRight class="size-3.5 text-slate-300" />

                <span class="font-semibold text-orange-600">
                    {{ article.category?.name || "Berita" }}
                </span>

                <ChevronRight class="size-3.5 text-slate-300" />

                <span class="max-w-[300px] truncate text-slate-400">
                    {{ article.title }}
                </span>
            </nav>

            <!-- ARTICLE HEADER FULL WIDTH -->
            <section class="min-w-0">
                <div class="mb-5 flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex rounded-md bg-orange-50 px-3 py-1.5 text-[11px] font-extrabold uppercase tracking-wider text-orange-600"
                    >
                        {{ article.category?.name || "Berita" }}
                    </span>

                    <span
                        v-if="article.region"
                        class="inline-flex items-center gap-1.5 text-xs text-slate-500"
                    >
                        <MapPin class="size-3.5" />
                        {{ article.region.name }}
                    </span>
                </div>

                <h1
                    class="max-w-5xl text-3xl font-black leading-[1.18] tracking-[-0.045em] text-[#071a36] sm:text-4xl lg:text-[43px]"
                >
                    {{ article.title }}
                </h1>

                <p
                    v-if="article.excerpt"
                    class="mt-5 max-w-5xl text-base leading-8 text-slate-500"
                >
                    {{ article.excerpt }}
                </p>

                <!-- ARTICLE META -->
                <div
                    class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3 border-y border-slate-200 py-5 text-xs text-slate-500 gap-2"
                >
                    <span class="inline-flex items-center gap-2">
                        <UserRound class="size-4 text-orange-500" />

                        <strong class="font-semibold text-slate-700">
                            {{ article.author?.name || "Redaksi Lintasara" }}
                        </strong>
                    </span>

                    <span class="inline-flex items-center gap-2">
                        <CalendarDays class="size-4 text-orange-500" />

                        {{ formatDate(article.published_at) }}
                    </span>

                    <span class="inline-flex items-center gap-2">
                        <Eye class="size-4 text-orange-500" />

                        {{ formatViews(article.views) }} pembaca
                    </span>

                    <span class="inline-flex items-center gap-2">
                        <Clock3 class="size-4 text-orange-500" />

                        {{ readingMinutes }} menit baca
                    </span>
                </div>
            </section>

            <!-- CONTENT GRID -->
            <!-- Foto dan sidebar mulai pada posisi yang sama -->
            <div
                class="mt-8 grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <!-- LEFT COLUMN -->
                <div class="min-w-0">
                    <!-- FEATURED IMAGE -->
                    <figure v-if="article.thumbnail_url" class="min-w-0">
                        <div class="overflow-hidden rounded-2xl bg-slate-100">
                            <img
                                :src="article.thumbnail_url"
                                :alt="article.thumbnail_alt || article.title"
                                class="aspect-video w-full object-cover"
                            />
                        </div>

                        <figcaption
                            v-if="article.image_caption || article.image_credit"
                            class="mt-3 text-xs leading-6 text-slate-500"
                        >
                            <span v-if="article.image_caption">
                                {{ article.image_caption }}
                            </span>

                            <span v-if="article.image_credit">
                                <span v-if="article.image_caption"> — </span>

                                Foto: {{ article.image_credit }}
                            </span>
                        </figcaption>
                    </figure>

                    <!-- ARTICLE CONTENT -->
                    <article
                        class="mt-9 rounded-2xl border border-slate-200/80 bg-white px-5 py-8 shadow-sm sm:px-9 sm:py-10"
                    >
                        <div
                            v-if="hasArticleContent"
                            class="article-body break-words text-[15px] leading-[1.95] text-slate-700 sm:text-[16px]"
                            v-html="articleContent"
                        ></div>

                        <p v-else class="text-sm text-slate-500">
                            Isi artikel belum tersedia.
                        </p>

                        <!-- TAGS -->
                        <div
                            v-if="article.tags?.length"
                            class="mt-10 border-t border-slate-100 pt-6"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <Tag class="mr-1 size-4 text-orange-500" />

                                <span
                                    v-for="tag in article.tags"
                                    :key="tag.slug"
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-600"
                                >
                                    #{{ tag.name }}
                                </span>
                            </div>
                        </div>

                        <!-- SHARE -->
                        <div
                            class="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-6"
                        >
                            <div class="flex items-center gap-2">
                                <Share2 class="size-4 text-orange-500" />

                                <span class="text-xs font-bold text-slate-700">
                                    Bagikan artikel
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <a
                                    :href="shareUrl('whatsapp')"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                >
                                    WhatsApp
                                </a>

                                <a
                                    :href="shareUrl('facebook')"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                >
                                    Facebook
                                </a>

                                <a
                                    :href="shareUrl('x')"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-orange-300 hover:text-orange-600"
                                >
                                    X
                                </a>

                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#071a36] px-3 py-2 text-xs font-semibold text-white transition hover:bg-orange-600"
                                    @click="copyLink"
                                >
                                    <Check v-if="copied" class="size-3.5" />

                                    <Copy v-else class="size-3.5" />

                                    {{ copied ? "Tersalin" : "Salin Link" }}
                                </button>
                            </div>
                        </div>
                    </article>

                    <!-- BACK -->
                    <Link
                        href="/"
                        class="mt-7 inline-flex items-center gap-2 text-xs font-bold text-orange-600 transition hover:text-orange-700"
                    >
                        <ArrowLeft class="size-4" />

                        Kembali ke Beranda
                    </Link>

                    <!-- RELATED ARTICLES -->
                    <section class="mt-14">
                        <div
                            class="mb-7 flex items-center justify-between gap-3 border-b border-slate-200 pb-5"
                        >
                            <div class="flex items-center gap-3">
                                <span
                                    class="h-6 w-1 rounded-full bg-orange-500"
                                ></span>

                                <h2
                                    class="text-xl font-black tracking-tight sm:text-2xl"
                                >
                                    Artikel Terkait
                                </h2>
                            </div>

                            <span
                                class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                            >
                                Pilihan untuk Anda
                            </span>
                        </div>

                        <div
                            v-if="related.length"
                            class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <article
                                v-for="item in related"
                                :key="item.id"
                                class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white"
                            >
                                <Link
                                    :href="articleUrl(item)"
                                    class="block aspect-video overflow-hidden bg-slate-100"
                                >
                                    <img
                                        v-if="item.thumbnail_url"
                                        :src="item.thumbnail_url"
                                        :alt="item.thumbnail_alt || item.title"
                                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    />

                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center"
                                    >
                                        <Newspaper
                                            class="size-9 text-slate-300"
                                        />
                                    </div>
                                </Link>

                                <div class="flex flex-1 flex-col p-4">
                                    <span
                                        class="text-[10px] font-extrabold uppercase tracking-wider text-orange-600"
                                    >
                                        {{ item.category?.name || "Berita" }}
                                    </span>

                                    <h3
                                        class="mt-2 line-clamp-3 text-sm font-bold leading-6"
                                    >
                                        <Link
                                            :href="articleUrl(item)"
                                            class="transition hover:text-orange-600"
                                        >
                                            {{ item.title }}
                                        </Link>
                                    </h3>

                                    <span
                                        class="mt-auto pt-4 text-[11px] text-slate-400"
                                    >
                                        {{ formatDate(item.published_at) }}
                                    </span>
                                </div>
                            </article>
                        </div>

                        <div
                            v-else
                            class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500"
                        >
                            Belum ada artikel terkait lainnya.
                        </div>
                    </section>
                </div>

                <!-- RIGHT COLUMN / SIDEBAR -->
                <!-- Sejajar dengan foto utama -->
                <aside class="min-w-0 space-y-7 lg:sticky lg:top-28">
                    <!-- POPULAR ARTICLES -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-6"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <TrendingUp class="size-5 text-orange-500" />

                            <h2 class="text-lg font-black">
                                Paling Banyak Dibaca
                            </h2>
                        </div>

                        <div
                            v-if="popular.length"
                            class="divide-y divide-slate-100"
                        >
                            <div
                                v-for="(item, index) in popular"
                                :key="item.id"
                                class="flex gap-4 py-5 first:pt-0 last:pb-0"
                            >
                                <span
                                    class="text-3xl font-black tracking-tight text-orange-200"
                                >
                                    {{ String(index + 1).padStart(2, "0") }}
                                </span>

                                <div class="min-w-0">
                                    <span
                                        class="text-[10px] font-bold uppercase text-orange-600"
                                    >
                                        {{ item.category?.name || "Berita" }}
                                    </span>

                                    <h3
                                        class="mt-1 line-clamp-3 text-sm font-bold leading-6"
                                    >
                                        <Link
                                            :href="articleUrl(item)"
                                            class="transition hover:text-orange-600"
                                        >
                                            {{ item.title }}
                                        </Link>
                                    </h3>

                                    <span
                                        class="mt-2 block text-[11px] text-slate-400"
                                    >
                                        {{ formatViews(item.views) }}
                                        pembaca
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p v-else class="text-sm text-slate-500">
                            Belum ada berita populer lainnya.
                        </p>
                    </div>

                    <!-- SIDEBAR PROMOTION -->
                    <div class="rounded-xl bg-[#172234] p-7 text-white">
                        <div
                            class="mb-5 flex size-11 items-center justify-center rounded-xl bg-orange-500"
                        >
                            <Newspaper class="size-5" />
                        </div>

                        <h3
                            class="text-2xl font-black leading-tight tracking-tight"
                        >
                            Lebih dekat dengan informasi.
                        </h3>

                        <p class="mt-4 text-sm leading-7 text-slate-300">
                            Lintasara menghadirkan perspektif dan informasi
                            untuk menemani setiap hari Anda.
                        </p>

                        <div
                            class="mt-7 h-1 w-12 rounded-full bg-orange-500"
                        ></div>
                    </div>
                </aside>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="bg-[#142136] text-white">
            <div class="mx-auto max-w-[1320px] px-5 pb-7 pt-14 lg:px-8">
                <div
                    class="grid gap-10 border-b border-white/10 pb-12 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1.2fr]"
                >
                    <!-- BRAND -->
                    <div>
                        <Link href="/" class="inline-flex items-center gap-3">
                            <span
                                class="flex size-11 items-center justify-center rounded-xl bg-orange-500"
                            >
                                <Newspaper class="size-5 text-white" />
                            </span>

                            <span
                                class="text-[26px] font-black tracking-[-0.07em]"
                            >
                                lintas<span class="text-orange-400">ara.</span>
                            </span>
                        </Link>

                        <p
                            class="mt-5 max-w-sm text-sm leading-7 text-slate-300"
                        >
                            Informasi yang relevan. Perspektif yang lebih luas.
                            Menghubungkan cerita, fakta, dan pembaca.
                        </p>

                        <p
                            class="mt-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-orange-400"
                        >
                            Portal Berita Independen
                        </p>
                    </div>

                    <!-- EXPLORE -->
                    <div>
                        <h3 class="text-sm font-extrabold">Jelajahi</h3>

                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <Link
                                href="/"
                                class="block transition hover:text-orange-400"
                            >
                                Beranda
                            </Link>

                            <span class="block text-slate-400">
                                Semua Berita (segera hadir)
                            </span>
                        </div>
                    </div>

                    <!-- INFORMATION -->
                    <div>
                        <h3 class="text-sm font-extrabold">Informasi</h3>

                        <div class="mt-5 space-y-3 text-sm text-slate-400">
                            <span class="block"> Tentang Kami </span>

                            <span class="block"> Redaksi </span>

                            <span class="block"> Hubungi Kami </span>

                            <span class="block"> Kebijakan Privasi </span>

                            <span class="block"> Pedoman Media Siber </span>
                        </div>

                        <p class="mt-5 text-[11px] leading-5 text-slate-500">
                            Halaman informasi akan tersedia secara bertahap.
                        </p>
                    </div>

                    <!-- CONTACT -->
                    <div>
                        <h3 class="text-sm font-extrabold">Kontak Redaksi</h3>

                        <p class="mt-5 text-sm leading-6 text-slate-300">
                            Punya informasi atau masukan? Hubungi redaksi
                            Lintasara melalui kanal resmi kami.
                        </p>

                        <a
                            :href="`mailto:${contactEmail}`"
                            class="mt-5 flex items-start gap-3 text-sm text-slate-200 transition hover:text-orange-400"
                        >
                            <Mail
                                class="mt-0.5 size-4 shrink-0 text-orange-400"
                            />

                            <span class="break-all">
                                {{ contactEmail }}
                            </span>
                        </a>

                        <div
                            class="mt-4 flex items-start gap-3 text-sm text-slate-300"
                        >
                            <MapPin
                                class="mt-0.5 size-4 shrink-0 text-orange-400"
                            />

                            <span>
                                {{ contactLocation }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- COPYRIGHT -->
                <div
                    class="flex flex-col justify-between gap-3 pt-6 text-[11px] text-slate-400 sm:flex-row sm:items-center"
                >
                    <span>
                        © {{ currentYear }} Lintasara. Seluruh hak cipta
                        dilindungi.
                    </span>

                    <span> Dibangun untuk cerita yang berarti. </span>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.article-body :deep(p) {
    margin: 0 0 1.4rem;
    line-height: 1.95;
}

.article-body :deep(h2) {
    margin: 2.2rem 0 1rem;
    color: #071a36;
    font-size: 1.55rem;
    font-weight: 800;
    line-height: 1.4;
}

.article-body :deep(h3) {
    margin: 1.8rem 0 0.85rem;
    color: #071a36;
    font-size: 1.25rem;
    font-weight: 750;
    line-height: 1.5;
}

.article-body :deep(strong) {
    font-weight: 700;
}

.article-body :deep(ul) {
    margin: 1.2rem 0;
    padding-left: 1.6rem;
    list-style: disc;
}

.article-body :deep(ol) {
    margin: 1.2rem 0;
    padding-left: 1.6rem;
    list-style: decimal;
}

.article-body :deep(li) {
    margin-bottom: 0.5rem;
    padding-left: 0.2rem;
}

.article-body :deep(blockquote) {
    margin: 1.8rem 0;
    border-left: 4px solid #f97316;
    padding: 0.6rem 0 0.6rem 1.25rem;
    color: #64748b;
    font-style: italic;
    background: #fff7ed;
}

.article-body :deep(a) {
    color: #ea580c;
    text-decoration: underline;
    text-underline-offset: 3px;
    overflow-wrap: anywhere;
}

.article-body :deep(a:hover) {
    color: #c2410c;
}

.article-body :deep(img) {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 1.8rem auto;
    border-radius: 0.75rem;
}

.article-body :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
