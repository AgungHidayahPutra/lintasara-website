<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
    ChevronRight,
    FileText,
    FolderOpen,
    Gauge,
    MapPin,
    Menu,
    Search,
    Tags,
    Users,
    X,
} from "lucide-vue-next";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

import AdminLogo from "./AdminLogo.vue";
import AdminUserMenu from "./AdminUserMenu.vue";

interface Breadcrumb {
    title: string;
    href?: string;
}

defineProps<{
    breadcrumbs?: Breadcrumb[];
}>();

const page = usePage();

const mobileOpen = ref(false);
const clientReady = ref(false);

const user = computed(() => page.props.auth.user);

const isAdmin = computed(() => user.value?.role === "admin");

/*
|--------------------------------------------------------------------------
| Menu Navigation
|--------------------------------------------------------------------------
*/

const contentMenus = computed(() => {
    const menus = [
        {
            title: "Artikel",
            href: "/admin/articles",
            icon: FileText,
        },
    ];

    if (isAdmin.value) {
        menus.push(
            {
                title: "Kategori",
                href: "/admin/categories",
                icon: FolderOpen,
            },
            {
                title: "Tag",
                href: "/admin/tags",
                icon: Tags,
            },
            {
                title: "Wilayah",
                href: "/admin/regions",
                icon: MapPin,
            },
        );
    }

    return menus;
});

const managementMenus = computed(() => {
    if (!isAdmin.value) {
        return [];
    }

    return [
        {
            title: "Pengguna",
            href: "/admin/users",
            icon: Users,
        },
    ];
});

/*
|--------------------------------------------------------------------------
| Active Navigation
|--------------------------------------------------------------------------
*/

function isActive(href: string) {
    const currentUrl = page.url.split("?")[0];

    if (href === "/admin") {
        return currentUrl === "/admin";
    }

    return currentUrl.startsWith(href);
}

/*
|--------------------------------------------------------------------------
| Mobile Sidebar
|--------------------------------------------------------------------------
*/

function closeMobileMenu() {
    mobileOpen.value = false;
}

function openMobileMenu() {
    mobileOpen.value = true;
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === "Escape") {
        closeMobileMenu();
    }
}

onMounted(() => {
    clientReady.value = true;
    window.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
    document.body.style.overflow = "";
});

/*
|--------------------------------------------------------------------------
| Lock Body Scroll
|--------------------------------------------------------------------------
*/

watch(mobileOpen, (open) => {
    if (typeof document !== "undefined") {
        document.body.style.overflow = open ? "hidden" : "";
    }
});

/*
|--------------------------------------------------------------------------
| Close Sidebar After Navigation
|--------------------------------------------------------------------------
*/

