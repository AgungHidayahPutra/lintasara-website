<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
    ArrowLeft,
    ChevronLeft,
    ChevronRight,
    FileText,
    Pencil,
    Plus,
    Search,
    ShieldCheck,
    Trash2,
    UserCheck,
    UserRound,
    UserX,
    Users,
    X,
} from "lucide-vue-next";
import { computed, onBeforeUnmount, ref, watch } from "vue";
import { toast } from "vue-sonner";

defineOptions({ layout: AdminLayout });

interface User {
    id: number;
    name: string;
    email: string;
    role: "admin" | "penulis";
    is_active: boolean;
    articles_count: number;
    created_at: string | null;
}
interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}
interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
}
interface Stats {
    total: number;
    admins: number;
    writers: number;
    active: number;
    inactive: number;
}
interface Filters {
    search: string;
    role: string;
    status: string;
}

const props = defineProps<{
    users: PaginatedUsers;
    stats: Stats;
    filters: Filters;
    currentUserId: number;
}>();
const page = usePage();
const search = ref(props.filters.search ?? "");
const role = ref(props.filters.role ?? "");
const status = ref(props.filters.status ?? "");
const deletingUser = ref<User | null>(null);
const deleting = ref(false);
let filterTimer: ReturnType<typeof setTimeout> | undefined;
let resetting = false;
const hasFilters = computed(
    () => !!(search.value || role.value || status.value),
);
const statsCards = computed(() => [
    {
        label: "Total Pengguna",
        value: props.stats.total,
        detail: "Seluruh akun terdaftar",
        icon: Users,
        iconClass: "bg-orange-50 text-orange-500",
    },
    {
        label: "Administrator",
        value: props.stats.admins,
        detail: "Pengelola sistem",
        icon: ShieldCheck,
        iconClass: "bg-violet-50 text-violet-600",
    },
    {
        label: "Penulis",
        value: props.stats.writers,
        detail: "Kontributor artikel",
        icon: UserRound,
        iconClass: "bg-blue-50 text-blue-600",
    },
    {
        label: "Aktif",
        value: props.stats.active,
        detail: "Akun yang diaktifkan",
        icon: UserCheck,
        iconClass: "bg-emerald-50 text-emerald-600",
    },
    {
        label: "Nonaktif",
        value: props.stats.inactive,
        detail: "Akun yang dinonaktifkan",
        icon: UserX,
        iconClass: "bg-rose-50 text-rose-600",
    },
]);

