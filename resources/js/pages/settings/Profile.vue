<script setup lang="ts">
import { Form, Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import {
    ArrowRight,
    BadgeCheck,
    CheckCircle2,
    Mail,
    Save,
    ShieldAlert,
    UserRound,
} from "lucide-vue-next";

import ProfileController from "@/actions/App/Http/Controllers/Settings/ProfileController";
import InputError from "@/components/InputError.vue";
import { edit } from "@/routes/profile";
import { send } from "@/routes/verification";

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Profil Akun",
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const initials = computed(() => {
    const name = user.value.name?.trim() || "Lintasara";

    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
});
</script>

<template>
    <Head title="Profil Akun" />

    <div class="w-full space-y-6 pb-10">
        <!-- Identitas akun -->
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p
                            class="text-[11px] font-bold uppercase tracking-[0.18em] text-orange-600"
                        >
                            Identitas Redaksi
                        </p>

                        <h2
                            class="mt-1 text-xl font-bold tracking-tight text-[#071A36]"
                        >
                            Profil Akun
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Kelola informasi identitas yang digunakan untuk
                            mengakses ruang redaksi Lintasara.
                        </p>
                    </div>

                    <div
                        class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600"
                    >
                        <BadgeCheck class="h-4 w-4 text-orange-500" />
                        Akun Redaksi
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 sm:px-7">
                <!-- Ringkasan pengguna -->
                <div
                    class="mb-7 flex flex-wrap items-center gap-4 rounded-xl border border-slate-100 bg-[#f8f9fb] p-4"
                >
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#071A36] text-lg font-bold text-white"
                    >
                        {{ initials }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-[#071A36]">
                            {{ user.name }}
                        </p>

                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            {{ user.email }}
                        </p>
                    </div>

                    <div
                        v-if="user.email_verified_at"
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700"
                    >
                        <CheckCircle2 class="h-3.5 w-3.5" />
                        Email terverifikasi
                    </div>

                    <div
                        v-else
                        class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700"
                    >
                        <ShieldAlert class="h-3.5 w-3.5" />
                        Belum terverifikasi
                    </div>
                </div>

                <!-- Form profil -->
                <Form
                    v-bind="ProfileController.update.form()"
                    class="space-y-5"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-5">
                        <div class="space-y-2">
                            <label
                                for="name"
                                class="flex items-center gap-2 text-sm font-semibold text-[#071A36]"
                            >
                                <UserRound class="h-4 w-4 text-orange-500" />
                                Nama Lengkap
                            </label>

                            <input
                                id="name"
                                name="name"
                                type="text"
                                :default-value="user.name"
                                autocomplete="name"
                                placeholder="Masukkan nama lengkap"
                                required
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                            />

                            <InputError :message="errors.name" />
                        </div>

                        <div class="space-y-2">
                            <label
                                for="email"
                                class="flex items-center gap-2 text-sm font-semibold text-[#071A36]"
                            >
                                <Mail class="h-4 w-4 text-orange-500" />
                                Alamat Email
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                :default-value="user.email"
                                autocomplete="username"
                                placeholder="Masukkan alamat email"
                                required
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-500/10"
                            />

                            <InputError :message="errors.email" />

                            <p class="text-xs leading-relaxed text-slate-500">
                                Pastikan alamat email aktif agar dapat menerima
                                tautan verifikasi dan pemulihan akun.
                            </p>
                        </div>
                    </div>

                    <!-- Verifikasi email -->
                    <div
                        v-if="
                            page.props.mustVerifyEmail &&
                            !user.email_verified_at
                        "
                        class="rounded-xl border border-amber-200 bg-amber-50 p-4"
                    >
                        <div class="flex items-start gap-3">
                            <ShieldAlert
                                class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"
                            />

                            <div class="space-y-2">
                                <p class="text-sm font-bold text-amber-900">
                                    Email belum diverifikasi
                                </p>

                                <p
                                    class="text-xs leading-relaxed text-amber-800"
                                >
                                    Verifikasi alamat email untuk memastikan
                                    keamanan dan kelancaran akses akun redaksi.
                                </p>

                                <Link
                                    :href="send()"
                                    method="post"
                                    as="button"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-orange-700 hover:text-orange-800"
                                >
                                    Kirim ulang email verifikasi
                                    <ArrowRight class="h-3.5 w-3.5" />
                                </Link>

                                <p
                                    v-if="
                                        page.props.status ===
                                        'verification-link-sent'
                                    "
                                    class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700"
                                >
                                    <CheckCircle2 class="h-4 w-4" />
                                    Tautan verifikasi baru telah dikirim.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Aksi -->
                    <div
                        class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-5"
                    >
                        <div
                            v-if="recentlySuccessful"
                            class="flex items-center gap-2 text-xs font-semibold text-emerald-700"
                            role="status"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            Perubahan profil berhasil disimpan.
                        </div>

                        <p
                            v-else
                            class="text-xs leading-relaxed text-slate-400"
                        >
                            Perubahan nama dan email akan digunakan pada akun
                            Lintasara Anda.
                        </p>

                        <button
                            type="submit"
                            :disabled="processing"
                            data-test="update-profile-button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-[#071A36] px-5 text-sm font-semibold text-white transition hover:bg-[#102c56] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-orange-500/20 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Save class="h-4 w-4" />
                            {{
                                processing ? "Menyimpan..." : "Simpan Perubahan"
                            }}
                        </button>
                    </div>
                </Form>
            </div>
        </section>

        <!-- Informasi keamanan -->
        <section
            class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >
            <div class="flex items-start gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-600"
                >
                    <BadgeCheck class="h-5 w-5" />
                </div>

                <div>
                    <h3 class="text-sm font-bold text-[#071A36]">
                        Keamanan Akun
                    </h3>

                    <p
                        class="mt-1 max-w-lg text-xs leading-relaxed text-slate-500"
                    >
                        Perbarui kata sandi dan kelola autentikasi dua faktor
                        untuk melindungi akun redaksi Anda.
                    </p>
                </div>
            </div>

            <Link
                href="/settings/security"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-[#071A36] transition hover:border-orange-200 hover:bg-orange-50"
            >
                Buka Keamanan
                <ArrowRight class="h-4 w-4" />
            </Link>
        </section>
    </div>
</template>
