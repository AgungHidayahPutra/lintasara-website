<script setup lang="ts">
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Eye,
    EyeOff,
    FileText,
    Info,
    LockKeyhole,
    Mail,
    Save,
    ShieldCheck,
    UserRound,
    Users,
} from "lucide-vue-next";
import { computed, ref } from "vue";
import { toast } from "vue-sonner";

import AdminLayout from "@/layouts/admin/AdminLayout.vue";

defineOptions({
    layout: AdminLayout,
});

interface User {
    id: number;
    name: string;
    email: string;
    role: "admin" | "penulis";
    is_active: boolean;
    email_verified_at: string | null;
    created_at: string | null;
}

const props = defineProps<{
    user: User;
    articlesCount: number;
    isCurrentUser: boolean;
}>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    is_active: props.user.is_active,
    password: "",
    password_confirmation: "",
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const isAdmin = computed(() => form.role === "admin");

const isEmailChanged = computed(() => form.email.trim() !== props.user.email);

const canChangeAccess = computed(() => !props.isCurrentUser);

function formatDate(date: string | null): string {
    if (!date) return "-";

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) return "-";

    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(parsed);
}

function initials(name: string): string {
    return (
        name
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((word) => word.charAt(0))
            .join("")
            .toUpperCase() || "U"
    );
}

function submit() {
    if (form.processing) return;

    form.transform((data) => ({
        ...data,
        name: data.name.trim(),
        email: data.email.trim(),
        password: data.password || null,
        password_confirmation: data.password
            ? data.password_confirmation
            : null,
    }));

    form.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            // Controller mengarahkan ke daftar pengguna.
            // Notifikasi sukses dapat ditampilkan dari flash global.
        },

        onError: (errors) => {
            if (Object.keys(errors).length > 0) {
                toast.error("Periksa kembali data yang Anda masukkan.");
            }
        },

        onFinish: () => {
            form.password = "";
            form.password_confirmation = "";
        },
    });
}

function cancel() {
    router.visit("/admin/users");
}
</script>

