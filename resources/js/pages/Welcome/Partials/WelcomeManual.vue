<script setup lang="ts">
import AiGenerate from '~icons/carbon/ai-generate';
import Cut from '~icons/carbon/cut';
import Headphones from '~icons/carbon/headphones';
import Idea from '~icons/carbon/idea';
import LogoGithub from '~icons/carbon/logo-github';
import Rss from '~icons/carbon/rss';
import Search from '~icons/carbon/search';

import WelcomeManualFeature from './WelcomeManualFeature.vue';
import WelcomeManualStep from './WelcomeManualStep.vue';
</script>

<template>
    <section id="how-it-works" class="pt-16 pb-16 sm:pt-40 sm:pb-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6">
            <h2
                class="border-neo-dark bg-neo-pink shadow-neo-hard inline-block -rotate-2 border-3 px-4 py-2 text-2xl font-black tracking-tighter uppercase transition-transform duration-300 hover:rotate-0 sm:text-5xl"
            >
                How does it work?
            </h2>

            <div class="relative mx-auto mt-20 max-w-5xl">
                <div class="space-y-12">
                    <WelcomeManualStep
                        :icon="Rss"
                        :number="1"
                        title="Bring your own feed"
                        description="Grab a standard RSS link from any show you want to listen to."
                        wrapper-class="hover:shadow-neo-hard-pink"
                        icon-wrapper-class="bg-neo-blue"
                    />

                    <WelcomeManualStep
                        :icon="AiGenerate"
                        :number="2"
                        title="Get automatic enhancements"
                        description="The feed will be upgraded with full transcriptions, chapters, and summaries."
                        wrapper-class="hover:shadow-neo-hard-blue"
                        icon-wrapper-class="bg-neo-pink"
                    />

                    <WelcomeManualStep
                        :icon="Headphones"
                        :number="3"
                        title="Listen from anywhere"
                        description="Get a newly enhanced RSS link that you can use in Overcast, AntennaPod, Apple Podcasts, or wherever you listen to podcasts."
                        wrapper-class="hover:shadow-neo-hard-pink"
                        icon-wrapper-class="bg-neo-blue"
                        last
                    />
                </div>
            </div>

            <div class="mt-32">
                <h3
                    class="border-neo-dark bg-neo-yellow shadow-neo-hard inline-block rotate-1 border-3 px-4 py-2 text-xl font-black tracking-tighter uppercase transition-transform duration-300 hover:rotate-0 sm:text-3xl"
                >
                    And much more...
                </h3>

                <div class="mx-auto mt-12 grid max-w-6xl gap-8 sm:grid-cols-2 sm:gap-10">
                    <WelcomeManualFeature
                        :icon="Idea"
                        title="Catch the Gist"
                        description="No idea what that two-hour episode is all about? Get a quick summary to find out before you start."
                        icon-class="text-neo-pink"
                        wrapper-class="hover:shadow-neo-hard-blue"
                    />
                    <WelcomeManualFeature
                        :icon="Cut"
                        title="Skip the Fluff"
                        description="Take control of your time and skip the parts you're not interested in."
                        icon-class="text-neo-blue"
                        wrapper-class="hover:shadow-neo-hard-pink"
                    />
                    <WelcomeManualFeature
                        :icon="Search"
                        title="Search the Conversation"
                        description="Never miss a detail again. Instead of rewinding to find that one mention, search the transcript of the entire conversation."
                        icon-class="text-neo-blue"
                        wrapper-class="hover:shadow-neo-hard-pink"
                    />
                    <WelcomeManualFeature
                        :icon="LogoGithub"
                        title="100% Open Source"
                        description="Go ahead and look under the hood, Podcast Enhancer is completely open source. You can even self-host it if you want!"
                        icon-class="text-neo-pink"
                        wrapper-class="hover:shadow-neo-hard-blue"
                    />
                </div>
            </div>
        </div>
    </section>
</template>
