<script setup lang="ts">
import { Head, useForm } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, reactive, ref, watch } from "vue";
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle2,
    Clock3,
    LockKeyhole,
    Mail,
    Newspaper,
    ShieldCheck,
    AlertCircle,
} from "lucide-vue-next";

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: "",
});

const SUCCESS_MESSAGE =
    "Jika alamat email terdaftar, tautan reset kata sandi telah dikirimkan. Silakan periksa email Anda.";

const COOLDOWN_SECONDS = 60;

const COOLDOWN_STORAGE_KEY = "lintasara_password_reset_cooldown";
const NOTIFICATION_STORAGE_KEY = "lintasara_password_reset_notification";

type NotificationType = "success" | "warning" | "error" | "";

interface StoredNotification {
    type: NotificationType;
    message: string;
}

const notification = reactive<StoredNotification>({
    type: props.status ? "success" : "",
    message: props.status ? SUCCESS_MESSAGE : "",
});

const remainingSeconds = ref(0);
const cooldownEndTime = ref(0);

let countdownTimer: ReturnType<typeof setInterval> | null = null;

const isCooldown = computed(() => remainingSeconds.value > 0);

const countdownLabel = computed(
    () => `Kirim ulang dalam ${remainingSeconds.value} detik`,
);

function saveNotification() {
    if (typeof window === "undefined") return;

    if (!notification.type) {
        sessionStorage.removeItem(NOTIFICATION_STORAGE_KEY);
        return;
    }

    sessionStorage.setItem(
        NOTIFICATION_STORAGE_KEY,
        JSON.stringify({
            type: notification.type,
            message: notification.message,
        }),
    );
}

function showNotification(type: NotificationType, message: string) {
    notification.type = type;
    notification.message = message;
    saveNotification();
}

function clearNotification() {
    showNotification("", "");
}

function stopCountdown() {
    if (countdownTimer !== null) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
}

function updateCountdown() {
    if (!cooldownEndTime.value) {
        remainingSeconds.value = 0;
        return;
    }

    const millisecondsLeft = cooldownEndTime.value - Date.now();

    remainingSeconds.value = Math.max(0, Math.ceil(millisecondsLeft / 1000));

    if (remainingSeconds.value === 0) {
        stopCountdown();
        cooldownEndTime.value = 0;

        if (typeof window !== "undefined") {
            sessionStorage.removeItem(COOLDOWN_STORAGE_KEY);
        }
    }
}

function runCountdown() {
    stopCountdown();
    updateCountdown();

    if (remainingSeconds.value > 0) {
        countdownTimer = setInterval(updateCountdown, 1000);
    }
}

function startCountdown(seconds: number) {
    const newEndTime = Date.now() + seconds * 1000;

    // Jangan memperpanjang cooldown yang masih berjalan.
    if (cooldownEndTime.value > Date.now() && remainingSeconds.value > 0) {
        runCountdown();
        return;
    }

    cooldownEndTime.value = newEndTime;

    if (typeof window !== "undefined") {
        sessionStorage.setItem(COOLDOWN_STORAGE_KEY, String(newEndTime));
    }

    runCountdown();
}

function restoreState() {
    if (typeof window === "undefined") return;

    const savedEndTime = Number(
        sessionStorage.getItem(COOLDOWN_STORAGE_KEY) || 0,
    );

    if (Number.isFinite(savedEndTime) && savedEndTime > Date.now()) {
        cooldownEndTime.value = savedEndTime;
        runCountdown();
    } else {
        sessionStorage.removeItem(COOLDOWN_STORAGE_KEY);
    }

    const savedNotification = sessionStorage.getItem(NOTIFICATION_STORAGE_KEY);

    if (savedNotification) {
        try {
            const parsed = JSON.parse(savedNotification) as StoredNotification;

            if (
                ["success", "warning", "error"].includes(parsed.type) &&
                typeof parsed.message === "string"
            ) {
                notification.type = parsed.type;
                notification.message = parsed.message;
            }
        } catch {
            sessionStorage.removeItem(NOTIFICATION_STORAGE_KEY);
        }
    }

    // Status terbaru dari Laravel lebih diutamakan.
    if (props.status) {
        showNotification("success", SUCCESS_MESSAGE);
    }
}

watch(
    () => props.status,
    (status) => {
        if (status) {
            showNotification("success", SUCCESS_MESSAGE);
        }
    },
);

watch(
    () => form.email,
    () => {
        form.clearErrors("email");

        // Pesan email salah boleh dibersihkan setelah email diperbaiki.
        // Pesan sukses dan cooldown tidak ikut hilang.
        if (notification.type === "error") {
            clearNotification();
        }
    },
);