<template>
    <Head :title="`Edit Pengguna - ${user.name} - Lintasara`" />

    <div class="min-h-full bg-[#f8f9fb] p-4 md:p-6 lg:p-8">
        <div class="mx-auto max-w-[1250px] space-y-6">
            <!-- HEADER -->
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <Link
                        href="/admin/users"
                        class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke Pengguna
                    </Link>

                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-950 sm:text-[28px]"
                    >
                        Edit Pengguna
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Perbarui informasi dan pengaturan akun
                        {{ user.name }}.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        @click="cancel"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        form="edit-user-form"
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-lg shadow-orange-500/15 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Save class="size-4" />
                        {{
                            form.processing
                                ? "Menyimpan..."
                                : "Simpan Perubahan"
                        }}
                    </button>
                </div>
            </div>

            <form
                id="edit-user-form"
                class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_345px]"
                @submit.prevent="submit"
            >
                <!-- LEFT COLUMN -->
                <div class="min-w-0 space-y-6">
                    <!-- INFORMASI PENGGUNA -->
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"
                        >
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500"
                            >
                                <UserRound class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="text-sm font-bold text-slate-900 sm:text-base"
                                >
                                    Informasi Pengguna
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Perbarui identitas dan kontak pengguna.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-5 p-5 sm:p-6">
                            <!-- NAME -->

                            <!-- NAME -->
                            <div>
                                <label
                                    for="name"
                                    class="mb-2 block text-sm font-semibold text-slate-800"
                                >
                                    Nama lengkap
                                    <span class="text-orange-500">*</span>
                                </label>

                                <div
                                    class="flex h-12 items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 transition-colors focus-within:border-orange-400 focus-within:ring-2 focus-within:ring-orange-100"
                                    :class="
                                        form.errors.name
                                            ? 'border-rose-400'
                                            : ''
                                    "
                                >
                                    <UserRound
                                        class="size-[18px] shrink-0 text-slate-400"
                                    />

                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        autocomplete="name"
                                        maxlength="255"
                                        required
                                        placeholder="Masukkan nama lengkap"
                                        class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-800 outline-none focus:outline-none focus:ring-0"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.name"
                                    class="mt-1.5 text-xs text-rose-600"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- EMAIL -->

                            <!-- EMAIL -->
                            <div>
                                <label
                                    for="email"
                                    class="mb-2 block text-sm font-semibold text-slate-800"
                                >
                                    Alamat email
                                    <span class="text-orange-500">*</span>
                                </label>

                                <div
                                    class="flex h-12 items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 transition-colors focus-within:border-orange-400 focus-within:ring-2 focus-within:ring-orange-100"
                                    :class="
                                        form.errors.email
                                            ? 'border-rose-400'
                                            : ''
                                    "
                                >
                                    <Mail
                                        class="size-[18px] shrink-0 text-slate-400"
                                    />

                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        autocomplete="email"
                                        maxlength="255"
                                        required
                                        placeholder="nama@lintasara.com"
                                        class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-800 outline-none focus:outline-none focus:ring-0"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.email"
                                    class="mt-1.5 text-xs text-rose-600"
                                >
                                    {{ form.errors.email }}
                                </p>

                                <div
                                    v-if="isEmailChanged"
                                    class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs leading-relaxed text-amber-700"
                                >
                                    <Info class="mt-0.5 size-4 shrink-0" />

                                    <span>
                                        Mengubah email akan menghapus status
                                        verifikasi sebelumnya. Pengguna perlu
                                        melakukan verifikasi kembali.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ROLE -->

                    <!-- AKSES & STATUS AKUN -->
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        <!-- HEADER -->
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"
                        >
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                            >
                                <ShieldCheck class="size-5" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Hak Akses Pengguna
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    Tentukan peran dan izin pengguna.
                                </p>
                            </div>
                        </div>

                        <!-- CONTENT -->
                        <div class="space-y-5 p-5 sm:p-6">
                            <!-- ROLE -->
                            <fieldset class="space-y-2">
                                <legend
                                    class="text-sm font-semibold text-slate-700"
                                >
                                    Role pengguna
                                    <span class="text-rose-500">*</span>
                                </legend>

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <!-- PENULIS -->
                                    <label
                                        class="flex items-start gap-3 rounded-xl border p-4 transition"
                                        :class="[
                                            form.role === 'penulis'
                                                ? 'border-orange-400 bg-orange-50/60'
                                                : 'border-slate-200 hover:bg-slate-50',
                                            isCurrentUser
                                                ? 'cursor-not-allowed'
                                                : 'cursor-pointer',
                                        ]"
                                    >
                                        <input
                                            v-model="form.role"
                                            type="radio"
                                            value="penulis"
                                            :disabled="isCurrentUser"
                                            class="mt-1 accent-orange-600 disabled:cursor-not-allowed"
                                        />

                                        <span>
                                            <span
                                                class="block text-sm font-semibold text-slate-900"
                                            >
                                                Penulis
                                            </span>

                                            <span
                                                class="mt-1 block text-xs leading-5 text-slate-500"
                                            >
                                                Membuat dan mengelola artikel
                                                sesuai izin yang diberikan.
                                            </span>
                                        </span>
                                    </label>

                                    <!-- ADMINISTRATOR -->
                                    <label
                                        class="flex items-start gap-3 rounded-xl border p-4 transition"
                                        :class="[
                                            form.role === 'admin'
                                                ? 'border-orange-400 bg-orange-50/60'
                                                : 'border-slate-200 hover:bg-slate-50',
                                            isCurrentUser
                                                ? 'cursor-not-allowed'
                                                : 'cursor-pointer',
                                        ]"
                                    >
                                        <input
                                            v-model="form.role"
                                            type="radio"
                                            value="admin"
                                            :disabled="isCurrentUser"
                                            class="mt-1 accent-orange-600 disabled:cursor-not-allowed"
                                        />

                                        <span>
                                            <span
                                                class="block text-sm font-semibold text-slate-900"
                                            >
                                                Administrator
                                            </span>

                                            <span
                                                class="mt-1 block text-xs leading-5 text-slate-500"
                                            >
                                                Mengelola artikel, kategori,
                                                tag, wilayah, dan pengguna.
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <p
                                    v-if="form.errors.role"
                                    class="text-xs text-rose-600"
                                >
                                    {{ form.errors.role }}
                                </p>
                            </fieldset>

                            <p
                                v-if="form.errors.is_active"
                                class="text-xs text-rose-600"
                            >
                                {{ form.errors.is_active }}
                            </p>

                            <!-- SELF ACCOUNT NOTICE -->
                            <div
                                v-if="isCurrentUser"
                                class="flex items-start gap-2 rounded-xl bg-blue-50 p-3 text-xs leading-relaxed text-blue-700"
                            >
                                <Info class="mt-0.5 size-4 shrink-0" />

                                <span>
                                    Anda sedang mengedit akun sendiri. Role
                                    administrator dan status aktif tidak dapat
                                    diubah.
                                </span>
                            </div>
                        </div>
                    </section>

                    <!-- PASSWORD -->
                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"
                        >
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                            >
                                <LockKeyhole class="size-5" />
                            </div>

                            <div>
                                <h2
                                    class="text-sm font-bold text-slate-900 sm:text-base"
                                >
                                    Keamanan Akun
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Kosongkan password jika tidak ingin
                                    menggantinya.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                            <!-- PASSWORD -->
                            <div>
                                <label
                                    for="password"
                                    class="mb-2 block text-sm font-semibold text-slate-800"
                                >
                                    Password baru
                                </label>

                                <div class="relative">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        autocomplete="new-password"
                                        placeholder="Minimal 8 karakter"
                                        class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 pr-12 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                    />

                                    <button
                                        type="button"
                                        :aria-label="
                                            showPassword
                                                ? 'Sembunyikan password'
                                                : 'Tampilkan password'
                                        "
                                        class="absolute right-3 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-700"
                                        @click="showPassword = !showPassword"
                                    >
                                        <EyeOff
                                            v-if="showPassword"
                                            class="size-4"
                                        />
                                        <Eye v-else class="size-4" />
                                    </button>
                                </div>

                                <p
                                    v-if="form.errors.password"
                                    class="mt-1.5 text-xs text-rose-600"
                                >
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- CONFIRM PASSWORD -->
                            <div>
                                <label
                                    for="password_confirmation"
                                    class="mb-2 block text-sm font-semibold text-slate-800"
                                >
                                    Konfirmasi password
                                </label>

                                <div class="relative">
                                    <input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="
                                            showPasswordConfirmation
                                                ? 'text'
                                                : 'password'
                                        "
                                        autocomplete="new-password"
                                        placeholder="Ulangi password baru"
                                        class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 pr-12 text-sm text-slate-800 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                    />

                                    <button
                                        type="button"
                                        :aria-label="
                                            showPasswordConfirmation
                                                ? 'Sembunyikan konfirmasi'
                                                : 'Tampilkan konfirmasi'
                                        "
                                        class="absolute right-3 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-700"
                                        @click="
                                            showPasswordConfirmation =
                                                !showPasswordConfirmation
                                        "
                                    >
                                        <EyeOff
                                            v-if="showPasswordConfirmation"
                                            class="size-4"
                                        />
                                        <Eye v-else class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="min-w-0 space-y-6">
                    <!-- STATUS -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <h2 class="text-sm font-bold text-slate-900">
                            Status Pengguna
                        </h2>

                        <p class="mt-2 text-xs leading-relaxed text-slate-400">
                            Tentukan apakah akun pengguna diaktifkan atau
                            dinonaktifkan.
                        </p>

                        <label
                            class="mt-5 flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4"
                            :class="
                                isCurrentUser
                                    ? 'cursor-not-allowed opacity-75'
                                    : ''
                            "
                        >
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl"
                                    :class="
                                        form.is_active
                                            ? 'bg-emerald-50 text-emerald-500'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    <CheckCircle2 class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        {{
                                            form.is_active
                                                ? "Aktif"
                                                : "Nonaktif"
                                        }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-xs leading-relaxed text-slate-400"
                                    >
                                        {{
                                            form.is_active
                                                ? "Akun pengguna diaktifkan."
                                                : "Akun pengguna dinonaktifkan."
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="relative h-6 w-11 shrink-0">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="sr-only"
                                    aria-label="Status aktif pengguna"
                                    :disabled="isCurrentUser"
                                />

                                <div
                                    class="pointer-events-none absolute inset-0 rounded-full transition-colors duration-200"
                                    :class="
                                        form.is_active
                                            ? 'bg-orange-500'
                                            : 'bg-slate-200'
                                    "
                                />

                                <div
                                    class="pointer-events-none absolute top-1 size-4 rounded-full bg-white shadow-sm transition-all duration-200"
                                    :class="
                                        form.is_active ? 'left-6' : 'left-1'
                                    "
                                />
                            </div>
                        </label>

                        <p
                            v-if="form.errors.is_active"
                            class="mt-2 text-xs text-rose-600"
                        >
                            {{ form.errors.is_active }}
                        </p>

                        <p
                            v-if="isCurrentUser"
                            class="mt-3 text-xs leading-relaxed text-slate-400"
                        >
                            Akun administrator yang sedang digunakan tidak dapat
                            dinonaktifkan.
                        </p>
                    </section>

                    <!-- INFORMATION -->
                    <section
                        class="rounded-2xl border border-sky-100 bg-sky-50/70 p-5"
                    >
                        <div class="flex items-start gap-3">
                            <Info class="mt-0.5 size-5 shrink-0 text-sky-500" />

                            <div>
                                <h3 class="text-sm font-bold text-sky-800">
                                    Tentang pengguna
                                </h3>

                                <p
                                    class="mt-2 text-xs leading-relaxed text-sky-700"
                                >
                                    Pengguna dengan role Administrator dapat
                                    mengelola seluruh modul administrasi.
                                    Penulis hanya memiliki akses sesuai
                                    kebijakan sistem.
                                </p>

                                <p
                                    class="mt-3 text-xs leading-relaxed text-sky-700"
                                >
                                    Menonaktifkan akun belum otomatis memblokir
                                    login hingga pemeriksaan status diterapkan
                                    pada autentikasi.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- ACCOUNT DETAILS -->
                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <p
                            class="text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Informasi Akun
                        </p>

                        <div
                            class="mt-4 flex items-center gap-3 border-b border-slate-100 pb-4"
                        >
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#071a36] text-xs font-bold text-white"
                            >
                                {{ initials(user.name) }}
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-semibold text-slate-900"
                                >
                                    {{ user.name }}
                                </p>

                                <p class="truncate text-xs text-slate-400">
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-4">
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="inline-flex items-center gap-2 text-xs text-slate-500"
                                >
                                    <FileText class="size-4" />
                                    Total Artikel
                                </span>

                                <span
                                    class="text-sm font-semibold text-slate-800"
                                >
                                    {{ articlesCount }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="inline-flex items-center gap-2 text-xs text-slate-500"
                                >
                                    <CalendarDays class="size-4" />
                                    Bergabung
                                </span>

                                <span
                                    class="text-xs font-semibold text-slate-800"
                                >
                                    {{ formatDate(user.created_at) }}
                                </span>
                            </div>

                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span class="text-xs text-slate-500">
                                    Verifikasi Email
                                </span>

                                <span
                                    class="rounded-lg px-2 py-1 text-[11px] font-semibold"
                                    :class="
                                        user.email_verified_at
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-amber-50 text-amber-700'
                                    "
                                >
                                    {{
                                        user.email_verified_at
                                            ? "Terverifikasi"
                                            : "Belum Verifikasi"
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="isCurrentUser"
                                class="rounded-lg bg-orange-50 px-3 py-2 text-xs font-medium text-orange-700"
                            >
                                Ini adalah akun Anda.
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </div>
</template>
