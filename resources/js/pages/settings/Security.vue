<script setup lang="ts">
import { Form, Head } from "@inertiajs/vue3";
import { KeyRound, LockKeyhole, ShieldCheck } from "lucide-vue-next";

import SecurityController from "@/actions/App/Http/Controllers/Settings/SecurityController";
import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import ManageTwoFactor from "@/components/ManageTwoFactor.vue";
import type { Props as ManageTwoFactorProps } from "@/components/ManageTwoFactor.vue";
import { edit } from "@/routes/security";

type Props = {
    passwordRules: string;
} & ManageTwoFactorProps;

defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Keamanan Akun",
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Keamanan Akun | Lintasara" />

    <div class="mx-auto w-full max-w-5xl space-y-8 pb-12">
        <!-- Header -->
        <div>
            <div
                class="mb-4 inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-600"
            >
                <ShieldCheck class="size-4" />
                PENGATURAN KEAMANAN
            </div>

            <h1
                class="text-2xl font-bold tracking-tight text-[#071A36] sm:text-3xl"
            >
                Keamanan Akun
            </h1>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Kelola kata sandi dan perlindungan tambahan untuk menjaga
                keamanan akun redaksi Lintasara.
            </p>
        </div>

        <!-- Password Card -->
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="flex items-start gap-4 border-b border-slate-100 px-6 py-6 sm:px-8"
            >
                <div
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-orange-50"
                >
                    <KeyRound class="size-5 text-orange-500" />
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#071A36]">
                        Ubah Kata Sandi
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Gunakan kata sandi yang kuat dan berbeda dari kata sandi
                        yang digunakan sebelumnya.
                    </p>
                </div>
            </div>

            <div class="px-6 py-7 sm:px-8">
                <Form
                    :action="SecurityController.update.url()"
                    method="put"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    class="max-w-xl space-y-6"
                    v-slot="{ errors, processing, wasSuccessful }"
                >
                    <div>
                        <label
                            for="current_password"
                            class="mb-2 block text-sm font-semibold text-[#071A36]"
                        >
                            Kata Sandi Saat Ini
                        </label>

                        <PasswordInput
                            id="current_password"
                            name="current_password"
                            class="block w-full"
                            autocomplete="current-password"
                            placeholder="Masukkan kata sandi saat ini"
                        />

                        <InputError
                            :message="errors.current_password"
                            class="mt-2"
                        />
                    </div>

                    <div>
                        <label
                            for="password"
                            class="mb-2 block text-sm font-semibold text-[#071A36]"
                        >
                            Kata Sandi Baru
                        </label>

                        <PasswordInput
                            id="password"
                            name="password"
                            class="block w-full"
                            autocomplete="new-password"
                            placeholder="Masukkan kata sandi baru"
                            :passwordrules="passwordRules"
                        />

                        <InputError :message="errors.password" class="mt-2" />
                    </div>

                    <div>
                        <label
                            for="password_confirmation"
                            class="mb-2 block text-sm font-semibold text-[#071A36]"
                        >
                            Konfirmasi Kata Sandi Baru
                        </label>

                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            class="block w-full"
                            autocomplete="new-password"
                            placeholder="Ulangi kata sandi baru"
                            :passwordrules="passwordRules"
                        />

                        <InputError
                            :message="errors.password_confirmation"
                            class="mt-2"
                        />
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6"
                    >
                        <button
                            type="submit"
                            :disabled="processing"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#071A36] px-6 text-sm font-semibold text-white transition hover:bg-[#102b51] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <LockKeyhole class="size-4" />

                            {{
                                processing
                                    ? "Menyimpan..."
                                    : "Simpan Kata Sandi"
                            }}
                        </button>

                        <span
                            v-if="wasSuccessful"
                            class="text-sm font-medium text-emerald-600"
                        >
                            Kata sandi berhasil diperbarui.
                        </span>
                    </div>
                </Form>
            </div>
        </section>

        <!-- Two Factor Card -->
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div
                class="flex items-start gap-4 border-b border-slate-100 px-6 py-6 sm:px-8"
            >
                <div
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-orange-50"
                >
                    <ShieldCheck class="size-5 text-orange-500" />
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#071A36]">
                        Autentikasi Dua Faktor
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Tambahkan lapisan keamanan dengan kode verifikasi dari
                        aplikasi autentikator.
                    </p>
                </div>
            </div>

            <div class="px-6 py-7 sm:px-8">
                <ManageTwoFactor
                    :canManageTwoFactor="canManageTwoFactor"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </section>
    </div>
</template>
