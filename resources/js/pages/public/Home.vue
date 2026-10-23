<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    ArrowUpRight,
    CalendarDays,
    Clock3,
    Flame,
    Instagram,
    Mail,
    MapPin,
    Menu,
    Newspaper,
    Search,
    TrendingUp,
    X,
} from "lucide-vue-next";

type Article = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    thumbnail_url: string | null;
    thumbnail_alt: string | null;
    published_at: string | null;
    views: number;
    is_featured: boolean;
    is_breaking: boolean;
    category: { name: string; slug: string } | null;
    region: { name: string; slug: string } | null;
    author: { name: string } | null;
};

type Category = {
    id: number;
    name: string;
    slug: string;
};

const props = defineProps<{
    featured: Article[];
    latest: Article[];
    breaking: Article[];
    popular: Article[];
    categories: Category[];
}>();

const mobileMenuOpen = ref(false);

/*
|--------------------------------------------------------------------------
| DATA HOMEPAGE
|--------------------------------------------------------------------------
| HomeController sudah memisahkan featured dan latest.
*/
const mainHeadline = computed(() => props.featured[0] ?? null);

const sideHeadlines = computed(() => props.featured.slice(1, 5));

const latestArticles = computed(() => props.latest.slice(0, 8));

/*
|--------------------------------------------------------------------------
| LINK ARTIKEL
|--------------------------------------------------------------------------
| Halaman detail /berita/{slug} akan dibuat pada tahap berikutnya.
*/
function articleUrl(article: Article): string {
    return `/berita/${encodeURIComponent(article.slug)}`;
}

/*
|--------------------------------------------------------------------------
| KONTAK RESMI LINTASARA
|--------------------------------------------------------------------------
| Isi hanya dengan informasi yang sudah resmi.
| Biarkan kosong jika belum tersedia.
*/
const contactEmail: string = "lintasara@gmail.com";
const contactWhatsapp = ref<string>("");
const contactLocation: string = "Lubuklinggau, Sumatera Selatan, Indonesia";

type SocialLink = {
    name: string;
    url: string;
    icon: typeof Instagram;
};

const socialLinks: SocialLink[] = [
    // Contoh setelah akun resmi tersedia:
    // {
    //     name: "Instagram",
    //     url: "https://instagram.com/akunresmi",
    //     icon: Instagram,
    // },
];

const hasContact = computed(
    () =>
        Boolean(contactEmail) ||
        Boolean(contactWhatsapp) ||
        Boolean(contactLocation) ||
        socialLinks.length > 0,
);

const currentYear = new Date().getFullYear();

const today = new Intl.DateTimeFormat("id-ID", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
    timeZone: "Asia/Jakarta",
}).format(new Date());

function formatDate(date: string | null): string {
    if (!date) return "";

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        timeZone: "Asia/Jakarta",
    }).format(new Date(date));
}

function formatShortDate(date: string | null): string {
    if (!date) return "";

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
        timeZone: "Asia/Jakarta",
    }).format(new Date(date));
}

function formatViews(views: number): string {
    return Number(views || 0).toLocaleString("id-ID");
}
</script>

