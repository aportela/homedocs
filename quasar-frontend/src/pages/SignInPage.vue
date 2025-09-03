<template>
  <q-page class="flex flex-center _bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section class="text-center">
          <q-avatar square size="128px">
            <img src="icons/favicon-128x128.png" />
          </q-avatar>
          <h3 class="q-mt-sm q-mb-md">{{ $t('Homedocs') }}</h3>
          <div>{{ $t('Sign in below to access your account') }}</div>
        </q-card-section>
        <q-card-section>
          <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
            :disable="loading" :autofocus="true" :rules="requiredFieldRules" lazy-rules
            :error="remoteValidation.email.hasErrors" :errorMessage="remoteValidation.email.message">
            <template v-slot:prepend>
              <q-icon name="alternate_email" />
            </template>
          </q-input>
          <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
            :label="t('Password')" :disable="loading" :rules="requiredFieldRules" lazy-rules
            :error="remoteValidation.password.hasErrors" :errorMessage="remoteValidation.password.message">
            <template v-slot:prepend>
              <q-icon name="key" />
            </template>
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-btn color="dark" size="md" :label="$t('Sign in')" no-caps class="full-width" icon="account_circle"
            :disable="loading || (!(email && password))" :loading="loading" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Loading...") }}
            </template>
          </q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none" v-if="signUpAllowed">
          <div>
            {{ t("Don't have an account yet ?") }}
            <router-link :to="{ name: 'signUp' }">
              <span class="text-weight-bold" style="text-decoration: none">{{
                t("Click here to sign up") }}</span>
            </router-link>
          </div>
        </q-card-section>
        <hr color="grey-5" />
        <q-card-section class="text-center q-pt-none">
          <q-btn-group flat square>
            <SwitchLanguageButton />
            <DarkModeButton />
            <GitHubButton label="@2025 HomeDocs" :href="GITHUB_PROJECT_URL" />
          </q-btn-group>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, nextTick, computed } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'
//import { useSessionStore } from "stores/session";
import { useInitialStateStore } from "stores/initialState";

import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"
const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

/*
const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}
*/

const initialState = useInitialStateStore();

const signUpAllowed = computed(() => initialState.isSignUpAllowed);

const loading = ref(false);

const remoteValidation = ref({
  email: {
    hasErrors: false,
    message: null
  },
  password: {
    hasErrors: false,
    message: null
  }
});

// TODO: not refreshing on requiredFieldRules
const fieldIsRequiredLabel = computed(() => t('Field is required'));

const requiredFieldRules = [
  val => !!val || t('Field is required')
];

const email = ref(null);
const emailRef = ref(null);

const password = ref(null);
const passwordRef = ref(null);

function onResetForm() {
  remoteValidation.value.email.hasErrors = false;
  remoteValidation.value.email.message = null;
  remoteValidation.value.password.hasErrors = false;
  remoteValidation.value.password.message = null;
  emailRef.value.resetValidation();
  passwordRef.value.resetValidation();
}

function onValidateForm() {
  onResetForm();
  emailRef.value.validate();
  passwordRef.value.validate();
  nextTick(() => {
    if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
      onSubmitForm();
    }
  });
}

function onSubmitForm() {
  loading.value = true;
  api.user
    .signIn(email.value, password.value)
    .then((success) => {
      // TODO
      //session.signIn();
      router.push({
        name: "index",
      });
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      switch (error.response.status) {
        case 400:
          if (
            error.response.data.invalidOrMissingParams.find(function (e) {
              return e === "email";
            })
          ) {
            $q.notify({
              type: "negative",
              message: t("API Error: missing email param"),
            });
            emailRef.value.focus();
          } else if (
            error.response.data.invalidOrMissingParams.find(function (e) {
              return e === "password";
            })
          ) {
            $q.notify({
              type: "negative",
              message: t("API Error: missing password param"),
            });
            passwordRef.value.focus();
          } else {
            $q.notify({
              type: "negative",
              message: t("API Error: invalid/missing param"),
            });
          }
          break;
        case 404:
          remoteValidation.value.email.hasErrors = true;
          remoteValidation.value.email.message = t("Email not registered");
          nextTick(() => {
            emailRef.value.focus();
          });
          break;
        case 401:
          remoteValidation.value.password.hasErrors = true;
          remoteValidation.value.password.message = t("Invalid password");
          nextTick(() => {
            passwordRef.value.focus();
          });
          break;
        default:
          $q.notify({
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
    });
}

</script>