<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import {
    ArrowRight,
    Eye,
    EyeOff,
    LockKeyhole,
    Mail,
    Newspaper,
    ShieldCheck,
} from "lucide-vue-next";

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

function submit() {
    if (form.processing) return;

    form.post("/login", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset("password");
        },
    });
}
</script>

<template>
    <Head title="Masuk " />

    <div class="flex min-h-screen bg-white">
        <!-- PANEL KIRI -->
        <div
            class="relative hidden w-[48%] overflow-hidden bg-[#071A36] lg:flex lg:flex-col lg:justify-between"
        >
            <!-- Dekorasi -->
            <div
                class="pointer-events-none absolute -right-40 -top-40 size-[500px] rounded-full border border-white/10"
            ></div>
            <div
                class="pointer-events-none absolute -right-24 -top-24 size-[350px] rounded-full border border-white/10"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-48 -left-32 size-[500px] rounded-full border border-white/10"
            ></div>

            <!-- Brand -->
            <div class="relative z-10 p-12 xl:p-16">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-orange-500"
                    >
                        <Newspaper class="size-6 text-white" />
                    </div>

                    <span class="text-2xl font-bold tracking-tight text-white">
                        Lintasara<span class="text-orange-500">.</span>
                    </span>
                </div>
            </div>

            <!-- Konten -->
            <div class="relative z-10 px-12 pb-12 xl:px-16">
                <div
                    class="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-medium text-slate-300"
                >
                    <span class="size-2 rounded-full bg-orange-500"></span>
                    RUANG REDAKSI DIGITAL
                </div>

                <h1
                    class="max-w-lg text-4xl font-bold leading-tight tracking-tight text-white xl:text-5xl"
                >
                    Setiap berita memiliki
                    <span class="text-orange-400">cerita.</span>
                </h1>

                <p class="mt-6 max-w-md text-sm leading-7 text-slate-300">
                    Kelola artikel, susun informasi, dan hadirkan berita
                    berkualitas melalui satu ruang redaksi yang terintegrasi.
                </p>

                <div
                    class="mt-10 flex items-center gap-3 border-t border-white/10 pt-8"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-xl bg-white/10"
                    >
                        <ShieldCheck class="size-5 text-orange-400" />
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-white">
                            Akses Redaksi
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Khusus Administrator dan Penulis Lintasara
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="relative z-10 flex items-center justify-between border-t border-white/10 px-12 py-6 text-xs text-slate-400 xl:px-16"
            >
                <span>© {{ new Date().getFullYear() }} Lintasara</span>
                <span>Portal Berita Digital</span>
            </div>
        </div>

        <!-- PANEL KANAN -->
        <div
            class="flex min-h-screen flex-1 flex-col bg-white px-6 py-8 sm:px-10 lg:px-16"
        >
            <!-- Header mobile -->
            <div class="flex items-center gap-2 lg:hidden">
                <div
                    class="flex size-9 items-center justify-center rounded-lg bg-orange-500"
                >
                    <Newspaper class="size-5 text-white" />
                </div>

                <span class="text-xl font-bold tracking-tight text-[#071A36]">
                    Lintasara<span class="text-orange-500">.</span>
                </span>
            </div>

            <!-- Link beranda -->
            <div class="hidden justify-end lg:flex">
                <a
                    href="/"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-500"
                >
                    Kembali ke Beranda
                    <ArrowRight class="size-4" />
                </a>
            </div>

            <!-- Form -->
            <div class="flex flex-1 items-center justify-center py-12">
                <div class="w-full max-w-[410px]">
                    <div
                        class="mb-8 flex size-12 items-center justify-center rounded-2xl bg-orange-50"
                    >
                        <LockKeyhole class="size-6 text-orange-500" />
                    </div>

                    <div>
                        <p
                            class="text-xs font-bold uppercase tracking-[0.18em] text-orange-500"
                        >
                            Lintasara Workspace
                        </p>

                        <h2
                            class="mt-3 text-3xl font-bold tracking-tight text-[#071A36]"
                        >
                            Selamat datang.
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Masuk menggunakan akun redaksi Anda untuk
                            melanjutkan aktivitas.
                        </p>
                    </div>

                    <!-- Status -->
                    <div
                        v-if="status"
                        class="mt-7 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                    >
                        {{ status }}
                    </div>

                    <form class="mt-9 space-y-5" @submit.prevent="submit">
                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold text-[#071A36]"
                            >
                                Alamat Email
                            </label>

                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute left-4 top-1/2 size-[18px] -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    autofocus
                                    placeholder="nama@email.com"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 text-sm text-[#071A36] outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />
                            </div>

                            <p
                                v-if="form.errors.email"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-3"
                            >
                                <label
                                    for="password"
                                    class="text-sm font-semibold text-[#071A36]"
                                >
                                    Kata Sandi
                                </label>

                                <a
                                    v-if="canResetPassword"
                                    href="/forgot-password"
                                    class="text-xs font-semibold text-orange-500 transition hover:text-orange-600"
                                >
                                    Lupa kata sandi?
                                </a>
                            </div>

                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute left-4 top-1/2 size-[18px] -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    required
                                    placeholder="Masukkan kata sandi"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-12 text-sm text-[#071A36] outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <button
                                    type="button"
                                    :aria-label="
                                        showPassword
                                            ? 'Sembunyikan kata sandi'
                                            : 'Tampilkan kata sandi'
                                    "
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff
                                        v-if="showPassword"
                                        class="size-[18px]"
                                    />
                                    <Eye v-else class="size-[18px]" />
                                </button>
                            </div>

                            <p
                                v-if="form.errors.password"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Remember -->
                        <label class="flex cursor-pointer items-center gap-3">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="size-4 rounded border-slate-300 text-orange-500 focus:ring-orange-500"
                            />

                            <span class="text-sm text-slate-600">
                                Ingat saya
                            </span>
                        </label>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#071A36] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#102b51] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span>
                                {{
                                    form.processing
                                        ? "Sedang masuk..."
                                        : "Masuk ke Dashboard"
                                }}
                            </span>

                            <ArrowRight
                                v-if="!form.processing"
                                class="size-4"
                            />
                        </button>
                    </form>

                    <!-- Info -->
                    <div
                        class="mt-8 rounded-xl border border-slate-100 bg-slate-50 px-4 py-4"
                    >
                        <div class="flex items-start gap-3">
                            <ShieldCheck
                                class="mt-0.5 size-5 shrink-0 text-slate-400"
                            />

                            <p class="text-xs leading-5 text-slate-500">
                                Halaman ini hanya dapat diakses oleh
                                Administrator dan Penulis yang memiliki akun
                                resmi Lintasara.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer mobile -->
            <p class="text-center text-xs text-slate-400 lg:hidden">
                © {{ new Date().getFullYear() }} Lintasara.
            </p>
        </div>
    </div>
</template>
