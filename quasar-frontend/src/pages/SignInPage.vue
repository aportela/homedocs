<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section class="text-center">
          <q-avatar square size="128px">
            <img src="icons/favicon-128x128.png" />
          </q-avatar>
          <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{
            t(!!savedEmail ? "Glad to see you again!" : "Welcome aboard!")
          }}</h4>
          <div class="text-color-secondary">{{
            t(!!savedEmail ? "Let's get back to organizing." : "Let's start organizing.")
            }}
          </div>
        </q-card-section>
        <q-card-section>
          <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
            :disable="loading" :autofocus="!savedEmail" :rules="formUtils.requiredFieldRules" lazy-rules
            :error="validator.email.hasErrors"
            :errorMessage="validator.email.message ? t(validator.email.message) : ''">
            <template v-slot:prepend>
              <q-icon name="alternate_email" />
            </template>
          </q-input>
          <CustomInputPassword dense outlined ref="passwordRef" class="q-mt-md" v-model="password" name="password"
            :label="t('Password')" :autofocus="!!savedEmail" :rules="formUtils.requiredFieldRules" lazy-rules
            :error="validator.password.hasErrors"
            :errorMessage="validator.password.message ? t(validator.password.message) : ''">
          </CustomInputPassword>
        </q-card-section>
        <q-card-section>
          <q-btn color="primary" size="md" :label="$t('Sign in')" no-caps class="full-width" icon="account_circle"
            :disable="loading || (!(email && password))" :loading="loading" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t('Sign in') }}
            </template>
          </q-btn>
          <CustomBanner v-if="error && errorMessage" :text="errorMessage" :error="true">
            <template v-slot:details v-if="error && initialState.isDevEnvironment && apiError">
              <APIErrorDetails class="q-mt-md" :apiError="apiError"></APIErrorDetails>
            </template>
          </CustomBanner>
        </q-card-section>
        <q-card-section class="text-center q-pt-none" v-if="signUpAllowed">
          <div>
            {{ t("Don't have an account yet ?") }}
            <router-link :to="{ name: 'signUp' }" class="main-app-text-link-hover">{{ t("Click here to sign up") }}
            </router-link>
          </div>
        </q-card-section>
        <q-separator class="q-mb-sm" />
        <q-card-section class="text-center q-py-none">
          <q-btn-group flat square>
            <DarkModeButton />
            <SwitchLanguageButton />
            <GitHubButton label="@2025 HomeDocs" :href="GITHUB_PROJECT_URL" />
          </q-btn-group>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, nextTick, computed } from "vue";
import { LocalStorage } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'

import { api } from 'boot/axios'
import { useFormUtils } from "src/composables/formUtils";
import { useInitialStateStore } from "stores/initialState";

import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as CustomInputPassword } from "src/components/CustomInputPassword.vue";
import { default as CustomBanner } from "src/components/CustomBanner.vue";
import { default as APIErrorDetails } from "components/APIErrorDetails.vue";

const { t } = useI18n();

const formUtils = useFormUtils();

const router = useRouter();

const initialState = useInitialStateStore();

const signUpAllowed = computed(() => initialState.isSignUpAllowed);

const loading = ref(false);
const error = ref(false);
const errorMessage = ref(null);
const apiError = ref(null);

const validator = ref({
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
});

const savedEmail = LocalStorage.getItem("email");

const email = ref(savedEmail || null);
const emailRef = ref(null);
const password = ref(null);
const passwordRef = ref(null);

const onResetForm = () => {
  validator.value.email.hasErrors = false;
  validator.value.email.message = null;
  validator.value.password.hasErrors = false;
  validator.value.password.message = null;
  emailRef.value.resetValidation();
  passwordRef.value.resetValidation();
}

const onValidateForm = () => {
  onResetForm();
  emailRef.value.validate();
  passwordRef.value.validate();
  nextTick(() => {
    if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
      onSubmitForm();
    }
  });
}

const onSubmitForm = () => {
  loading.value = true;
  error.value = false;
  errorMessage.value = null;
  apiError.value = null;
  api.user
    .signIn(email.value, password.value)
    .then((successResponse) => {
      LocalStorage.set("email", email.value);
      loading.value = false;
      router.push({
        name: "index",
      });
    })
    .catch((errorResponse) => {
      loading.value = false;
      error.value = true;
      apiError.value = errorResponse.customAPIErrorDetails;
      switch (errorResponse.response.status) {
        case 400:
          if (
            errorResponse.response.data.invalidOrMissingParams.find(function (e) {
              return e === "email";
            })
          ) {
            errorMessage.value = "API Error: missing email param";
            nextTick(() => {
              emailRef.value?.focus();
            });
          } else if (
            errorResponse.response.data.invalidOrMissingParams.find(function (e) {
              return e === "password";
            })
          ) {
            errorMessage.value = "API Error: missing password param";
            nextTick(() => {
              passwordRef.value?.focus();
            });
            passwordRef.value.focus();
          } else {
            errorMessage.value = "API Error: invalid/missing param";
            nextTick(() => {
              emailRef.value?.focus();
            });
          }
          break;
        case 404:
          validator.value.email.hasErrors = true;
          validator.value.email.message = "Email not registered";
          nextTick(() => {
            emailRef.value?.focus();
          });
          break;
        case 401:
          validator.value.password.hasErrors = true;
          validator.value.password.message = "Invalid password";
          nextTick(() => {
            passwordRef.value?.focus();
          });
          break;
        default:
          errorMessage.value = "API Error: fatal error";
          break;
      }
    });
}

</script>