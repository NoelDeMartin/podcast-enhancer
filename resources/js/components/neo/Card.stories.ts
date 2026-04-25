import type { Meta, StoryObj } from '@storybook/vue3-vite';

import { Star } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';

import Card from './Card.vue';

const meta = {
    title: 'Neo/Card',
    component: Card,
    tags: ['autodocs'],
    argTypes: {
        as: { control: 'text' },
        class: { control: 'text' },
    },
    args: {
        as: 'div',
        class: '',
    },
} satisfies Meta;

export default meta;
type Story = StoryObj<typeof meta>;

export const Default: Story = {
    render: (args) => ({
        components: { Card },
        setup: () => ({ args }),
        template: `
          <div class="bg-[#f6c2cf] min-h-svh p-6 sm:p-10">
            <Card v-bind="args" class="mx-auto max-w-2xl">
              <div class="space-y-3">
                <div class="text-lg font-extrabold tracking-tight">Neo card</div>
                <p class="text-sm font-medium text-black/70">
                  A thick border, offset shadow, and plenty of padding.
                </p>
              </div>
            </Card>
          </div>
        `,
    }),
};

export const WithSections: Story = {
    render: (args) => ({
        components: { Card },
        setup: () => ({ args }),
        template: `
          <div class="bg-[#f6c2cf] min-h-svh p-6 sm:p-10">
            <Card v-bind="args" class="mx-auto max-w-2xl">
              <div class="flex flex-col gap-6">
                <div class="space-y-1">
                  <div class="text-xs font-black uppercase tracking-[0.18em] text-black/60">
                    Component
                  </div>
                  <div class="text-2xl font-black tracking-tight">Card header</div>
                </div>

                <p class="text-sm font-medium text-black/70">
                  Use slots to compose anything inside. Add your own layout with Tailwind utilities.
                </p>

                <div class="grid grid-cols-2 gap-3">
                  <div class="border-2 border-black bg-white p-4 font-semibold shadow-[4px_4px_0_0_#000]">
                    Left
                  </div>
                  <div class="border-2 border-black bg-white p-4 font-semibold shadow-[4px_4px_0_0_#000]">
                    Right
                  </div>
                </div>
              </div>
            </Card>
          </div>
        `,
    }),
};

export const HeroLike: Story = {
    render: (args) => ({
        components: { Button, Card, Star },
        setup: () => ({ args }),
        template: `
          <div class="bg-[#f6c2cf] min-h-svh p-6 sm:p-10">
            <Card v-bind="args" class="mx-auto max-w-5xl">
              <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-balance text-4xl font-black leading-[0.95] tracking-tight sm:text-5xl md:text-6xl">
                  UPGRADE YOUR PODCASTS WITH AI.
                  <br />
                  KEEP YOUR FAVORITE PLAYER.
                </h1>

                <p class="mt-6 text-pretty text-base font-medium text-black/70 sm:text-lg">
                  Supercharge your listening experience. Generate smart summaries, clickable chapters, and full transcripts
                  injected directly into a custom RSS feed you can use anywhere.
                </p>

                <div class="mt-10 flex flex-col items-stretch justify-center gap-4 sm:flex-row sm:items-center">
                  <Button
                    as-child
                    size="lg"
                    class="rounded-none border-2 border-black bg-[#d5ff00] px-8 text-black shadow-[6px_6px_0_0_#000] hover:bg-[#c8f200]"
                  >
                    <a href="#">Get Started (Free Trial)</a>
                  </Button>

                  <Button
                    as-child
                    size="lg"
                    variant="outline"
                    class="rounded-none border-2 border-black bg-white px-8 text-black shadow-[6px_6px_0_0_#000] hover:bg-black/5"
                  >
                    <a href="#" rel="noreferrer">
                      <Star class="mr-1.5 size-4" />
                      Star on GitHub
                    </a>
                  </Button>
                </div>
              </div>
            </Card>
          </div>
        `,
    }),
};
