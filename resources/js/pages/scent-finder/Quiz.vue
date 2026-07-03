<script setup lang="ts">
import NavBar from '@/components/NavBar.vue';
import Footer from '@/components/Footer.vue';
import SeoHead from '@/components/SeoHead.vue';
import { useSeoHead } from '@/composables/useSeoHead';
import { router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';
import { SCENT_FAMILIES, MOOD_TAGS, INTENSITY_LABELS } from '@/lib/scentTaxonomy';

const seo = useSeoHead({
    title: 'Scent Finder',
    description: 'Answer a few quick questions and we\'ll match you with the Chapter of You fragrance you\'ll love most.',
    canonical: '/scent-finder',
});

const answers = reactive({
    scent_families: [] as string[],
    mood_tags: [] as string[],
    intensity: 3,
});

const currentStep = ref(0);
const submitting = ref(false);

const steps = [
    { key: 'families', title: 'Which scents call to you?', subtitle: 'Pick up to 2' },
    { key: 'moods', title: 'What mood are you after?', subtitle: 'Pick up to 3' },
    { key: 'intensity', title: 'How strong should it be?', subtitle: 'Slide to your preference' },
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

const canProceed = computed(() => {
    if (currentStep.value === 0) return answers.scent_families.length > 0;
    if (currentStep.value === 1) return answers.mood_tags.length > 0;
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
    router.post(route('scent-finder.results'), { ...answers }, {
        onFinish: () => { submitting.value = false; },
    });
};
</script>

<template>
    <NavBar />
    <SeoHead v-bind="seo" />

    <main class="sf">
        <div class="sf-wrap">

            <header class="sf-header">
                <p class="sf-eyebrow">Chapter of You</p>
                <h1 class="sf-title">Scent <em>Finder</em></h1>
                <div class="sf-rule">
                    <span></span><span class="sf-petal">✿</span><span></span>
                </div>
            </header>

            <div class="sf-progress">
                <div class="sf-progress-bar">
                    <div class="sf-progress-fill" :style="{ width: `${((currentStep + 1) / totalSteps) * 100}%` }" />
                </div>
                <p class="sf-progress-label">Step {{ currentStep + 1 }} of {{ totalSteps }}</p>
            </div>

            <div class="sf-card">
                <h2 class="sf-question">{{ steps[currentStep].title }}</h2>
                <p class="sf-subtitle">{{ steps[currentStep].subtitle }}</p>

                <!-- Step 1: Scent families -->
                <div v-if="currentStep === 0" class="sf-chip-grid">
                    <button v-for="family in SCENT_FAMILIES" :key="family.value" type="button"
                        class="sf-chip" :class="{ 'sf-chip--active': answers.scent_families.includes(family.value) }"
                        :disabled="!answers.scent_families.includes(family.value) && answers.scent_families.length >= 2"
                        @click="toggleFamily(family.value)">
                        {{ family.label }}
                    </button>
                </div>

                <!-- Step 2: Mood tags -->
                <div v-else-if="currentStep === 1" class="sf-chip-grid">
                    <button v-for="mood in MOOD_TAGS" :key="mood.value" type="button"
                        class="sf-chip" :class="{ 'sf-chip--active': answers.mood_tags.includes(mood.value) }"
                        :disabled="!answers.mood_tags.includes(mood.value) && answers.mood_tags.length >= 3"
                        @click="toggleMood(mood.value)">
                        {{ mood.label }}
                    </button>
                </div>

                <!-- Step 3: Intensity -->
                <div v-else class="sf-intensity">
                    <input type="range" min="1" max="5" step="1" v-model.number="answers.intensity"
                        class="sf-slider" />
                    <p class="sf-intensity-label">{{ INTENSITY_LABELS[answers.intensity] }}</p>
                </div>

                <div class="sf-actions">
                    <button v-if="currentStep > 0" type="button" class="sf-btn sf-btn--ghost" @click="back">
                        Back
                    </button>
                    <button type="button" class="sf-btn sf-btn--primary" :disabled="!canProceed || submitting"
                        @click="next">
                        {{ submitting ? 'Finding matches…' : isLastStep ? 'See my matches' : 'Next' }}
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
    transition: border-color 0.15s, background 0.15s, color 0.15s, transform 0.15s;
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

.sf-intensity {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0.5rem;
}

.sf-slider {
    width: 100%;
    accent-color: #a85058;
}

.sf-intensity-label {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.3rem;
    color: #a85058;
    font-weight: 500;
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
    transition: transform 0.2s, box-shadow 0.2s, background 0.2s;
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
</style>
