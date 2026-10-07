<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import {
    FileText,
    FolderOpen,
    Gauge,
    MapPin,
    Tags,
    Users,
} from "lucide-vue-next";
import { computed } from "vue";
import AdminLogo from "./AdminLogo.vue";

const page = usePage();

const user = computed(() => page.props.auth.user);

const isAdmin = computed(() => user.value.role === "admin");

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

function isActive(href: string) {
    const currentUrl = page.url.split("?")[0];

    if (href === "/admin") {
        return currentUrl === "/admin";
    }

    return currentUrl.startsWith(href);
}

function initials(name: string) {
    return name
        .split(" ")
        .map((word) => word.charAt(0))
        .join("")
        .substring(0, 2)
        .toUpperCase();
}
</script>

<template>
    <aside
        class="sticky top-0 hidden h-screen w-[240px] shrink-0 flex-col self-start border-r border-slate-200/80 bg-white lg:flex"
    >
        <!-- Logo -->
        <div
            class="flex h-[76px] shrink-0 items-center border-b border-slate-100 px-5"
        >
            <Link href="/admin">
                <AdminLogo />
            </Link>
        </div>

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto px-4 py-5">
            <nav>
                <Link
                    href="/admin"
                    class="group flex h-11 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-all"
                    :class="
                        isActive('/admin')
                            ? 'bg-orange-50 text-orange-600'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'
                    "
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
                </Link>
            </nav>

            <!-- Konten -->
            <div class="mt-7">
                <p
                    class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                >
                    Konten
                </p>

                <nav class="space-y-1">
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

            <!-- Manajemen -->
            <div v-if="managementMenus.length" class="mt-7">
                <p
                    class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400"
                >
                    Manajemen
                </p>

                <nav class="space-y-1">
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
                    </Link>
                </nav>
            </div>
        </div>

        <!-- User -->
        <div class="border-t border-slate-100 p-4">
            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 p-3">
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#071a36] text-xs font-bold text-white"
                >
                    {{ initials(user.name) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-800">
                        {{ user.name }}
                    </p>

                    <p class="truncate text-xs text-slate-400">
                        {{ user.email }}
                    </p>
                </div>
            </div>
        </div>
    </aside>
</template>
