<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, LockKeyhole, ShieldCheck } from '@lucide/vue';
import { ref } from 'vue';
import PasskeyVerify from '@/components/PasskeyVerify.vue';

defineProps<{
    canResetPassword: boolean;
    forgotPasswordUrl: string;
    loginUrl: string;
    passwordRules: string;
    status?: string;
    office: {
        portalName: string;
        representativeName: string;
        district: string;
    };
}>();

const showPassword = ref(false);
</script>

<template>
    <Head title="District Office Administration" />

    <main data-admin-theme="command" class="admin-login-page">
        <a href="#admin-login-form" class="admin-skip-link"
            >Skip to secure sign in</a
        >

        <div class="admin-login-frame">
            <section
                class="admin-login-identity"
                aria-labelledby="admin-login-title"
            >
                <div class="admin-login-identity__marks">
                    <span class="admin-login-identity__seals">
                        <img
                            src="/assets/images/brand/House_of_Representative_Logo_Seal.svg"
                            alt="House of Representatives seal"
                            width="52"
                            height="52"
                        />
                        <img
                            src="/assets/images/brand/Logo_of_San_Juan_Seal.svg"
                            alt="Official seal of San Juan City"
                            width="52"
                            height="52"
                        />
                    </span>
                    <span class="admin-login-identity__system-state">
                        <span aria-hidden="true"></span>
                        Secure staff workspace
                    </span>
                </div>

                <div class="admin-login-identity__copy">
                    <p class="admin-kicker admin-kicker--gold">
                        Internal operations system
                    </p>
                    <h1 id="admin-login-title">
                        District Office<br />Command System
                    </h1>
                    <p>
                        A secure workspace for constituent inquiries,
                        public-content review, program records, and accountable
                        District Office work.
                    </p>
                </div>

                <div
                    class="admin-login-identity__ledger"
                    aria-label="Security controls"
                >
                    <div>
                        <ShieldCheck aria-hidden="true" />
                        <span
                            ><strong>Role protected</strong>Access follows
                            assigned office duties.</span
                        >
                    </div>
                    <div>
                        <LockKeyhole aria-hidden="true" />
                        <span
                            ><strong>Activity recorded</strong>Staff actions are
                            saved in the activity log.</span
                        >
                    </div>
                </div>

                <p class="admin-login-identity__notice">
                    Authorized District Office personnel only. Do not share
                    credentials or leave an active session unattended.
                </p>
            </section>

            <section
                id="admin-login-form"
                class="admin-login-panel"
                tabindex="-1"
            >
                <header class="admin-login-panel__header">
                    <div class="admin-login-panel__brand">
                        <img
                            src="/assets/images/brand/CongBelLogo_transparent.png"
                            alt="Cong. Bel Zamora - Dunong at Dangal"
                            width="152"
                            height="73"
                        />
                        <div>
                            <strong>Atty. Bel Zamora</strong>
                            <span>{{ office.district }}</span>
                        </div>
                    </div>
                    <p class="admin-kicker">Secure staff sign in</p>
                    <h2>Enter the command system</h2>
                    <p>
                        Use your assigned District Office account to continue.
                    </p>
                </header>

                <div v-if="status" class="admin-form-notice" role="status">
                    {{ status }}
                </div>

                <div class="admin-login-passkey">
                    <PasskeyVerify
                        label="Continue with a passkey"
                        separator="Or use your staff credentials"
                    />
                </div>

                <Form
                    :action="loginUrl"
                    method="post"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="admin-login-form"
                >
                    <label class="admin-field">
                        <span>Staff email address</span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="name@district-office.gov.ph"
                            :aria-invalid="Boolean(errors.email)"
                            :aria-describedby="
                                errors.email ? 'email-error' : undefined
                            "
                        />
                        <small
                            v-if="errors.email"
                            id="email-error"
                            class="admin-field__error"
                        >
                            {{ errors.email }}
                        </small>
                    </label>

                    <label class="admin-field">
                        <span>Password</span>
                        <span class="admin-password-field">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                :aria-invalid="Boolean(errors.password)"
                                :aria-describedby="
                                    errors.password
                                        ? 'password-error'
                                        : undefined
                                "
                            />
                            <button
                                type="button"
                                :aria-label="
                                    showPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff
                                    v-if="showPassword"
                                    aria-hidden="true"
                                />
                                <Eye v-else aria-hidden="true" />
                            </button>
                        </span>
                        <small
                            v-if="errors.password"
                            id="password-error"
                            class="admin-field__error"
                        >
                            {{ errors.password }}
                        </small>
                    </label>

                    <div class="admin-login-form__options">
                        <label class="admin-checkbox">
                            <input type="checkbox" name="remember" value="1" />
                            <span>Keep me signed in on this device</span>
                        </label>
                        <a v-if="canResetPassword" :href="forgotPasswordUrl">
                            Reset password
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="admin-button admin-button--primary admin-login-form__submit"
                        :disabled="processing"
                    >
                        <LockKeyhole aria-hidden="true" />
                        {{
                            processing
                                ? 'Verifying access...'
                                : 'Enter command system'
                        }}
                    </button>
                </Form>

                <footer class="admin-login-panel__footer">
                    <span
                        >Protected by rate limiting, secure sessions, and staff
                        access rules.</span
                    >
                    <strong>{{ office.portalName }}</strong>
                </footer>
            </section>
        </div>
    </main>
</template>
