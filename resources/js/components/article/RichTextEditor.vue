<script setup lang="ts">
import { computed, onBeforeUnmount, watch } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import Underline from "@tiptap/extension-underline";
import Link from "@tiptap/extension-link";
import {
    Bold,
    Italic,
    Underline as UnderlineIcon,
    Heading2,
    Heading3,
    List,
    ListOrdered,
    Quote,
    Link2,
    Unlink,
    Undo2,
    Redo2,
    Pilcrow,
} from "lucide-vue-next";

const props = withDefaults(
    defineProps<{
        modelValue: string;
        disabled?: boolean;
    }>(),
    {
        modelValue: "",
        disabled: false,
    },
);

const emit = defineEmits<{
    "update:modelValue": [value: string];
}>();

const editor = useEditor({
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3],
            },
        }),
        Underline,
        Link.configure({
            openOnClick: false,
            autolink: true,
            linkOnPaste: true,
            HTMLAttributes: {
                rel: "noopener noreferrer nofollow",
                target: "_blank",
            },
        }),
    ],
    content: props.modelValue,
    editable: !props.disabled,
    editorProps: {
        attributes: {
            class:
                "min-h-[320px] px-5 py-5 outline-none " +
                "text-[15px] leading-8 text-slate-700 " +
                "[&_p]:my-3 " +
                "[&_h2]:mt-7 [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-bold " +
                "[&_h3]:mt-6 [&_h3]:mb-3 [&_h3]:text-lg [&_h3]:font-bold " +
                "[&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-6 " +
                "[&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-6 " +
                "[&_blockquote]:my-5 [&_blockquote]:border-l-4 " +
                "[&_blockquote]:border-orange-500 [&_blockquote]:pl-4 " +
                "[&_blockquote]:italic [&_blockquote]:text-slate-500 " +
                "[&_a]:text-orange-600 [&_a]:underline",
        },
    },
    onUpdate: ({ editor }) => {
        emit("update:modelValue", editor.getHTML());
    },
});

watch(
    () => props.modelValue,
    (value) => {
        if (!editor.value) return;

        const next = value || "";
        if (editor.value.getHTML() === next) return;

        editor.value.commands.setContent(next, {
            emitUpdate: false,
        });
    },
);

watch(
    () => props.disabled,
    (disabled) => {
        editor.value?.setEditable(!disabled);
    },
);

const toolbar = computed(() => [
    {
        label: "Paragraf",
        icon: Pilcrow,
        active: editor.value?.isActive("paragraph"),
        action: () => editor.value?.chain().focus().setParagraph().run(),
    },
    {
        label: "Heading 2",
        icon: Heading2,
        active: editor.value?.isActive("heading", { level: 2 }),
        action: () =>
            editor.value?.chain().focus().toggleHeading({ level: 2 }).run(),
    },
    {
        label: "Heading 3",
        icon: Heading3,
        active: editor.value?.isActive("heading", { level: 3 }),
        action: () =>
            editor.value?.chain().focus().toggleHeading({ level: 3 }).run(),
    },
    {
        label: "Bold",
        icon: Bold,
        active: editor.value?.isActive("bold"),
        action: () => editor.value?.chain().focus().toggleBold().run(),
    },
    {
        label: "Italic",
        icon: Italic,
        active: editor.value?.isActive("italic"),
        action: () => editor.value?.chain().focus().toggleItalic().run(),
    },
    {
        label: "Underline",
        icon: UnderlineIcon,
        active: editor.value?.isActive("underline"),
        action: () => editor.value?.chain().focus().toggleUnderline().run(),
    },
    {
        label: "Bullet List",
        icon: List,
        active: editor.value?.isActive("bulletList"),
        action: () => editor.value?.chain().focus().toggleBulletList().run(),
    },
    {
        label: "Numbered List",
        icon: ListOrdered,
        active: editor.value?.isActive("orderedList"),
        action: () => editor.value?.chain().focus().toggleOrderedList().run(),
    },
    {
        label: "Quote",
        icon: Quote,
        active: editor.value?.isActive("blockquote"),
        action: () => editor.value?.chain().focus().toggleBlockquote().run(),
    },
]);

function editLink() {
    const instance = editor.value;
    if (!instance) return;

    const current = instance.getAttributes("link").href || "";
    const input = window.prompt("Masukkan URL tautan:", current);

    if (input === null) return;

    const value = input.trim();

    if (!value) {
        instance.chain().focus().unsetLink().run();
        return;
    }

    let url: URL;

    try {
        url = new URL(value);
    } catch {
        window.alert("Gunakan URL lengkap, misalnya https://example.com");
        return;
    }

    if (!["http:", "https:"].includes(url.protocol)) {
        window.alert("Hanya tautan HTTP atau HTTPS yang diizinkan.");
        return;
    }

    instance
        .chain()
        .focus()
        .extendMarkRange("link")
        .setLink({ href: url.href })
        .run();
}

onBeforeUnmount(() => {
    editor.value?.destroy();
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div
            v-if="editor"
            class="flex flex-wrap items-center gap-1 border-b border-slate-200 bg-slate-50 p-2"
        >
            <button
                v-for="item in toolbar"
                :key="item.label"
                type="button"
                :title="item.label"
                :aria-label="item.label"
                :aria-pressed="!!item.active"
                :disabled="disabled"
                class="flex size-9 items-center justify-center rounded-lg transition disabled:opacity-40"
                :class="
                    item.active
                        ? 'bg-orange-100 text-orange-600'
                        : 'text-slate-600 hover:bg-slate-200'
                "
                @click="item.action()"
            >
                <component :is="item.icon" class="size-4" />
            </button>

            <span class="mx-1 h-6 w-px bg-slate-200"></span>

            <button
                type="button"
                title="Tambah atau ubah tautan"
                aria-label="Tambah atau ubah tautan"
                :disabled="disabled"
                class="flex size-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40"
                @click="editLink"
            >
                <Link2 class="size-4" />
            </button>

            <button
                type="button"
                title="Hapus tautan"
                aria-label="Hapus tautan"
                :disabled="disabled || !editor.isActive('link')"
                class="flex size-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40"
                @click="editor.chain().focus().unsetLink().run()"
            >
                <Unlink class="size-4" />
            </button>

            <span class="mx-1 h-6 w-px bg-slate-200"></span>

            <button
                type="button"
                title="Undo"
                aria-label="Undo"
                :disabled="disabled || !editor.can().undo()"
                class="flex size-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40"
                @click="editor.chain().focus().undo().run()"
            >
                <Undo2 class="size-4" />
            </button>

            <button
                type="button"
                title="Redo"
                aria-label="Redo"
                :disabled="disabled || !editor.can().redo()"
                class="flex size-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-40"
                @click="editor.chain().focus().redo().run()"
            >
                <Redo2 class="size-4" />
            </button>
        </div>

        <EditorContent :editor="editor" class="min-h-[320px]" />

        <div
            class="border-t border-slate-100 px-4 py-2 text-[11px] text-slate-400"
        >
            Gunakan Heading 2/3 untuk subjudul, serta toolbar untuk memformat
            isi berita.
        </div>
    </div>
</template>
