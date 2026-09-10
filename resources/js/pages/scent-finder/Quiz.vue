<script setup lang="ts">
import Footer from '@/components/Footer.vue';
import NavBar from '@/components/NavBar.vue';
import SeoHead from '@/components/SeoHead.vue';
import CoyBreadcrumbs from '@/components/ui/coy/CoyBreadcrumbs.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { MOOD_TAGS, ROOMS, SCENT_FAMILIES } from '@/lib/scentTaxonomy';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const seo = useSeoHead({
    title: 'Scent Finder',
    description:
        "Answer a few quick questions and we'll match you with the Chapter of You fragrance you'll love most.",
    canonical: '/scent-finder',
});

const answers = reactive({
    scent_families: [] as string[],
    mood_tags: [] as string[],
    room_tags: [] as string[],
});

const currentStep = ref(0);
const submitting = ref(false);

const steps = [
    {
        key: 'families',
        title: 'Which scents call to you?',
        subtitle: 'Pick up to 2',
    },
    {
        key: 'moods',
        title: 'What mood are you after?',
        subtitle: 'Pick up to 3',
    },
    { key: 'rooms', title: 'Where will you use it?', subtitle: 'Pick up to 2' },
] as const;

const totalSteps = steps.length;

const toggleFamily = (value: string) => {
    const idx = answers.scent_families.indexOf(value);
    if (idx !== -1) {
        answers.scent_families.splice(idx, 1);
    } else if (answers.scent_families.length < 2) {
        answers.scent_families.push(value);
    }
};

const toggleMood = (value: string) => {
    const idx = answers.mood_tags.indexOf(value);
    if (idx !== -1) {
        answers.mood_tags.splice(idx, 1);
    } else if (answers.mood_tags.length < 3) {
        answers.mood_tags.push(value);
    }
};

const toggleRoom = (value: string) => {
    const idx = answers.room_tags.indexOf(value);
    if (idx !== -1) {
        answers.room_tags.splice(idx, 1);
    } else if (answers.room_tags.length < 2) {
        answers.room_tags.push(value);
    }
};

const canProceed = computed(() => {
    if (currentStep.value === 0) return answers.scent_families.length > 0;
    if (currentStep.value === 1) return answers.mood_tags.length > 0;
    if (currentStep.value === 2) return answers.room_tags.length > 0;
    return true;
});

const isLastStep = computed(() => currentStep.value === totalSteps - 1);

const next = () => {
    if (!canProceed.value) return;
    if (isLastStep.value) {
        submit();
    } else {
        currentStep.value++;
    }
};

const back = () => {
    if (currentStep.value > 0) currentStep.value--;
};

const submit = () => {
    submitting.value = true;
    router.get(
        route('scent-finder.results'),
        { ...answers },
        {
            onFinish: () => {
                submitting.value = false;
            },
        },
    );
};
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <main class="sf coy-storefront">
        <header class="coy-page-header">
            <div class="coy-container coy-page-header__inner">
                <div>
                    <CoyBreadcrumbs
                        :items="[
                            { label: 'Home', href: '/' },
                            { label: 'Scent finder' },
                        ]"
                    />
                    <h1 class="coy-page-header__title">Find your scent</h1>
                    <p class="coy-page-header__meta">
                        Tell us what you enjoy and we will suggest the closest
                        matches.
                    </p>
                </div>
            </div>
        </header>
        <div class="sf-wrap">
            <div class="sf-progress">
                <p class="sf-progress-label">
                    Question {{ currentStep + 1 }} of {{ totalSteps }}
                </p>
                <div class="sf-progress-bar">
                    <div
                        class="sf-progress-fill"
                        :style="{
                            width: `${((currentStep + 1) / totalSteps) * 100}%`,
                        }"
                    />
                </div>
            </div>

            <div class="sf-card">
                <h2 class="sf-question">{{ steps[currentStep].title }}</h2>
                <p class="sf-subtitle">{{ steps[currentStep].subtitle }}</p>

                <div v-if="currentStep === 0" class="sf-chip-grid">
                    <button
                        v-for="family in SCENT_FAMILIES"
                        :key="family.value"
                        type="button"
                        class="sf-chip"
                        :class="{
                            'sf-chip--active': answers.scent_families.includes(
                                family.value,
                            ),
                        }"
                        :disabled="
                            !answers.scent_families.includes(family.value) &&
                            answers.scent_families.length >= 2
                        "
                        :aria-pressed="
                            answers.scent_families.includes(family.value)
                        "
                        @click="toggleFamily(family.value)"
                    >
                        {{ family.label }}
                    </button>
                </div>

                <div v-else-if="currentStep === 1" class="sf-chip-grid">
                    <button
                        v-for="mood in MOOD_TAGS"
                        :key="mood.value"
                        type="button"
                        class="sf-chip"
                        :class="{
                            'sf-chip--active': answers.mood_tags.includes(
                                mood.value,
                            ),
                        }"
                        :disabled="
                            !answers.mood_tags.includes(mood.value) &&
                            answers.mood_tags.length >= 3
                        "
                        :aria-pressed="answers.mood_tags.includes(mood.value)"
                        @click="toggleMood(mood.value)"
                    >
                        {{ mood.label }}
                    </button>
                </div>

                <div v-else class="sf-chip-grid">
                    <button
                        v-for="room in ROOMS"
                        :key="room.value"
                        type="button"
                        class="sf-chip"
                        :class="{
                            'sf-chip--active': answers.room_tags.includes(
                                room.value,
                            ),
                        }"
                        :disabled="
                            !answers.room_tags.includes(room.value) &&
                            answers.room_tags.length >= 2
                        "
                        :aria-pressed="answers.room_tags.includes(room.value)"
                        @click="toggleRoom(room.value)"
                    >
                        {{ room.label }}
                    </button>
                </div>

                <div class="sf-actions">
                    <button
                        v-if="currentStep > 0"
                        type="button"
                        class="sf-btn sf-btn--ghost"
                        @click="back"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        class="sf-btn sf-btn--primary"
                        :disabled="!canProceed || submitting"
                        @click="next"
                    >
                        {{
                            submitting
                                ? 'Finding matches…'
                                : isLastStep
                                  ? 'See my matches'
                                  : 'Next'
                        }}
                    </button>
                </div>
            </div>
        </div>
    </main>

    <Footer />
