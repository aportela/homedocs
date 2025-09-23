<template>
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
      <q-input dense outlined ref="emailRef" v-model="profile.email" type="email" name="email" :label="t('Email')"
        :disable="state.loading" :autofocus="!savedEmail" :rules="formUtils.requiredFieldRules" lazy-rules
        :error="validator.email.hasErrors" :errorMessage="validator.email.message ? t(validator.email.message) : ''">
        <template v-slot:prepend>
          <q-icon name="alternate_email" />
        </template>
      </q-input>
      <CustomInputPassword dense outlined ref="passwordRef" class="q-mt-md" v-model="profile.password" name="password"
        :label="t('Password')" :disable="state.loading" :autofocus="!!savedEmail" :rules="formUtils.requiredFieldRules"
        lazy-rules :error="validator.password.hasErrors"
        :errorMessage="validator.password.message ? t(validator.password.message) : ''">
      </CustomInputPassword>
    </q-card-section>
    <q-card-section>
      <q-btn color="primary" size="md" :label="$t('Sign in')" no-caps class="full-width" icon="account_circle"
        :disable="state.loading || (!(profile.email && profile.password))" :loading="state.loading" type="submit">
        <template v-slot:loading>
          <q-spinner-hourglass class="on-left" />
          {{ t('Sign in') }}
        </template>
      </q-btn>
      <CustomErrorBanner v-if="state.loadingError && state.errorMessage" :text="state.errorMessage"
        :apiError="state.apiError" class="q-mt-lg">
      </CustomErrorBanner>
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
</template>

<script setup>

import { ref, reactive, nextTick, computed } from "vue";
import { LocalStorage } from "quasar";
import { useI18n } from 'vue-i18n'

import { api } from 'boot/axios'
import { useFormUtils } from "src/composables/formUtils";
import { useInitialStateStore } from "stores/initialState";

import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
import { default as CustomInputPassword } from "src/components/CustomInputPassword.vue";
import { default as CustomErrorBanner } from "components/CustomErrorBanner.vue";

const emit = defineEmits(['success']);

const { t } = useI18n();

const formUtils = useFormUtils();

const initialState = useInitialStateStore();

const signUpAllowed = computed(() => initialState.isSignUpAllowed === true);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const validator = reactive({
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

const profile = reactive(
  {
    email: savedEmail || null,
    password: null
  }
);

const emailRef = ref(null);
const passwordRef = ref(null);

const onResetForm = () => {
  validator.email.hasErrors = false;
  validator.email.message = null;
  validator.password.hasErrors = false;
  validator.password.message = null;
  emailRef.value?.resetValidation();
  passwordRef.value?.resetValidation();
}

const onValidateForm = () => {
  onResetForm();
  emailRef.value?.validate();
  passwordRef.value?.validate();
  if (!(emailRef.value?.hasError || passwordRef.value?.hasError)) {
    onSubmitForm();
  }
}

const onSubmitForm = () => {
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  api.user
    .signIn(profile.email, profile.password)
    .then((successResponse) => {
      LocalStorage.set("email", profile.email);
      state.loading = false;
      emit("success", successResponse.data);
    })
    .catch((errorResponse) => {
      state.apiError = errorResponse.customAPIErrorDetails;
      switch (errorResponse.response.status) {
        case 400:
          if (
            errorResponse.response.data.invalidOrMissingParams.find(function (e) {
              return e === "email";
            })
          ) {
            state.loadingError = true;
            state.errorMessage = "API Error: missing email param";
            nextTick(() => {
              emailRef.value?.focus();
            });
          } else if (
            errorResponse.response.data.invalidOrMissingParams.find(function (e) {
              return e === "password";
            })
          ) {
            state.loadingError = true;
            state.errorMessage = "API Error: missing password param";
            nextTick(() => {
              passwordRef.value?.focus();
            });
          } else {
            state.loadingError = true;
            state.errorMessage = "API Error: invalid/missing param";
            nextTick(() => {
              emailRef.value?.focus();
            });
          }
          break;
        case 404:
          validator.email.hasErrors = true;
          validator.email.message = "Email not registered";
          nextTick(() => {
            emailRef.value?.focus();
          });
          break;
        case 401:
          validator.password.hasErrors = true;
          validator.password.message = "Invalid password";
          nextTick(() => {
            passwordRef.value?.focus();
          });
          break;
        default:
          state.loadingError = true;
          state.errorMessage = "API Error: fatal error";
          break;
      }
      state.loading = false;
    });
}

</script>