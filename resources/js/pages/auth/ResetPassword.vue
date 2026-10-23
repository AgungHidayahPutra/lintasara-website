<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed, reactive, ref, watch } from "vue";
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Eye,
    EyeOff,
    KeyRound,
    LockKeyhole,
    Mail,
    Newspaper,
    ShieldCheck,
    AlertCircle,
} from "lucide-vue-next";

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const showPassword = ref(false);
const showConfirmation = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

type NoticeType = "success" | "error" | "";

const notification = reactive({
    type: "" as NoticeType,
    title: "",
    message: "",
});

const isPasswordTooShort = computed(
    () => form.password.length > 0 && form.password.length < 8,
);

const isConfirmationMismatch = computed(
    () =>
        form.password_confirmation.length > 0 &&
        form.password !== form.password_confirmation,
);

function showError(title: string, message: string) {
    notification.type = "error";
    notification.title = title;
    notification.message = message;
}

function clearError() {
    notification.type = "";
    notification.title = "";
    notification.message = "";
}

watch(
    () => form.password,
    () => {
        form.clearErrors("password");

        // Notifikasi tetap tampil selama pengguna mengetik.
        // Baru berubah ketika tombol Simpan ditekan kembali.
    },
);

watch(
    () => form.password_confirmation,
    () => {
        form.clearErrors("password_confirmation");
    },
);

function submit() {
    if (form.processing) return;

    form.clearErrors();
    clearError();

    if (form.password.length < 8) {
        showError(
            "Kata sandi terlalu pendek",
            "Kata sandi harus terdiri dari minimal 8 karakter.",
        );
        return;
    }

    if (form.password !== form.password_confirmation) {
        showError(
            "Konfirmasi tidak sesuai",
            "Konfirmasi kata sandi tidak sesuai dengan kata sandi baru.",
        );
        return;
    }

    form.post("/reset-password", {
        preserveScroll: true,

        onError: (errors) => {
            if (errors.token) {
                showError(
                    "Tautan tidak valid",
                    "Tautan reset kata sandi tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.",
                );
                return;
            }

            if (errors.password) {
                showError(
                    "Kata sandi belum memenuhi persyaratan",
                    String(errors.password),
                );
                return;
            }

            if (errors.password_confirmation) {
                showError(
                    "Konfirmasi tidak sesuai",
                    String(errors.password_confirmation),
                );
                return;
            }

            if (errors.email) {
                showError("Permintaan tidak berhasil", String(errors.email));
                return;
            }

            showError(
                "Permintaan tidak berhasil",
                "Kata sandi belum dapat diperbarui. Silakan coba kembali.",
            );
        },

        onSuccess: () => {},
    });
}
</script>

