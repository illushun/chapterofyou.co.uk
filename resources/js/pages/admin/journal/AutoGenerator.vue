<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    settings: {
        enabled: boolean;
        frequency: 'daily' | 'weekly' | 'biweekly' | 'monthly';
        day_of_week: number | null;
        day_of_month: number | null;
        topic_notes: string | null;
        last_generated_at: string | null;
        last_run_status: 'success' | 'failed' | null;
        last_error: string | null;
    };
    recentAiPosts: Array<{
        id: number;
        title: string;
        slug: string;
        published_at: string | null;
    }>;
}>();

const page = usePage();
const flash = computed(() => (page.props.flash as any) ?? {});

const days = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
];

const daysOfMonth = Array.from({ length: 31 }, (_, i) => i + 1);

function ordinal(n: number): string {
    const suffixes: Record<number, string> = { 1: 'st', 2: 'nd', 3: 'rd' };
    const suffix = n % 100 >= 11 && n % 100 <= 13 ? 'th' : (suffixes[n % 10] ?? 'th');
    return `${n}${suffix}`;
}

const form = useForm({
    enabled: props.settings.enabled,
    frequency: props.settings.frequency,
    day_of_week: props.settings.day_of_week ?? 1,
    day_of_month: props.settings.day_of_month ?? 1,
    topic_notes: props.settings.topic_notes ?? '',
});

function save() {
    form.transform(d => ({ ...d, _method: 'PUT' }))
        .post(route('admin.journal.auto-generator.update'), { preserveScroll: true });
}

const generating = ref(false);
const generateForm = useForm({});

function generateNow() {
    generating.value = true;
    generateForm.post(route('admin.journal.auto-generator.generate-now'), {
        onFinish: () => { generating.value = false; },
    });
}
</script>

<template>
    <AdminLayout>
        <div class="adm-header">
            <div>
                <div class="adm-breadcrumb">
                    <Link :href="route('admin.journal.index')">Journal</Link>
                    <span class="adm-breadcrumb-sep">/</span>
                    <span>Auto Generator</span>
                </div>
                <h1 class="adm-title">Journal Auto Generator</h1>
                <p class="adm-sub">Automatically write and publish SEO-optimised journal posts on a schedule.</p>
            </div>
            <button @click="generateNow" :disabled="generating" class="adm-btn adm-btn--primary">
                <svg v-if="generating" class="adm-spinner" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.3)" stroke-width="3" />
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="#fff" stroke-width="3" stroke-linecap="round" />
                </svg>
                {{ generating ? 'Generating…' : 'Generate Now' }}
            </button>
        </div>

        <div v-if="flash.success" class="adm-flash adm-flash--success">{{ flash.success }}</div>
        <div v-if="flash.error" class="adm-flash adm-flash--error">{{ flash.error }}</div>

        <div class="je-layout">
            <div class="je-main">
                <!-- Schedule settings -->
                <div class="adm-card">
                    <h2 class="adm-card-title">Schedule</h2>

                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Status</label>
                        <div class="adm-status-btns">
                            <button type="button" @click="form.enabled = false" class="adm-status-btn"
                                :class="!form.enabled ? 'adm-status-btn--off' : ''">
                                Off
                            </button>
                            <button type="button" @click="form.enabled = true" class="adm-status-btn"
                                :class="form.enabled ? 'adm-status-btn--on' : ''">
                                On
                            </button>
                        </div>
                        <p class="adm-field-note">When on, a new post is written and published automatically on the
                            schedule below.</p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Frequency</label>
                        <select v-model="form.frequency" class="adm-input">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Every 2 weeks</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>

                    <div v-if="form.frequency === 'weekly' || form.frequency === 'biweekly'" class="adm-field">
                        <label class="adm-label adm-label--sm">Day of week</label>
                        <select v-model.number="form.day_of_week" class="adm-input">
                            <option v-for="d in days" :key="d.value" :value="d.value">{{ d.label }}</option>
                        </select>
                    </div>

                    <div v-if="form.frequency === 'monthly'" class="adm-field">
                        <label class="adm-label adm-label--sm">Day of month</label>
                        <select v-model.number="form.day_of_month" class="adm-input">
                            <option v-for="d in daysOfMonth" :key="d" :value="d">{{ ordinal(d) }}</option>
                        </select>
                        <p class="adm-field-note">If a month is shorter than this (e.g. 31st in February), the post
                            runs on the last day of that month instead.</p>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">Topic guidance <span
                                style="color:var(--bb-muted); font-weight:400;">(optional)</span></label>
                        <textarea v-model="form.topic_notes" rows="3" class="adm-textarea" maxlength="1000"
                            placeholder="e.g. Focus on gifting season, mention our new scent range…"></textarea>
                        <p class="adm-field-note">{{ form.topic_notes.length }}/1000</p>
                    </div>

                    <button @click="save" :disabled="form.processing" class="adm-btn adm-btn--primary">
                        Save Settings
                    </button>
                </div>

                <!-- Recently generated -->
                <div class="adm-card">
                    <h2 class="adm-card-title">Recently Generated</h2>
                    <ul v-if="recentAiPosts.length" style="list-style:none; display:flex; flex-direction:column; gap:0.6rem;">
                        <li v-for="post in recentAiPosts" :key="post.id"
                            style="display:flex; justify-content:space-between; gap:1rem; align-items:baseline;">
                            <Link :href="route('admin.journal.show', post.id)"
                                style="color: var(--bb-text); font-weight:600;">{{ post.title }}</Link>
                            <span style="font-size:0.78rem; color:var(--bb-muted); white-space:nowrap;">{{
                                post.published_at ?? '—' }}</span>
                        </li>
                    </ul>
                    <p v-else style="color:var(--bb-muted); font-style:italic;">No AI-generated posts yet.</p>
                </div>
            </div>

            <div class="je-sidebar">
                <div class="adm-card">
                    <h2 class="adm-card-title">Last Run</h2>
                    <div class="adm-field">
                        <label class="adm-label adm-label--sm">When</label>
                        <p>{{ settings.last_generated_at ?? 'Never run yet' }}</p>
                    </div>
                    <div v-if="settings.last_run_status" class="adm-field">
                        <label class="adm-label adm-label--sm">Status</label>
                        <span class="adm-badge" :class="settings.last_run_status === 'success' ? 'adm-badge--on' : 'adm-badge--warn'">
                            {{ settings.last_run_status }}
                        </span>
                    </div>
                    <p v-if="settings.last_error" class="adm-field-error">{{ settings.last_error }}</p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.je-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 1.5rem;
    align-items: start;
}

@media (max-width: 900px) {
    .je-layout {
        grid-template-columns: 1fr;
    }
}

.je-main {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.je-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

@media (min-width: 900px) {
    .je-sidebar {
        position: sticky;
        top: 84px;
    }
}
</style>