</template>

<style scoped>
.sf {
    min-height: 70vh;
    background: #fdf4f3;
    padding: 4rem 1.5rem 5rem;
}

.sf-wrap {
    max-width: 640px;
    margin: 0 auto;
    padding: 3rem 1.25rem 6rem;
}

.sf-header {
    text-align: center;
    margin-bottom: 2rem;
}

.sf-eyebrow {
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #a85058;
    margin-bottom: 0.5rem;
}

.sf-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.5rem;
    font-weight: 400;
    color: #2d1a1a;
}

.sf-title em {
    font-style: italic;
    color: #a85058;
}

.sf-rule {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    margin-top: 0.75rem;
}

.sf-rule span:not(.sf-petal) {
    width: 40px;
    height: 1px;
    background: #e5c9c7;
}

.sf-petal {
    color: #c9a4a4;
    font-size: 0.9rem;
}

.sf-progress {
    max-width: 320px;
    margin: 0 auto 2.5rem;
}

.sf-progress-bar {
    height: 6px;
    border-radius: 999px;
    background: #f0dcd8;
    overflow: hidden;
}

.sf-progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #c47078, #a85058);
    transition: width 0.3s ease;
}

.sf-progress-label {
    text-align: center;
    font-family: 'Nunito', sans-serif;
    font-size: 0.78rem;
    color: #8c6a6a;
    margin-top: 0.5rem;
}

.sf-card {
    background: #fffafa;
    border: 1px solid #e5c9c7;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(229, 201, 199, 0.35);
    padding: 2.25rem 2rem;
}

.sf-question {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.6rem;
    font-weight: 500;
    color: #2d1a1a;
    text-align: center;
}

.sf-subtitle {
    font-family: 'Nunito', sans-serif;
    font-size: 0.85rem;
    color: #8c6a6a;
    text-align: center;
    margin-top: 0.25rem;
    margin-bottom: 1.75rem;
}

.sf-chip-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 0.75rem;
}

.sf-chip {
    padding: 0.85rem 1rem;
    border-radius: 14px;
    border: 1.5px solid #e5c9c7;
    background: #fdf4f3;
    color: #6b4f4f;
    font-family: 'Nunito', sans-serif;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        border-color 0.15s,
        background 0.15s,
        color 0.15s,
        transform 0.15s;
}

.sf-chip:hover:not(:disabled) {
    border-color: #c9a4a4;
    transform: translateY(-1px);
}

.sf-chip--active {
    background: linear-gradient(135deg, #c47078, #a85058);
    border-color: #a85058;
    color: #fff;
}

.sf-chip:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.sf-actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 2rem;
}

.sf-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.75rem;
    border-radius: 999px;
    font-family: 'Nunito', sans-serif;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        transform 0.2s,
        box-shadow 0.2s,
        background 0.2s;
    border: 1px solid transparent;
}

.sf-btn--primary {
    background: linear-gradient(135deg, #c47078, #a85058);
    border-color: #a85058;
    color: #fff;
    box-shadow: 0 3px 12px rgba(168, 80, 88, 0.2);
}

.sf-btn--primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 5px 18px rgba(168, 80, 88, 0.28);
}