watch(
    () => page.url,
    () => {
        closeMobileMenu();
    },
);
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-[76px] shrink-0 items-center border-b border-slate-200/80 bg-white/90 px-4 backdrop-blur-xl sm:px-6 lg:px-8"
    >
        <!-- MOBILE MENU BUTTON -->
        <button
            type="button"
            aria-label="Buka sidebar"
            :aria-expanded="mobileOpen"
            aria-controls="lintasara-mobile-navigation"
            class="mr-3 flex size-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 lg:hidden"
            @click="openMobileMenu"
        >
            <Menu class="size-5" />
        </button>

        <!-- BREADCRUMB -->
        <div class="min-w-0 flex-1">
            <div
                v-if="breadcrumbs?.length"
                class="flex min-w-0 items-center gap-2"
            >
                <template
                    v-for="(item, index) in breadcrumbs"
                    :key="`${item.title}-${index}`"
                >
                    <ChevronRight
                        v-if="index > 0"
                        class="size-3.5 shrink-0 text-slate-300"
                    />

                    <Link
                        v-if="item.href && index !== breadcrumbs.length - 1"
                        :href="item.href"
                        class="truncate text-sm text-slate-400 transition hover:text-orange-500"
                    >
                        {{ item.title }}
                    </Link>

                    <span
                        v-else
                        class="truncate text-sm font-semibold text-slate-700"
                    >
                        {{ item.title }}
                    </span>
                </template>
            </div>

            <span v-else class="text-sm font-semibold text-slate-700">
                Lintasara
            </span>
        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                aria-label="Cari"
                class="hidden h-10 cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 text-sm text-slate-400 transition hover:border-slate-300 hover:bg-slate-50 md:flex"
            >
                <Search class="size-4" />

                <span class="hidden xl:inline"> Cari... </span>
            </button>

            <div class="mx-1 hidden h-7 w-px bg-slate-200 sm:block" />

            <AdminUserMenu />
        </div>
    </header>

    <!-- MOBILE SIDEBAR -->
    <Teleport v-if="clientReady" to="body">
        <div v-if="mobileOpen" class="fixed inset-0 z-[100] lg:hidden">
            <!-- OVERLAY -->
            <button
                type="button"
                aria-label="Tutup sidebar"
                class="absolute inset-0 cursor-pointer bg-slate-950/40 backdrop-blur-[2px]"
                @click="closeMobileMenu"
            />

            <!-- SIDEBAR -->
            <aside
                id="lintasara-mobile-navigation"
                role="dialog"
                aria-modal="true"
                aria-label="Navigasi Lintasara"
                class="absolute inset-y-0 left-0 flex w-[290px] max-w-[85vw] flex-col bg-white shadow-2xl"
            >
                <!-- LOGO -->
                <div
                    class="flex h-[76px] shrink-0 items-center justify-between border-b border-slate-100 px-5"
                >
                    <Link href="/admin" @click="closeMobileMenu">
                        <AdminLogo />
                    </Link>

                    <button
                        type="button"
                        aria-label="Tutup sidebar"
                        class="flex size-9 cursor-pointer items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="closeMobileMenu"
                    >
                        <X class="size-5" />
                    </button>
                </div>

                <!-- NAVIGATION -->
                <div
                    class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-5"
                >
                    <!-- DASHBOARD -->
                    <nav aria-label="Dashboard">
                        <Link
                            href="/admin"
                            class="group flex h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-all"
                            :class="
                                isActive('/admin')
                                    ? 'bg-orange-50 text-orange-600'
                                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                            "
                            @click="closeMobileMenu"
                        >
                            <Gauge
                                class="size-[19px] shrink-0"
                                :class="
                                    isActive('/admin')
                                        ? 'text-orange-500'
                                        : 'text-slate-400 group-hover:text-slate-700'
                                "
                            />

                            Dashboard

                            <span
                                v-if="isActive('/admin')"
                                class="ml-auto size-1.5 rounded-full bg-orange-500"
                            />
                        </Link>
                    </nav>

                    <!-- KONTEN -->
                    <div class="mt-7">
                        <p
                            class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                        >
                            Konten
                        </p>

                        <nav aria-label="Konten" class="space-y-1">
                            <Link
                                v-for="item in contentMenus"
                                :key="item.href"
                                :href="item.href"
                                class="group flex h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-all"
                                :class="
                                    isActive(item.href)
                                        ? 'bg-orange-50 text-orange-600'
                                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                                "
                                @click="closeMobileMenu"
                            >
                                <component
                                    :is="item.icon"
                                    class="size-[19px] shrink-0"
                                    :class="
                                        isActive(item.href)
                                            ? 'text-orange-500'
                                            : 'text-slate-400 group-hover:text-slate-700'
                                    "
                                />

                                {{ item.title }}

                                <span
                                    v-if="isActive(item.href)"
                                    class="ml-auto size-1.5 rounded-full bg-orange-500"
                                />
                            </Link>
                        </nav>
                    </div>

                    <!-- MANAJEMEN -->
                    <div v-if="managementMenus.length" class="mt-7">
                        <p
                            class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                        >
                            Manajemen
                        </p>

                        <nav aria-label="Manajemen" class="space-y-1">
                            <Link
                                v-for="item in managementMenus"
                                :key="item.href"
                                :href="item.href"
                                class="group flex h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-all"
                                :class="
                                    isActive(item.href)
                                        ? 'bg-orange-50 text-orange-600'
                                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                                "
                                @click="closeMobileMenu"
                            >
                                <component
                                    :is="item.icon"
                                    class="size-[19px] shrink-0"
                                    :class="
                                        isActive(item.href)
                                            ? 'text-orange-500'
                                            : 'text-slate-400 group-hover:text-slate-700'
                                    "
                                />

                                {{ item.title }}

                                <span
                                    v-if="isActive(item.href)"
                                    class="ml-auto size-1.5 rounded-full bg-orange-500"
                                />
                            </Link>
                        </nav>
                    </div>
                </div>

                <!-- USER PROFILE -->
                <div class="shrink-0 border-t border-slate-100 p-4">
                    <div
                        class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#071a36] text-xs font-bold text-white"
                        >
                            {{
                                user?.name
                                    ?.split(" ")
                                    .map((word: string) => word.charAt(0))
                                    .join("")
                                    .substring(0, 2)
                                    .toUpperCase() || "U"
                            }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold text-slate-800"
                            >
                                {{ user?.name ?? "Pengguna" }}
                            </p>

                            <p class="truncate text-xs text-slate-400">
                                {{ user?.email ?? "" }}
                            </p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </Teleport>
</template>
