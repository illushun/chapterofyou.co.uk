<script setup lang="ts">
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
        class="!p-0"
    >
        <Head title="Register" />

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
                <Form
                    v-bind="RegisteredUserController.store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-6 w-full"
                >
                    <div class="grid gap-6">
                        <div class="grid gap-2">
                            <Label for="name" class="text-copy font-semibold">Name</Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="name"
                                name="name"
                                placeholder="Full name"
                                class="border-2 border-copy rounded-lg p-3 focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="text-copy font-semibold">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="2"
                                autocomplete="email"
                                name="email"
                                placeholder="email@example.com"
                                class="border-2 border-copy rounded-lg p-3 focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="text-copy font-semibold">Password</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="3"
                                autocomplete="new-password"
                                name="password"
                                placeholder="Password"
                                class="border-2 border-copy rounded-lg p-3 focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password_confirmation" class="text-copy font-semibold">Confirm password</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                name="password_confirmation"
                                placeholder="Confirm password"
                                class="border-2 border-copy rounded-lg p-3 focus:border-primary focus:ring-primary text-copy"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 w-full border-2 border-copy text-lg font-bold shadow-lg transition-colors duration-300 hover:bg-primary-dark"
                            tabindex="5"
                            :disabled="processing"
                            data-test="register-user-button"
                            style="background-color: var(--primary); color: var(--primary-content);"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-4 w-4 animate-spin mr-2"
                            />
                            Create account
                        </Button>
                    </div>

                    <div class="text-center text-sm text-copy">
                        Already have an account?
                        <TextLink
                            :href="login()"
                            class="text-secondary-content hover:text-secondary-dark transition font-semibold"
                            :tabindex="6"
                        >
                            Log in
                        </TextLink>
                    </div>
                </Form>
            </div>
        </div>
    </AuthBase>
</template>
