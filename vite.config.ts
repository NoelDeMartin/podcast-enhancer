import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { FileSystemIconLoader } from 'unplugin-icons/loaders';
import IconsResolver from 'unplugin-icons/resolver';
import Icons from 'unplugin-icons/vite';
import Components from 'unplugin-vue-components/vite';
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
        Components({
            resolvers: [IconsResolver()],
        }),
        Icons({
            compiler: 'vue3',
            customCollections: {
                app: FileSystemIconLoader('./resources/js/icons'),
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
        sortImports: true,
        sortTailwindcss: true,
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
