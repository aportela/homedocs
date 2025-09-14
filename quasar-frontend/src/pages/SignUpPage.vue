<template>
  <q-page>
    <q-card class="q-pa-md" flat>
      <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section class="text-center">
          <q-avatar square size="128px">
            <img src="icons/favicon-128x128.png" />
          </q-avatar>
          <h4 class="q-mt-sm q-mb-md text-h4 text-weight-bolder">{{ t("Sign up now and take control.") }}</h4>
          <div class="text-grey-6">{{ t("The first step to a more organized you starts here!") }}</div>
        </q-card-section>
        <q-card-section>
          <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
            :disable="loading" :autofocus="true" :rules="requiredFieldRules" lazy-rules
            :error="remoteValidation.email.hasErrors"
            :errorMessage="remoteValidation.email.message ? t(remoteValidation.email.message) : ''">
            <template v-slot:prepend>
              <q-icon name="alternate_email" />
            </template>
          </q-input>
          <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
            :label="t('Password')" :disable="loading" :rules="requiredFieldRules" lazy-rules
            :error="remoteValidation.password.hasErrors"
            :errorMessage="remoteValidation.password.message ? t(remoteValidation.password.message) : ''">
            <template v-slot:prepend>
              <q-icon name="key" />
            </template>
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-btn color="dark" size="md" :label="$t('Sign up')" no-caps class="full-width" icon="account_circle"
            :disable="loading || (!(email && password)) || !signUpAllowed" :loading="loading" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Loading...") }}
            </template>
          </q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none">
          <div>
            {{ t("Already have an account ?") }}
            <router-link :to="{ name: 'signIn' }" class="text-decoration-none">{{ t("Click here to sign in") }}
            </router-link>
          </div>
        </q-card-section>
        <q-card-section class="text-center q-pt-none" v-if="!signUpAllowed">
          <div class="text-bold">
            <q-icon name="info" />
            {{ t("New sign ups are not allowed on this system") }}
          </div>
        </q-card-section>
        <q-separator color="grey-5 q-mb-sm" />
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

import { ref, computed, nextTick } from "vue";
import { uid, useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'
import { useInitialStateStore } from "stores/initialState";

import { default as DarkModeButton } from "components/DarkModeButton.vue"
import { default as SwitchLanguageButton } from "components/SwitchLanguageButton.vue"
import { default as GitHubButton } from "components/GitHubButton.vue"
import { GITHUB_PROJECT_URL } from "src/constants"

const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

const loading = ref(false);


const initialState = useInitialStateStore();

const signUpAllowed = computed(() => initialState.isSignUpAllowed);

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

const fieldIsRequiredLabel = computed(() => t('Field is required'));

const requiredFieldRules = [
  val => !!val || fieldIsRequiredLabel.value
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
    .signUp(uid(), email.value, password.value)
    .then((success) => {
      $q.notify({
        type: "positive",
        message: t("Your account has been created"),
        actions: [
          {
            label: t("Sign in"), color: 'white', handler: () => {
              router.push({
                name: "signIn",
              });
            }
          }
        ]
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
        case 409:
          remoteValidation.value.email.hasErrors = true;
          remoteValidation.value.email.message = "Email already used";
          nextTick(() => {
            emailRef.value.focus();
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