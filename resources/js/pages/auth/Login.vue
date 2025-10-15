<script setup lang="ts">
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Log in to your account"
        description="Enter your email and password below to log in"
        class="!p-0"
    >
        <Head title="Log in" />

        <div
            class="
                w-full max-w-sm mx-auto
                rounded-xl border-2 border-copy bg-[var(--primary-content)] p-0
                shadow-xl transition-all
            "
        >
            <div
                class="
                    relative rounded-xl -m-0.5 border-2 border-copy bg-foreground p-8 md:p-10
                    flex flex-col gap-6
                "
            >
                <h1 class="text-3xl font-extrabold text-copy mb-[-0.5rem]">Log in to your account</h1>
                <p class="text-copy-lighter text-sm">Enter your email and password below to log in</p>

                <div
                    v-if="status"
                    class="mb-4 text-center text-sm font-medium text-green-600"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="AuthenticatedSessionController.store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-6 w-full"
                >
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="email" class="text-copy font-semibold">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                class="border-2 border-copy rounded-lg p-3 shadow-md focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-copy font-semibold">Password</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-sm text-primary hover:text-primary-dark transition"
                                    :tabindex="5"
                                >
                                    Forgot password?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Password"
                                class="border-2 border-copy rounded-lg p-3 shadow-md focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <Label for="remember" class="flex items-center space-x-3 text-copy-light cursor-pointer">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    :tabindex="3"
                                    class="border-copy data-[state=checked]:bg-[var(--primary)] data-[state=checked]:text-primary-content"
                                />
                                <span>Remember me</span>
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 w-full border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 hover:bg-primary-dark"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                            style="background-color: var(--primary); color: var(--primary-content);"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-4 w-4 animate-spin mr-2"
                            />
                            Log in
                        </Button>
                    </div>

                    <div class="text-center text-sm text-copy-lighter">
                        Don't have an account?
                        <TextLink :href="register()" :tabindex="5" class="text-primary hover:text-primary-dark transition font-semibold">Sign up</TextLink>
                    </div>
                </Form>
            </div>
        </div>

    </AuthBase>
</template>
