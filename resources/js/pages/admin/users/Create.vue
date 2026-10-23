<script setup lang="ts">
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Eye,
    EyeOff,
    LockKeyhole,
    Mail,
    Save,
    ShieldCheck,
    UserRound,
    Users,
} from "lucide-vue-next";
import { ref } from "vue";

defineOptions({ layout: AdminLayout });

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "penulis" as "admin" | "penulis",
    is_active: true,
});

const showPassword = ref(false);
const showConfirmation = ref(false);

function submit() {
    form.post("/admin/users", {
        preserveScroll: true,
        onSuccess: () => form.reset("password", "password_confirmation"),
    });
}
</script>

<template>
    <Head title="Tambah Pengguna - Lintasara" />

    <div class="min-h-full bg-[#f8f9fb] p-4 md:p-6 lg:p-8">
        <div class="mx-auto max-w-[1000px] space-y-6">
            <div>
                <Link
                    href="/admin/users"
                    class="inline-flex items-center gap-2 text-sm text-slate-500 transition hover:text-orange-600"
                >
                    <ArrowLeft class="size-4" /> Kembali ke Pengguna
                </Link>
                <h1
                    class="mt-3 text-2xl font-bold tracking-tight text-slate-950 sm:text-[28px]"
                >
                    Tambah Pengguna
                </h1>
                <p class="mt-1.5 text-sm text-slate-500">
                    Buat akun administrator atau penulis baru untuk Lintasara.
                </p>
            </div>

            <form class="space-y-5" @submit.prevent="submit">
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-orange-50 text-orange-600"
                        >
                            <UserRound class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">
                                Informasi Pengguna
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Identitas dasar untuk akun baru.
                            </p>
                        </div>
                    </div>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div class="space-y-2">
                            <label
                                for="name"
                                class="text-sm font-semibold text-slate-700"
                                >Nama lengkap
                                <span class="text-rose-500">*</span></label
                            >
                            <div class="relative">
                                <Users
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                /><input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    autocomplete="name"
                                    required
                                    maxlength="255"
                                    placeholder="Contoh: Rahmat Maulana"
                                    :aria-invalid="!!form.errors.name"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                />
                            </div>
                            <p
                                v-if="form.errors.name"
                                class="text-xs text-rose-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label
                                for="email"
                                class="text-sm font-semibold text-slate-700"
                                >Alamat email
                                <span class="text-rose-500">*</span></label
                            >
                            <div class="relative">
                                <Mail
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                /><input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    maxlength="255"
                                    placeholder="nama@lintasara.com"
                                    :aria-invalid="!!form.errors.email"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                />
                            </div>
                            <p
                                v-if="form.errors.email"
                                class="text-xs text-rose-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                        >
                            <ShieldCheck class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">
                                Akses & Status Akun
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Tentukan hak akses pengguna.
                            </p>
                        </div>
                    </div>
                    <div class="space-y-5 p-5 sm:p-6">
                        <fieldset class="space-y-2">
                            <legend
                                class="text-sm font-semibold text-slate-700"
                            >
                                Role pengguna
                                <span class="text-rose-500">*</span>
                            </legend>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                                    :class="
                                        form.role === 'penulis'
                                            ? 'border-orange-400 bg-orange-50/60'
                                            : 'border-slate-200 hover:bg-slate-50'
                                    "
                                >
                                    <input
                                        v-model="form.role"
                                        type="radio"
                                        value="penulis"
                                        class="mt-1 accent-orange-600"
                                    />
                                    <span
                                        ><span
                                            class="block text-sm font-semibold text-slate-900"
                                            >Penulis</span
                                        ><span
                                            class="mt-1 block text-xs leading-5 text-slate-500"
                                            >Membuat dan mengelola artikel
                                            sesuai izin yang diberikan.</span
                                        ></span
                                    >
                                </label>
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                                    :class="
                                        form.role === 'admin'
                                            ? 'border-orange-400 bg-orange-50/60'
                                            : 'border-slate-200 hover:bg-slate-50'
                                    "
                                >
                                    <input
                                        v-model="form.role"
                                        type="radio"
                                        value="admin"
                                        class="mt-1 accent-orange-600"
                                    />
                                    <span
                                        ><span
                                            class="block text-sm font-semibold text-slate-900"
                                            >Administrator</span
                                        ><span
                                            class="mt-1 block text-xs leading-5 text-slate-500"
                                            >Mengelola artikel, kategori, tag,
                                            wilayah, dan pengguna.</span
                                        ></span
                                    >
                                </label>
                            </div>
                            <p
                                v-if="form.errors.role"
                                class="text-xs text-rose-600"
                            >
                                {{ form.errors.role }}
                            </p>
                        </fieldset>

                        <!-- STATUS AKUN -->
                        <div
                            class="rounded-xl border border-slate-200 bg-white p-4"
                        >
                            <label
                                class="flex cursor-pointer items-center justify-between gap-4"
                            >
                                <div class="flex min-w-0 items-center gap-3">
                                    <!-- ICON STATUS -->
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-xl transition-colors"
                                        :class="
                                            form.is_active
                                                ? 'bg-emerald-50 text-emerald-500'
                                                : 'bg-slate-100 text-slate-400'
                                        "
                                    >
                                        <ShieldCheck class="size-4" />
                                    </div>

                                    <!-- TEXT STATUS -->
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
                                            class="mt-0.5 text-xs leading-5 text-slate-500"
                                        >
                                            {{
                                                form.is_active
                                                    ? "Akun pengguna akan diaktifkan."
                                                    : "Akun pengguna akan dinonaktifkan."
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
                                        aria-label="Aktifkan akun"
                                    />

                                    <!-- TRACK -->
                                    <div
                                        class="pointer-events-none absolute inset-0 rounded-full transition-colors duration-200"
                                        :class="
                                            form.is_active
                                                ? 'bg-orange-500'
                                                : 'bg-slate-200'
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
                        </div>

                        <p
                            v-if="form.errors.is_active"
                            class="text-xs text-rose-600"
                        >
                            {{ form.errors.is_active }}
                        </p>
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-3 border-b border-slate-100 px-5 py-5 sm:px-6"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <LockKeyhole class="size-5" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">
                                Keamanan Akun
                            </h2>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Buat password minimal 8 karakter.
                            </p>
                        </div>
                    </div>
                    <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-6">
                        <div class="space-y-2">
                            <label
                                for="password"
                                class="text-sm font-semibold text-slate-700"
                                >Password
                                <span class="text-rose-500">*</span></label
                            >
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    required
                                    minlength="8"
                                    placeholder="Minimal 8 karakter"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 pr-11 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                /><button
                                    type="button"
                                    :aria-label="
                                        showPassword
                                            ? 'Sembunyikan password'
                                            : 'Tampilkan password'
                                    "
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff
                                        v-if="showPassword"
                                        class="size-4"
                                    /><Eye v-else class="size-4" />
                                </button>
                            </div>
                            <p
                                v-if="form.errors.password"
                                class="text-xs text-rose-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label
                                for="password_confirmation"
                                class="text-sm font-semibold text-slate-700"
                                >Konfirmasi password
                                <span class="text-rose-500">*</span></label
                            >
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="
                                        showConfirmation ? 'text' : 'password'
                                    "
                                    autocomplete="new-password"
                                    required
                                    minlength="8"
                                    placeholder="Ulangi password"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 pr-11 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                /><button
                                    type="button"
                                    :aria-label="
                                        showConfirmation
                                            ? 'Sembunyikan konfirmasi'
                                            : 'Tampilkan konfirmasi'
                                    "
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
                                    @click="
                                        showConfirmation = !showConfirmation
                                    "
                                >
                                    <EyeOff
                                        v-if="showConfirmation"
                                        class="size-4"
                                    /><Eye v-else class="size-4" />
                                </button>
                            </div>
                            <p
                                v-if="form.errors.password_confirmation"
                                class="text-xs text-rose-600"
                            >
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>
                    </div>
                </section>

                <div
                    class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/admin/users"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >Batal</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-orange-500 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Save class="size-4" />{{
                            form.processing ? "Menyimpan..." : "Simpan Pengguna"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