<template>
    <Head title="Beranda | Lintasara">
        <meta
            name="description"
            content="Lintasara menyajikan berita terkini, informasi relevan, dan perspektif yang lebih luas."
        />
    </Head>

    <div class="min-h-screen bg-[#fafaf8] font-sans text-slate-900">
        <!-- =====================================================
             TOP BAR
        ====================================================== -->
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

        <!-- =====================================================
             HEADER
        ====================================================== -->
        <header
            class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur-xl"
        >
            <div class="mx-auto max-w-[1320px] px-5 lg:px-8">
                <div class="flex h-[76px] items-center justify-between gap-5">
                    <!-- LOGO -->
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

                    <!-- DESKTOP NAVIGATION -->
                    <nav
                        class="hidden items-center gap-7 lg:flex"
                        aria-label="Navigasi utama"
                    >
                        <Link
                            href="/"
                            class="text-[13px] font-bold text-orange-600"
                        >
                            Beranda
                        </Link>

                        <span
                            v-for="category in categories.slice(0, 6)"
                            :key="category.id"
                            class="cursor-default text-[13px] font-semibold text-slate-600"
                            :title="`${category.name} — halaman kategori segera hadir`"
                        >
                            {{ category.name }}
                        </span>
                    </nav>

                    <!-- HEADER ACTIONS -->
                    <div class="flex items-center gap-3">
                        <span
                            class="hidden text-slate-500 sm:flex"
                            title="Pencarian akan tersedia pada tahap berikutnya"
                        >
                            <Search class="size-5" />
                        </span>

                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 p-2.5 lg:hidden"
                            :aria-label="
                                mobileMenuOpen ? 'Tutup menu' : 'Buka menu'
                            "
                            :aria-expanded="mobileMenuOpen"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <X v-if="mobileMenuOpen" class="size-5" />

                            <Menu v-else class="size-5" />
                        </button>
                    </div>
                </div>

                <!-- MOBILE NAVIGATION -->
                <div
                    v-if="mobileMenuOpen"
                    class="space-y-1 border-t border-slate-100 py-4 lg:hidden"
                >
                    <Link
                        href="/"
                        class="block rounded-lg bg-orange-50 px-4 py-3 text-sm font-bold text-orange-600"
                        @click="mobileMenuOpen = false"
                    >
                        Beranda
                    </Link>

                    <div
                        v-for="category in categories"
                        :key="category.id"
                        class="px-4 py-3 text-sm text-slate-500"
                    >
                        {{ category.name }}
                    </div>
                </div>
            </div>
        </header>

        <!-- =====================================================
             MAIN CONTENT
        ====================================================== -->
        <main class="mx-auto max-w-[1320px] px-5 pb-20 lg:px-8">
            <!-- =================================================
                 INTRO
            ================================================== -->
            <section class="border-b border-slate-200 pb-8 pt-10 lg:pt-14">
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <div class="mb-4 flex items-center gap-2">
                            <span class="h-0.5 w-7 bg-orange-500"></span>

                            <span
                                class="text-[11px] font-extrabold uppercase tracking-[0.2em] text-orange-600"
                            >
                                Lintasara Editorial
                            </span>
                        </div>

                        <h1
                            class="max-w-3xl text-4xl font-black leading-[1.12] tracking-[-0.055em] sm:text-5xl lg:text-[64px]"
                        >
                            Cerita hari ini.
                            <span class="text-orange-500">
                                Sudut pandang baru.
                            </span>
                        </h1>

                        <p
                            class="mt-5 max-w-xl text-sm leading-7 text-slate-500"
                        >
                            Menghadirkan informasi terkini, fakta yang relevan,
                            dan cerita dari berbagai penjuru.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-2 text-xs font-semibold text-slate-500"
                    >
                        <CalendarDays class="size-4 text-orange-500" />

                        {{ today }}
                    </div>
                </div>
            </section>

            <!-- =================================================
                 BREAKING NEWS
            ================================================== -->
            <section
                v-if="breaking.length"
                aria-label="Breaking News"
                class="mt-7 flex min-h-[56px] overflow-hidden rounded-xl border border-orange-100 bg-orange-50"
            >
                <div
                    class="relative z-10 flex shrink-0 items-center gap-2 bg-orange-500 px-4 text-[11px] font-extrabold uppercase tracking-wider text-white sm:px-5"
                >
                    <Flame class="size-4" />

                    <span class="hidden sm:inline"> Breaking News </span>

                    <span class="sm:hidden"> Breaking </span>
                </div>

                <div
                    class="ticker-window relative flex min-w-0 flex-1 items-center overflow-hidden"
                >
                    <div class="ticker-track flex w-max shrink-0 items-center">
                        <!-- Dua salinan untuk infinite loop -->
                        <div
                            v-for="copy in 2"
                            :key="copy"
                            class="ticker-set flex shrink-0 items-center"
                            :aria-hidden="copy === 2 ? true : undefined"
                        >
                            <a
                                v-for="article in breaking"
                                :key="`${copy}-${article.id}`"
                                :href="articleUrl(article)"
                                :tabindex="copy === 2 ? -1 : undefined"
                                class="inline-flex shrink-0 items-center gap-3 px-6 py-3 text-[13px] font-semibold text-slate-800 transition hover:text-orange-600 focus-visible:outline-2 focus-visible:outline-orange-500"
                            >
                                <span
                                    class="size-1.5 shrink-0 rounded-full bg-orange-500"
                                ></span>

                                <span>
                                    {{ article.title }}
                                </span>

                                <ArrowUpRight
                                    class="size-3.5 shrink-0 text-orange-500"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================
                 SOROTAN UTAMA
            ================================================== -->
            <section class="pt-12">
                <!-- SECTION HEADER -->
                <div class="mb-7 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="h-6 w-1 rounded-full bg-orange-500"></span>

                        <h2
                            class="text-xl font-black tracking-tight sm:text-2xl"
                        >
                            Sorotan Utama
                        </h2>
                    </div>

                    <span
                        class="text-right text-[10px] font-bold uppercase tracking-widest text-slate-400 sm:text-[11px]"
                    >
                        Pilihan Redaksi
                    </span>
                </div>

                <!-- FEATURED GRID -->
                <div
                    v-if="mainHeadline"
                    class="grid items-stretch gap-6 lg:grid-cols-[1.5fr_1fr]"
                >
                    <!-- =========================================
                         MAIN HEADLINE
                         Tinggi mengikuti grid kanan.
                    ========================================== -->
                    <article
                        class="group flex min-w-0 flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/80"
                    >
                        <!-- MAIN IMAGE -->
                        <div
                            class="relative aspect-video shrink-0 overflow-hidden bg-slate-100"
                        >
                            <a
                                :href="articleUrl(mainHeadline)"
                                class="block h-full w-full"
                                :aria-label="`Baca ${mainHeadline.title}`"
                            >
                                <img
                                    v-if="mainHeadline.thumbnail_url"
                                    :src="mainHeadline.thumbnail_url"
                                    :alt="
                                        mainHeadline.thumbnail_alt ||
                                        mainHeadline.title
                                    "
                                    class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-[1.035]"
                                />

                                <div
                                    v-else
                                    class="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 to-orange-50"
                                >
                                    <Newspaper class="size-16 text-slate-300" />
                                </div>
                            </a>

                            <span
                                class="absolute left-5 top-5 rounded-md bg-orange-500 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-wider text-white"
                            >
                                Headline
                            </span>
                        </div>

                        <!-- MAIN CONTENT -->
                        <div class="flex flex-1 flex-col p-6 sm:p-8">
                            <div
                                class="mb-4 flex flex-wrap items-center gap-3 text-xs"
                            >
                                <span
                                    class="font-extrabold uppercase tracking-wider text-orange-600"
                                >
                                    {{
                                        mainHeadline.category?.name || "Berita"
                                    }}
                                </span>

                                <span class="text-slate-300"> • </span>

                                <span class="text-slate-500">
                                    {{ formatDate(mainHeadline.published_at) }}
                                </span>
                            </div>

                            <h3
                                class="text-2xl font-black leading-tight tracking-tight sm:text-3xl"
                            >
                                <a
                                    :href="articleUrl(mainHeadline)"
                                    class="transition hover:text-orange-600"
                                >
                                    {{ mainHeadline.title }}
                                </a>
                            </h3>

                            <p
                                v-if="mainHeadline.excerpt"
                                class="mt-4 line-clamp-3 text-sm leading-7 text-slate-500"
                            >
                                {{ mainHeadline.excerpt }}
                            </p>

                            <!--
                                mt-auto mendorong bagian redaksi
                                ke batas bawah kartu.
                            -->
                            <div
                                class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5"
                                :class="mainHeadline.excerpt ? 'mt-6' : 'mt-8'"
                            >
                                <span class="text-xs text-slate-500">
                                    {{
                                        mainHeadline.author?.name ||
                                        "Redaksi Lintasara"
                                    }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-2 text-xs font-bold text-orange-600"
                                >
                                    Sorotan redaksi
                                </span>
                            </div>
                        </div>
                    </article>

                    <!-- =========================================
                         SIDE HEADLINES
                         Dua baris mengisi tinggi kolom.
                    ========================================== -->
                    <div
                        v-if="sideHeadlines.length"
                        class="grid min-w-0 auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-2"
                    >
                        <article
                            v-for="article in sideHeadlines"
                            :key="article.id"
                            class="group flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white p-3"
                        >
                            <div
                                class="aspect-video w-full shrink-0 overflow-hidden rounded-lg bg-slate-100"
                            >
                                <a
                                    :href="articleUrl(article)"
                                    class="block h-full w-full"
                                    :aria-label="`Baca ${article.title}`"
                                >
                                    <img
                                        v-if="article.thumbnail_url"
                                        :src="article.thumbnail_url"
                                        :alt="
                                            article.thumbnail_alt ||
                                            article.title
                                        "
                                        class="h-full w-full object-cover object-center transition duration-300 group-hover:scale-105"
                                        loading="lazy"
                                    />

                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center"
                                    >
                                        <Newspaper
                                            class="size-8 text-slate-300"
                                        />
                                    </div>
                                </a>
                            </div>

                            <div
                                class="flex min-w-0 flex-1 flex-col gap-2 px-1 pb-1 pt-3"
                            >
                                <span
                                    class="text-[10px] font-extrabold uppercase tracking-wider text-orange-600"
                                >
                                    {{ article.category?.name || "Berita" }}
                                </span>

                                <h3
                                    class="line-clamp-3 text-sm font-bold leading-5 text-slate-900"
                                >
                                    <a
                                        :href="articleUrl(article)"
                                        class="transition hover:text-orange-600"
                                    >
                                        {{ article.title }}
                                    </a>
                                </h3>

                                <span
                                    class="mt-auto pt-1 text-[11px] text-slate-400"
                                >
                                    {{ formatShortDate(article.published_at) }}
                                </span>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div
                    v-else
                    class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-20 text-center"
                >
                    <div
                        class="mx-auto mb-5 flex size-16 items-center justify-center rounded-2xl bg-orange-50"
                    >
                        <Newspaper class="size-8 text-orange-500" />
                    </div>

                    <h3 class="text-xl font-bold">
                        Belum ada berita yang diterbitkan
                    </h3>

                    <p
                        class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-500"
                    >
                        Berita akan muncul di halaman ini setelah artikel
                        diterbitkan melalui dashboard Lintasara.
                    </p>
                </div>
            </section>

            <!-- =================================================
                 BERITA TERBARU + SIDEBAR
            ================================================== -->
            <section
                class="mt-16 grid gap-12 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <!-- LATEST ARTICLES -->
                <div class="min-w-0">
                    <div
                        class="mb-7 flex items-center justify-between border-b border-slate-200 pb-5"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="h-6 w-1 rounded-full bg-orange-500"
                            ></span>

                            <h2
                                class="text-xl font-black tracking-tight sm:text-2xl"
                            >
                                Berita Terbaru
                            </h2>
                        </div>

                        <Clock3 class="size-5 text-slate-400" />
                    </div>

                    <div
                        v-if="latestArticles.length"
                        class="grid gap-6 sm:grid-cols-2"
                    >
                        <article
                            v-for="article in latestArticles"
                            :key="article.id"
                            class="group flex min-w-0 flex-col overflow-hidden rounded-xl bg-white ring-1 ring-slate-200/80"
                        >
                            <!-- ARTICLE IMAGE -->
                            <div
                                class="aspect-video overflow-hidden bg-slate-100"
                            >
                                <a
                                    :href="articleUrl(article)"
                                    class="block h-full w-full"
                                    :aria-label="`Baca ${article.title}`"
                                >
                                    <img
                                        v-if="article.thumbnail_url"
                                        :src="article.thumbnail_url"
                                        :alt="
                                            article.thumbnail_alt ||
                                            article.title
                                        "
                                        class="h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                                        loading="lazy"
                                    />

                                    <div
                                        v-else
                                        class="flex h-full items-center justify-center"
                                    >
                                        <Newspaper
                                            class="size-10 text-slate-300"
                                        />
                                    </div>
                                </a>
                            </div>

                            <!-- ARTICLE CONTENT -->
                            <div class="flex flex-1 flex-col p-5">
                                <div
                                    class="mb-3 flex flex-wrap items-center justify-between gap-3"
                                >
                                    <span
                                        class="text-[10px] font-extrabold uppercase tracking-wider text-orange-600"
                                    >
                                        {{ article.category?.name || "Berita" }}
                                    </span>

                                    <span class="text-[11px] text-slate-400">
                                        {{
                                            formatShortDate(
                                                article.published_at,
                                            )
                                        }}
                                    </span>
                                </div>

                                <h3
                                    class="line-clamp-3 text-lg font-extrabold leading-snug tracking-tight"
                                >
                                    <a
                                        :href="articleUrl(article)"
                                        class="transition hover:text-orange-600"
                                    >
                                        {{ article.title }}
                                    </a>
                                </h3>

                                <p
                                    v-if="article.excerpt"
                                    class="mt-3 line-clamp-2 text-xs leading-6 text-slate-500"
                                >
                                    {{ article.excerpt }}
                                </p>

                                <div
                                    class="mt-auto border-t border-slate-100 pt-4"
                                    :class="article.excerpt ? 'mt-5' : 'mt-6'"
                                >
                                    <a
                                        :href="articleUrl(article)"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-orange-600 hover:underline"
                                    >
                                        Baca selengkapnya

                                        <ArrowUpRight class="size-3.5" />
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- NO LATEST -->
                    <div
                        v-else
                        class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500"
                    >
                        Belum ada berita terbaru lainnya.
                    </div>

                    <!-- ALL NEWS PLACEHOLDER -->
                    <div
                        v-if="latestArticles.length"
                        class="mt-8 flex justify-center"
                    >
                        <span
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-5 py-3 text-xs font-bold text-slate-400"
                            title="Halaman arsip berita akan dibuat pada tahap berikutnya"
                        >
                            Lihat Semua Berita

                            <ArrowUpRight class="size-4" />

                            <span class="text-[10px] font-normal">
                                (segera hadir)
                            </span>
                        </span>
                    </div>
                </div>

                <!-- =============================================
                     SIDEBAR
                ============================================== -->
                <aside class="min-w-0 space-y-8">
                    <!-- POPULAR -->
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
                                v-for="(article, index) in popular"
                                :key="article.id"
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
                                        {{ article.category?.name || "Berita" }}
                                    </span>

                                    <h3
                                        class="mt-1 line-clamp-3 text-sm font-bold leading-6"
                                    >
                                        <a
                                            :href="articleUrl(article)"
                                            class="transition hover:text-orange-600"
                                        >
                                            {{ article.title }}
                                        </a>
                                    </h3>

                                    <span
                                        class="mt-2 block text-[11px] text-slate-400"
                                    >
                                        {{ formatViews(article.views) }}
                                        pembaca
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p v-else class="text-sm leading-6 text-slate-500">
                            Berita populer akan ditampilkan setelah artikel
                            diterbitkan.
                        </p>
                    </div>

                    <!-- PROMOTIONAL CARD -->
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

                    <!-- CATEGORIES -->
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-6"
                    >
                        <h3 class="mb-5 text-base font-black">
                            Jelajahi Kategori
                        </h3>

                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="category in categories"
                                :key="category.id"
                                class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600"
                                :title="`Halaman ${category.name} segera hadir`"
                            >
                                {{ category.name }}
                            </span>
                        </div>

                        <p
                            v-if="!categories.length"
                            class="text-xs text-slate-500"
                        >
                            Kategori belum tersedia.
                        </p>
                    </div>
                </aside>
            </section>
        </main>

        <!-- =====================================================
             FOOTER LINTASARA
        ====================================================== -->
        <footer class="bg-[#142136] text-white">
            <div class="mx-auto max-w-[1320px] px-5 pb-7 pt-14 lg:px-8">
                <div
                    class="grid gap-10 border-b border-white/10 pb-12 md:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1.2fr]"
                >
                    <!-- =========================================
                         FOOTER BRAND
                    ========================================== -->
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

                    <!-- =========================================
                         FOOTER NAVIGATION
                    ========================================== -->
                    <div>
                        <h3 class="text-sm font-extrabold">Jelajahi</h3>

                        <div class="mt-5 space-y-3 text-sm text-slate-300">
                            <Link
                                href="/"
                                class="block transition hover:text-orange-400"
                            >
                                Beranda
                            </Link>

                            <span
                                class="block text-slate-400"
                                title="Halaman arsip akan dibuat"
                            >
                                Semua Berita
                            </span>

                            <span
                                v-for="category in categories.slice(0, 5)"
                                :key="category.id"
                                class="block text-slate-400"
                            >
                                {{ category.name }}
                            </span>
                        </div>
                    </div>

                    <!-- =========================================
                         FOOTER INFORMATION
                    ========================================== -->
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

                    <!-- =========================================
                         FOOTER CONTACT
                    ========================================== -->
                    <div>
                        <h3 class="text-sm font-extrabold">Kontak Redaksi</h3>

                        <p class="mt-5 text-sm leading-6 text-slate-300">
                            Punya informasi, masukan, atau ingin menghubungi
                            redaksi Lintasara? Temukan kanal komunikasi resmi
                            kami di bagian ini.
                        </p>

                        <!-- EMAIL -->
                        <a
                            v-if="contactEmail"
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

                        <!-- WHATSAPP -->
                        <a
                            v-if="contactWhatsapp"
                            :href="`https://wa.me/${contactWhatsapp.replace(/\D/g, '')}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-4 flex items-center gap-3 text-sm text-slate-200 transition hover:text-orange-400"
                        >
                            <span
                                class="flex size-4 shrink-0 items-center justify-center rounded-full border border-orange-400 text-[10px] font-bold text-orange-400"
                            >
                                W
                            </span>

                            <span> WhatsApp Redaksi </span>

                            <ArrowUpRight class="size-3.5" />
                        </a>

                        <!-- LOCATION -->
                        <div
                            v-if="contactLocation"
                            class="mt-4 flex items-start gap-3 text-sm text-slate-300"
                        >
                            <MapPin
                                class="mt-0.5 size-4 shrink-0 text-orange-400"
                            />

                            <span>
                                {{ contactLocation }}
                            </span>
                        </div>

                        <!-- SOCIAL MEDIA -->
                        <div v-if="socialLinks.length" class="mt-5">
                            <p
                                class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Ikuti Kami
                            </p>

                            <div class="flex flex-wrap gap-2">
                                <a
                                    v-for="social in socialLinks"
                                    :key="social.name"
                                    :href="social.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :aria-label="social.name"
                                    :title="social.name"
                                    class="flex size-10 items-center justify-center rounded-lg bg-white/10 transition hover:bg-orange-500"
                                >
                                    <component
                                        :is="social.icon"
                                        class="size-4"
                                    />
                                </a>
                            </div>
                        </div>

                        <!-- NO CONTACT YET -->
                        <div
                            v-if="!hasContact"
                            class="mt-5 rounded-lg border border-white/10 bg-white/5 px-4 py-3"
                        >
                            <div class="flex items-start gap-2">
                                <Mail
                                    class="mt-0.5 size-4 shrink-0 text-orange-400"
                                />

                                <p class="text-xs leading-5 text-slate-400">
                                    Email dan media sosial resmi Lintasara akan
                                    ditampilkan setelah tersedia.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER BOTTOM -->
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
/*
|--------------------------------------------------------------------------
| BREAKING NEWS TICKER
|--------------------------------------------------------------------------
| Dua salinan identik membuat judul berita bergerak
| terus-menerus dari kanan ke kiri.
*/
.ticker-track {
    animation: lintasara-ticker 55s linear infinite;
    will-change: transform;
}

.ticker-window:hover .ticker-track,
.ticker-window:focus-within .ticker-track {
    animation-play-state: paused;
}

@keyframes lintasara-ticker {
    from {
        transform: translateX(0);
    }

    to {
        transform: translateX(-50%);
    }
}

/*
|--------------------------------------------------------------------------
| AKSESIBILITAS
|--------------------------------------------------------------------------
| Pengguna yang mengurangi animasi di sistem
| tidak dipaksa melihat running text.
*/
@media (prefers-reduced-motion: reduce) {
    .ticker-track {
        animation: none;
        will-change: auto;
    }

    .ticker-window {
        overflow-x: auto;
    }
}
</style>
