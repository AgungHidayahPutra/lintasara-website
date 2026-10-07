<script setup lang="ts">
import { Link, router, usePage } from "@inertiajs/vue3";
import { ChevronDown, LogOut, Settings, UserRound } from "lucide-vue-next";
import { computed, ref } from "vue";

const page = usePage();
const open = ref(false);

const user = computed(() => page.props.auth.user);

const initials = computed(() => {
    return user.value.name
        .split(" ")
        .map((word) => word.charAt(0))
        .join("")
        .substring(0, 2)
        .toUpperCase();
});

function logout() {
    open.value = false;

    router.post("/logout");
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="flex items-center gap-3 rounded-xl p-1.5 transition hover:bg-slate-100"
            @click="open = !open"
        >
            <div
                class="flex size-9 items-center justify-center rounded-xl bg-[#071a36] text-xs font-bold text-white"
            >
                {{ initials }}
            </div>

            <div class="hidden min-w-0 text-left sm:block">
                <p
                    class="max-w-32 truncate text-sm font-semibold text-slate-800"
                >
                    {{ user.name }}
                </p>

                <p class="text-xs capitalize text-slate-400">
                    {{ user.role }}
                </p>
            </div>

            <ChevronDown
                class="hidden size-4 text-slate-400 transition sm:block"
                :class="{ 'rotate-180': open }"
            />
        </button>

        <button
            v-if="open"
            type="button"
            class="fixed inset-0 z-40 cursor-default"
            aria-label="Tutup menu"
            @click="open = false"
        />

        <div
            v-if="open"
            class="absolute right-0 top-[calc(100%+8px)] z-50 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-950/10"
        >
            <div class="border-b border-slate-100 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#071a36] text-xs font-bold text-white"
                    >
                        {{ initials }}
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
            </div>

            <div class="p-2">
                <Link
                    href="/settings/profile"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-950"
                    @click="open = false"
                >
                    <UserRound class="size-4 text-slate-400" />
                    Profil
                </Link>

                <Link
                    href="/settings/appearance"
                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-950"
                    @click="open = false"
                >
                    <Settings class="size-4 text-slate-400" />
                    Pengaturan
                </Link>
            </div>

            <div class="border-t border-slate-100 p-2">
                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50"
                    @click="logout"
                >
                    <LogOut class="size-4" />
                    Keluar
                </button>
            </div>
        </div>
    </div>
</template>