function submit() {
    if (form.processing || isCooldown.value) {
        return;
    }

    form.clearErrors();
    clearNotification();

    form.post("/forgot-password", {
        preserveScroll: true,

        onSuccess: () => {
            showNotification("success", SUCCESS_MESSAGE);

            startCountdown(COOLDOWN_SECONDS);
        },

        onError: (errors) => {
            const error = String(errors.email || "");
            const normalizedError = error.toLowerCase();

            // 1. Email tidak ditemukan.
            const isEmailNotFound =
                normalizedError.includes("can't find a user") ||
                normalizedError.includes("cannot find a user") ||
                normalizedError.includes("not found") ||
                normalizedError.includes("tidak terdaftar");

            if (isEmailNotFound) {
                showNotification(
                    "error",
                    "Alamat email tidak terdaftar di Lintasara. Periksa kembali alamat email Anda.",
                );

                form.clearErrors("email");
                return;
            }

            // 2. Laravel membatasi pengiriman berulang.
            const isThrottleError =
                normalizedError.includes("please wait") ||
                normalizedError.includes("throttled") ||
                normalizedError.includes("too many requests") ||
                normalizedError.includes("terlalu cepat") ||
                normalizedError.includes("coba lagi");

            if (isThrottleError) {
                showNotification(
                    "warning",
                    "Permintaan sebelumnya masih dalam masa tunggu. Silakan coba kembali setelah jeda keamanan berakhir.",
                );

                form.clearErrors("email");

                // Hanya buat cooldown perkiraan jika belum ada.
                // Jika masih tersisa 35 detik, tidak kembali ke 60.
                if (!isCooldown.value) {
                    startCountdown(COOLDOWN_SECONDS);
                }

                return;
            }

            // 3. Kesalahan lain.
            showNotification(
                "error",
                error ||
                    "Permintaan tidak dapat diproses. Silakan periksa kembali alamat email Anda.",
            );

            form.clearErrors("email");
        },
    });
}

onMounted(() => {
    restoreState();
});

onUnmounted(() => {
    stopCountdown();
});
</script>

<template>
    <Head title="Lupa Kata Sandi" />

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
                    Akses aman,
                    <span class="text-orange-400"> tanpa hambatan. </span>
                </h1>

                <p class="mt-6 max-w-md text-sm leading-7 text-slate-300">
                    Pulihkan akses akun redaksi Anda dengan mudah. Kami membantu
                    menjaga keamanan setiap aktivitas di ruang redaksi
                    Lintasara.
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
                            Pemulihan Akun
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            Tautan pemulihan dikirim ke email terdaftar.
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

            <!-- Kembali ke Login -->
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
                        <LockKeyhole class="size-6 text-orange-500" />
                    </div>

                    <p
                        class="text-xs font-bold uppercase tracking-[0.18em] text-orange-500"
                    >
                        PEMULIHAN AKUN
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight text-[#071A36]"
                    >
                        Lupa kata sandi?
                    </h2>

                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Masukkan alamat email yang terdaftar. Kami akan
                        mengirimkan tautan untuk mengatur ulang kata sandi akun
                        Anda.
                    </p>

                    <!-- NOTIFIKASI SUKSES -->
                    <div
                        v-if="notification.type === 'success'"
                        role="status"
                        aria-live="polite"
                        class="mt-7 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4"
                    >
                        <CheckCircle2
                            class="mt-0.5 size-5 shrink-0 text-emerald-600"
                        />

                        <div>
                            <p class="text-sm font-semibold text-emerald-800">
                                Permintaan berhasil diproses
                            </p>

                            <p class="mt-1 text-sm leading-6 text-emerald-700">
                                {{ notification.message }}
                            </p>
                        </div>
                    </div>

                    <!-- NOTIFIKASI COOLDOWN -->
                    <div
                        v-if="notification.type === 'warning'"
                        role="alert"
                        class="mt-7 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-4"
                    >
                        <Clock3 class="mt-0.5 size-5 shrink-0 text-amber-600" />

                        <div>
                            <p class="text-sm font-semibold text-amber-800">
                                Mohon tunggu sebelum mencoba lagi
                            </p>

                            <p class="mt-1 text-sm leading-6 text-amber-700">
                                {{ notification.message }}
                            </p>

                            <p
                                v-if="isCooldown"
                                class="mt-2 text-xs font-semibold text-amber-800"
                            >
                                Sisa waktu: {{ remainingSeconds }} detik
                            </p>
                        </div>
                    </div>

                    <!-- NOTIFIKASI ERROR -->
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
                                Permintaan tidak berhasil
                            </p>

                            <p class="mt-1 text-sm leading-6 text-red-700">
                                {{ notification.message }}
                            </p>
                        </div>
                    </div>

                    <!-- FORM -->
                    <form class="mt-9 space-y-5" @submit.prevent="submit">
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

                        <!-- TOMBOL SUBMIT -->
                        <button
                            type="submit"
                            :disabled="form.processing || isCooldown"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#071A36] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#102b51] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span>
                                {{
                                    form.processing
                                        ? "Sedang mengirim..."
                                        : isCooldown
                                          ? countdownLabel
                                          : "Kirim Tautan Reset Password"
                                }}
                            </span>

                            <Clock3 v-if="isCooldown" class="size-4" />

                            <ArrowRight
                                v-else-if="!form.processing"
                                class="size-4"
                            />
                        </button>
                    </form>

                    <!-- INFORMASI KEAMANAN -->
                    <div
                        class="mt-8 rounded-xl border border-slate-100 bg-slate-50 px-4 py-4"
                    >
                        <div class="flex items-start gap-3">
                            <ShieldCheck
                                class="mt-0.5 size-5 shrink-0 text-slate-400"
                            />

                            <p class="text-xs leading-5 text-slate-500">
                                Demi keamanan, gunakan alamat email yang
                                terhubung dengan akun redaksi Lintasara Anda.
                            </p>
                        </div>
                    </div>

                    <!-- KEMBALI KE LOGIN -->
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