function initials(name: string): string {
    return name
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((word) => word[0] ?? "")
        .join("")
        .toUpperCase();
}
function formatDate(value: string | null): string {
    if (!value) return "—";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "—";
    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "short",
        year: "numeric",
    }).format(date);
}
function applyFilters(): void {
    if (resetting) return;
    router.get(
        "/admin/users",
        {
            ...(search.value.trim() ? { search: search.value.trim() } : {}),
            ...(role.value ? { role: role.value } : {}),
            ...(status.value ? { status: status.value } : {}),
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
watch(search, () => {
    if (resetting) return;
    clearTimeout(filterTimer);
    filterTimer = setTimeout(applyFilters, 350);
});
watch([role, status], () => {
    if (resetting) return;
    clearTimeout(filterTimer);
    applyFilters();
});
onBeforeUnmount(() => clearTimeout(filterTimer));
function resetFilters(): void {
    clearTimeout(filterTimer);
    resetting = true;
    search.value = "";
    role.value = "";
    status.value = "";
    resetting = false;
    router.get(
        "/admin/users",
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
function navigateTo(url: string | null): void {
    if (!url) return;
    router.get(url, {}, { preserveState: true, preserveScroll: true });
}
function openDeleteModal(user: User): void {
    if (user.id === props.currentUserId) {
        toast.error("Akun sendiri tidak dapat dihapus.");
        return;
    }
    deletingUser.value = user;
}
function closeDeleteModal(): void {
    if (!deleting.value) deletingUser.value = null;
}
function deleteUser(): void {
    if (
        !deletingUser.value ||
        deleting.value ||
        deletingUser.value.articles_count > 0
    )
        return;
    deleting.value = true;
    router.delete(`/admin/users/${deletingUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            const flash = page.props.flash as
                | { success?: string; error?: string }
                | undefined;
            if (flash?.error) toast.error(flash.error);
            else {
                toast.success(flash?.success ?? "Pengguna berhasil dihapus.");
                deletingUser.value = null;
            }
        },
        onError: () => toast.error("Gagal menghapus pengguna."),
        onFinish: () => {
            deleting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Manajemen Pengguna" />
    <div class="min-h-full bg-[#f8f9fb] p-4 md:p-6 lg:p-8 dark:bg-background">
        <div class="mx-auto max-w-[1250px]">
            <!-- Header seperti halaman Wilayah -->
            <div
                class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <Link
                        href="/admin"
                        class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" /> Kembali ke Dashboard
                    </Link>
                    <h1
                        class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        Manajemen Pengguna
                    </h1>
                    <p
                        class="mt-2 text-sm leading-6 text-slate-500 dark:text-muted-foreground"
                    >
                        Kelola akun administrator dan penulis Lintasara.
                    </p>
                </div>
                <Link
                    href="/admin/users/create"
                    class="inline-flex h-11 items-center justify-center gap-2 self-start rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600"
                >
                    <Plus class="size-4" /> Tambah Pengguna
                </Link>
            </div>

            <!-- Statistik dengan ikon berwarna -->
            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div
                    v-for="item in statsCards"
                    :key="item.label"
                    class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                >
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-slate-500">
                            {{ item.label }}
                        </p>
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl"
                            :class="item.iconClass"
                        >
                            <component :is="item.icon" class="size-5" />
                        </div>
                    </div>
                    <p
                        class="mt-3 text-3xl font-bold tracking-tight text-slate-950 dark:text-white"
                    >
                        {{ item.value }}
                    </p>
                    <p class="mt-1 text-xs text-slate-400">{{ item.detail }}</p>
                </div>
            </div>

            <!-- Daftar pengguna -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200/70 bg-white shadow-sm dark:border-border dark:bg-card"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-100 p-5 dark:border-border"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="font-semibold text-slate-900 dark:text-white"
                            >
                                Daftar Pengguna
                            </h2>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ users.total }} pengguna ditemukan
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span
                                class="inline-flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700"
                            >
                                <UserCheck class="size-3.5" />
                                {{ stats.active }} Aktif
                            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600"
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
                                placeholder="Cari nama atau email..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10 dark:border-border dark:bg-background"
                            />
                        </div>
                        <select
                            v-model="role"
                            aria-label="Filter role pengguna"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-600 outline-none focus:border-orange-400 dark:border-border dark:bg-background dark:text-slate-300"
                        >
                            <option value="">Semua Role</option>
                            <option value="admin">Administrator</option>
                            <option value="penulis">Penulis</option>
                        </select>
                        <select
                            v-model="status"
                            aria-label="Filter status pengguna"
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
                            <X class="size-4" /> Reset
                        </button>
                    </div>
                </div>

                <!-- Tabel desktop -->
                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full min-w-[850px] text-left">
                        <thead
                            class="border-b border-slate-100 bg-slate-50/70 dark:border-border dark:bg-muted/20"
                        >
                            <tr
                                class="text-xs font-semibold uppercase tracking-wider text-slate-400"
                            >
                                <th class="px-5 py-4">Pengguna</th>
                                <th class="px-5 py-4">Role</th>
                                <th class="px-5 py-4 text-center">Status</th>
                                <th class="px-5 py-4 text-center">Artikel</th>
                                <th class="px-5 py-4">Bergabung</th>
                                <th class="px-5 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-border"
                        >
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="transition hover:bg-slate-50/70 dark:hover:bg-muted/20"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-sm font-bold text-orange-600"
                                        >
                                            {{ initials(user.name) }}
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <p
                                                    class="max-w-[230px] truncate text-sm font-semibold text-slate-900 dark:text-white"
                                                >
                                                    {{ user.name }}
                                                </p>
                                                <span
                                                    v-if="
                                                        user.id ===
                                                        currentUserId
                                                    "
                                                    class="rounded-md bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-600"
                                                    >Anda</span
                                                >
                                            </div>
                                            <p
                                                class="mt-1 max-w-[260px] truncate text-xs text-slate-400"
                                            >
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-lg px-2.5 py-1.5 text-xs font-semibold"
                                        :class="
                                            user.role === 'admin'
                                                ? 'bg-violet-50 text-violet-700'
                                                : 'bg-blue-50 text-blue-700'
                                        "
                                        >{{
                                            user.role === "admin"
                                                ? "Administrator"
                                                : "Penulis"
                                        }}</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-lg px-2.5 py-1.5 text-xs font-semibold"
                                        :class="
                                            user.is_active
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-slate-100 text-slate-500'
                                        "
                                        >{{
                                            user.is_active
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        ><FileText
                                            class="size-4 text-slate-400"
                                        />
                                        {{ user.articles_count }}</span
                                    >
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-5 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                            :aria-label="`Edit ${user.name}`"
                                            title="Edit Pengguna"
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-orange-200 hover:bg-orange-50 hover:text-orange-600 dark:border-border"
                                            ><Pencil class="size-4"
                                        /></Link>
                                        <button
                                            type="button"
                                            :aria-label="`Hapus ${user.name}`"
                                            title="Hapus Pengguna"
                                            :disabled="
                                                user.id === currentUserId
                                            "
                                            class="inline-flex size-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-35 dark:border-border"
                                            @click="openDeleteModal(user)"
                                        >
                                            <Trash2 class="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kartu mobile -->
                <div class="divide-y divide-slate-100 md:hidden">
                    <div v-for="user in users.data" :key="user.id" class="p-4">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-sm font-bold text-orange-600"
                            >
                                {{ initials(user.name) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="truncate text-sm font-semibold text-slate-900"
                                >
                                    {{ user.name }}
                                    <span
                                        v-if="user.id === currentUserId"
                                        class="text-xs font-medium text-orange-600"
                                        >(Anda)</span
                                    >
                                </p>
                                <p class="truncate text-xs text-slate-400">
                                    {{ user.email }}
                                </p>
                            </div>
                            <Link
                                :href="`/admin/users/${user.id}/edit`"
                                :aria-label="`Edit ${user.name}`"
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500"
                                ><Pencil class="size-4"
                            /></Link>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span
                                class="rounded-lg px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    user.role === 'admin'
                                        ? 'bg-violet-50 text-violet-700'
                                        : 'bg-blue-50 text-blue-700'
                                "
                                >{{
                                    user.role === "admin"
                                        ? "Administrator"
                                        : "Penulis"
                                }}</span
                            >
                            <span
                                class="rounded-lg px-2.5 py-1 text-xs font-semibold"
                                :class="
                                    user.is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500'
                                "
                                >{{
                                    user.is_active ? "Aktif" : "Nonaktif"
                                }}</span
                            >
                            <span class="text-xs text-slate-400"
                                >{{ user.articles_count }} artikel</span
                            >
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs text-slate-400">{{
                                formatDate(user.created_at)
                            }}</span>
                            <button
                                type="button"
                                :disabled="user.id === currentUserId"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 disabled:opacity-30"
                                @click="openDeleteModal(user)"
                            >
                                <Trash2 class="size-3.5" /> Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-if="users.data.length === 0"
                    class="px-6 py-16 text-center"
                >
                    <div
                        class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-500"
                    >
                        <Users class="size-7" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-slate-900">
                        Tidak ada pengguna
                    </h3>
                    <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">
                        {{
                            hasFilters
                                ? "Tidak ada pengguna yang sesuai dengan filter."
                                : "Belum ada pengguna terdaftar."
                        }}
                    </p>
                    <Link
                        v-if="!hasFilters"
                        href="/admin/users/create"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-600"
                        ><Plus class="size-4" /> Tambah Pengguna</Link
                    >
                </div>

                <div
                    v-if="users.total > 0"
                    class="flex flex-col gap-4 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-border"
                >
                    <p class="text-xs text-slate-500">
                        Menampilkan
                        <strong
                            >{{ users.from ?? 0 }}–{{ users.to ?? 0 }}</strong
                        >
                        dari <strong>{{ users.total }}</strong> pengguna
                    </p>
                    <div
                        v-if="users.last_page > 1"
                        class="flex flex-wrap items-center gap-1.5"
                    >
                        <button
                            v-for="(link, index) in users.links"
                            :key="index"
                            type="button"
                            :disabled="!link.url || link.active"
                            class="inline-flex min-h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-xs font-semibold transition disabled:cursor-default"
                            :class="
                                link.active
                                    ? 'border-orange-500 bg-orange-500 text-white'
                                    : link.url
                                      ? 'border-slate-200 bg-white text-slate-600 hover:border-orange-300 hover:text-orange-500'
                                      : 'border-slate-100 bg-slate-50 text-slate-300'
                            "
                            @click="navigateTo(link.url)"
                        >
                            <ChevronLeft v-if="index === 0" class="size-4" />
                            <ChevronRight
                                v-else-if="index === users.links.length - 1"
                                class="size-4"
                            />
                            <span v-else>{{ link.label }}</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <!-- Modal hapus -->
        <div
            v-if="deletingUser"
            class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm"
            @click.self="closeDeleteModal"
        >
            <div
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="delete-user-title"
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-border dark:bg-card"
            >
                <div
                    class="flex size-12 items-center justify-center rounded-2xl bg-red-50 text-red-500"
                >
                    <Trash2 class="size-6" />
                </div>
                <h2
                    id="delete-user-title"
                    class="mt-4 text-xl font-bold text-slate-950 dark:text-white"
                >
                    Hapus Pengguna?
                </h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Anda akan menghapus akun
                    <strong class="text-slate-900">{{
                        deletingUser.name
                    }}</strong
                    >.
                </p>
                <div
                    v-if="deletingUser.articles_count > 0"
                    class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800"
                >
                    Pengguna ini memiliki
                    <strong>{{ deletingUser.articles_count }} artikel</strong>
                    sehingga tidak dapat dihapus. Nonaktifkan akun sebagai
                    alternatif.
                </div>
                <p v-else class="mt-3 text-xs text-slate-400">
                    Tindakan ini tidak dapat dibatalkan. Administrator aktif
                    terakhir juga dilindungi oleh sistem.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        :disabled="deleting"
                        class="h-10 rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                        @click="closeDeleteModal"
                    >
                        Batal
                    </button>
                    <button
                        v-if="deletingUser.articles_count === 0"
                        type="button"
                        :disabled="deleting"
                        class="h-10 rounded-xl bg-red-500 px-4 text-sm font-semibold text-white hover:bg-red-600 disabled:opacity-60"
                        @click="deleteUser"
                    >
                        {{ deleting ? "Menghapus..." : "Hapus Pengguna" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
