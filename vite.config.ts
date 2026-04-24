import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite-plus';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({ formVariants: true }),
    ],
    lint: {
        plugins: ['oxc', 'typescript', 'unicorn', 'vue', 'import'],
        options: {
            typeAware: true,
            typeCheck: true,
        },
    },
    fmt: {
        semi: true,
        singleQuote: true,
        ignorePatterns: [
            'resources/js/components/ui/*',
            'resources/views/mail/*',
            '.claude/**',
            '.agents/**',
            '.gemini/**',
            '.mcp.json',
            'AGENTS.md',
            'CLAUDE.md',
            'GEMINI.md',
            'boost.json',
            'opencode.json',
        ],
    },
});