<template>
    <Head title="Reset Kata Sandi" />

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
                <a href="/" class="inline-flex items-center gap-3">
                    <div
                        class="flex size-11 items-center justify-center rounded-xl bg-orange-500"
                    >
                        <Newspaper class="size-6 text-white" />
                    </div>

                    <span class="text-2xl font-bold tracking-tight text-white">
                        Lintasara<span class="text-orange-500">.</span>
                    </span>
                </a>
            </div>

            <!-- Konten -->
            <div class="relative z-10 px-12 pb-12 xl:px-16">
                <div
                    class="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-medium text-slate-300"
                >
                    <span class="size-2 rounded-full bg-orange-500"></span>
                    KEAMANAN AKUN
                </div>

                <h1
                    class="max-w-lg text-4xl font-bold leading-tight tracking-tight text-white xl:text-5xl"
                >
                    Keamanan dimulai dari
                    <span class="text-orange-400">kata sandi.</span>
                </h1>

                <p class="mt-6 max-w-md text-sm leading-7 text-slate-300">
                    Lindungi akses ruang redaksi Anda dengan kata sandi yang
                    kuat dan unik untuk menjaga keamanan akun Lintasara.
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
                            Perlindungan Akun
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Pastikan kata sandi baru tidak digunakan pada akun
                            lain.
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

            <!-- Navigasi -->
            <div class="hidden justify-end lg:flex">
                <a
                    href="/login"
                    class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-orange-500"
                >
                    <ArrowLeft class="size-4" />
                    Kembali ke Login
                </a>
            </div>

            <!-- Form -->
            <div class="flex flex-1 items-center justify-center py-12">
                <div class="w-full max-w-[410px]">
                    <div
                        class="mb-8 flex size-12 items-center justify-center rounded-2xl bg-orange-50"
                    >
                        <KeyRound class="size-6 text-orange-500" />
                    </div>

                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-orange-500"
                    >
                        PEMULIHAN AKUN
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-[#071A36]"
                    >
                        Buat kata sandi baru.
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Masukkan kata sandi baru untuk memulihkan akses akun
                        redaksi Lintasara Anda.
                    </p>

                    <!-- NOTIFIKASI RESET PASSWORD -->
                    <div
                        v-if="notification.type === 'error'"
                        role="alert"
                        class="mt-7 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-4"
                    >
                        <AlertCircle
                            class="mt-0.5 size-5 shrink-0 text-red-600"
                        />

                        <div>
                            <p class="text-sm font-semibold text-red-800">
                                {{ notification.title }}
                            </p>

                            <p class="mt-1 text-sm leading-6 text-red-700">
                                {{ notification.message }}
                            </p>

                            <a
                                v-if="form.errors.token"
                                href="/forgot-password"
                                class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-red-800 underline underline-offset-4 hover:text-red-950"
                            >
                                Minta tautan reset baru
                                <ArrowRight class="size-4" />
                            </a>
                        </div>
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
                                    readonly
                                    class="h-12 w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm text-slate-500 outline-none"
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
                            <label
                                for="password"
                                class="mb-2 block text-sm font-semibold text-[#071A36]"
                            >
                                Kata Sandi Baru
                            </label>

                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute left-4 top-1/2 size-[18px] -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    required
                                    autofocus
                                    placeholder="Masukkan kata sandi baru"
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

                        <!-- Konfirmasi Password -->
                        <div>
                            <label
                                for="password_confirmation"
                                class="mb-2 block text-sm font-semibold text-[#071A36]"
                            >
                                Konfirmasi Kata Sandi
                            </label>

                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute left-4 top-1/2 size-[18px] -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="
                                        showConfirmation ? 'text' : 'password'
                                    "
                                    autocomplete="new-password"
                                    required
                                    placeholder="Ulangi kata sandi baru"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-12 pr-12 text-sm text-[#071A36] outline-none transition placeholder:text-slate-400 focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                                />

                                <button
                                    type="button"
                                    :aria-label="
                                        showConfirmation
                                            ? 'Sembunyikan konfirmasi'
                                            : 'Tampilkan konfirmasi'
                                    "
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-600"
                                    @click="
                                        showConfirmation = !showConfirmation
                                    "
                                >
                                    <EyeOff
                                        v-if="showConfirmation"
                                        class="size-[18px]"
                                    />
                                    <Eye v-else class="size-[18px]" />
                                </button>
                            </div>

                            <p
                                v-if="form.errors.password_confirmation"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <!-- Error token -->
                        <p
                            v-if="form.errors.token"
                            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                        >
                            {{ form.errors.token }}
                        </p>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#071A36] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#102b51] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{
                                form.processing
                                    ? "Sedang menyimpan..."
                                    : "Simpan Kata Sandi Baru"
                            }}
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
                                Gunakan kata sandi yang kuat, unik, dan tidak
                                mudah ditebak.
                            </p>
                        </div>
                    </div>

                    <a
                        href="/login"
                        class="mt-7 flex items-center justify-center gap-2 text-sm font-semibold text-orange-500 transition hover:text-orange-600"
                    >
                        <ArrowLeft class="size-4" />
                        Kembali ke halaman Login
                    </a>
                </div>
            </div>

            <!-- Footer mobile -->
            <p class="text-center text-xs text-slate-400 lg:hidden">
                © {{ new Date().getFullYear() }} Lintasara.
            </p>
        </div>
    </div>
</template>