.sf-btn--primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.sf-btn--ghost {
    background: rgba(255, 250, 250, 0.7);
    border-color: #e5c9c7;
    color: #6b4f4f;
}

.sf-btn--ghost:hover {
    background: #faeaea;
    border-color: #c9a4a4;
}

.sf {
    min-height: 70vh;
    padding: var(--coy-nav-height) 0 0;
    background: var(--coy-color-page);
}

.sf-wrap {
    width: min(calc(100% - (var(--coy-gutter) * 2)), var(--coy-container-md));
    max-width: none;
    margin-inline: auto;
    padding: clamp(2.5rem, 6vw, 5rem) 0 clamp(4rem, 8vw, 7rem);
}

.sf-header {
    max-width: 42rem;
    margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
    text-align: left;
}

.sf-title {
    margin: 0.45rem 0 0.8rem;
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(2.25rem, 5vw, 3.5rem);
    font-weight: 500;
    line-height: 0.98;
    letter-spacing: -0.035em;
}

.sf-intro {
    max-width: 39rem;
    color: var(--coy-color-text);
    font-size: 1.05rem;
    line-height: 1.7;
}

.sf-progress {
    max-width: none;
    margin: 0 0 1rem;
}

.sf-progress-label {
    margin: 0 0 0.55rem;
    color: var(--coy-color-text);
    font-family: var(--coy-font-body);
    font-size: 0.875rem;
    font-weight: 600;
    text-align: left;
}

.sf-progress-bar {
    height: 0.25rem;
    border-radius: 0;
    background: var(--coy-color-border-soft);
}

.sf-progress-fill {
    background: var(--coy-color-accent);
    transition: width var(--coy-duration-base) var(--coy-ease);
}

.sf-card {
    padding: clamp(1.5rem, 4vw, 2.5rem);
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-lg);
    background: var(--coy-color-surface);
    box-shadow: none;
}

.sf-question {
    color: var(--coy-color-heading);
    font-family: var(--coy-font-display);
    font-size: clamp(1.75rem, 4vw, 2.35rem);
    font-weight: 500;
    line-height: 1.15;
    text-align: left;
}

.sf-subtitle {
    margin: 0.35rem 0 1.75rem;
    color: var(--coy-color-text);
    font-family: var(--coy-font-body);
    font-size: 1rem;
    text-align: left;
}

.sf-chip-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}

.sf-chip {
    min-height: 3.5rem;
    padding: 0.85rem 1rem;
    border: 1px solid var(--coy-color-border);
    border-radius: var(--coy-radius-sm);
    background: var(--coy-color-page);
    color: var(--coy-color-heading);
    font-family: var(--coy-font-body);
    font-size: 1rem;
    font-weight: 600;
    text-align: left;
    transition:
        border-color var(--coy-duration-fast) var(--coy-ease),
        background-color var(--coy-duration-fast) var(--coy-ease),
        color var(--coy-duration-fast) var(--coy-ease);
}

.sf-chip:hover:not(:disabled) {
    border-color: var(--coy-color-accent);
    background: var(--coy-color-surface-soft);
    transform: none;
}

.sf-chip--active,
.sf-chip--active:hover:not(:disabled) {
    border-color: var(--coy-color-heading);
    background: var(--coy-color-heading);
    color: var(--coy-color-on-accent);
}

.sf-chip:disabled {
    opacity: 0.45;
}

.sf-actions {
    justify-content: flex-end;
    margin-top: 2rem;
}

.sf-btn {
    min-height: var(--coy-control-height);
    padding: 0.75rem 1.4rem;
    border-radius: var(--coy-radius-sm);
    font-family: var(--coy-font-body);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    transition: opacity var(--coy-duration-fast) var(--coy-ease);
}

.sf-btn--primary {
    border-color: var(--coy-color-heading);
    background: var(--coy-color-heading);
    box-shadow: none;
    color: var(--coy-color-on-accent);
}

.sf-btn--primary:hover:not(:disabled) {
    box-shadow: none;
    opacity: 0.86;
    transform: none;
}

.sf-btn--ghost {
    border-color: var(--coy-color-border);
    background: transparent;
    color: var(--coy-color-heading);
}

.sf-btn--ghost:hover {
    border-color: var(--coy-color-heading);
    background: var(--coy-color-surface-soft);
}

@media (max-width: 560px) {
    .sf-wrap {
        padding-top: 2rem;
    }

    .sf-chip-grid {
        grid-template-columns: 1fr;
    }

    .sf-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .sf-actions .sf-btn:only-child {
        grid-column: 2;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sf-progress-fill,
    .sf-chip,
    .sf-btn {
        transition: none;
    }
}
</style>
