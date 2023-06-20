<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section class="text-center">
          <h3>{{ $t('Homedocs') }}</h3>
          <h5>{{ $t('"I ASSURE YOU; WE\'RE OPEN"') }}</h5>
          <div class="text-grey-9 text-h5 text-weight-bold">{{ $t('Sign in') }}</div>
          <div class="text-grey-8">{{ $t('Sign in below to access your account') }}</div>
        </q-card-section>
        <q-card-section>
          <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
            :disable="loading" :autofocus="true" :rules="emailRules" lazy-rules>
            <template v-slot:prepend>
              <q-icon name="alternate_email" />
            </template>
          </q-input>
          <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
            :label="t('Password')" :disable="loading" :rules="passwordRules" lazy-rules>
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
              {{ t('Loading...') }}
            </template>
          </q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none">
          <div class="text-grey-8">
            {{ t('Don\'t have an account yet ?') }}
            <router-link :to="{ name: 'signUp' }">
              <span class="text-dark text-weight-bold" style="text-decoration: none">{{
                t("Click here to sign up") }}</span>
            </router-link>
          </div>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, computed } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'

const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

const loading = ref(false);

const remoteErrors = ref({
  emailNotFound: false,
  invalidPassword: false
});

const email = ref(null);
const emailRef = ref(null);
const invalidRemoteEmail = computed(() => remoteErrors.value.emailNotFound);
const emailRules = [
  val => !!val || t('Field is required'),
  val => !invalidRemoteEmail.value || t("Email not registered")
];

const password = ref(null);
const passwordRef = ref(null);
const invalidRemotePassword = computed(() => remoteErrors.value.invalidPassword);

const passwordRules = [
  val => !!val || t('Field is required'),
  val => !invalidRemotePassword.value || t("Invalid password")
];

function onResetForm() {
  remoteErrors.value.emailNotFound = false;
  remoteErrors.value.invalidPassword = false;
  emailRef.value.resetValidation();
  passwordRef.value.resetValidation();
}

function onValidateForm() {
  emailRef.value.validate();
  passwordRef.value.validate();
}

function onSubmitForm() {
  onResetForm();
  onValidateForm();
  if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
    loading.value = true;
    api.user
      .signIn(email.value, password.value)
      .then((success) => {
        router.push({
          name: "index",
        });
      })
      .catch((error) => {
        switch (error.response.status) {
          case 400:
            if (
              error.response.data.invalidOrMissingParams.find(function (e) {
                return e === "email";
              })
            ) {
              $q.notify({
                color: "negative",
                icon: "error",
                message: t("API Error: missing email param"),
              });
              emailRef.value.focus();
            } else if (
              error.response.data.invalidOrMissingParams.find(function (e) {
                return e === "password";
              })
            ) {
              $q.notify({
                color: "negative",
                icon: "error",
                message: t("API Error: missing password param"),
              });
              passwordRef.value.focus();
            } else {
              $q.notify({
                color: "negative",
                icon: "error",
                message: t("API Error: missing param"),
              });
            }
            break;
          case 404:
            remoteErrors.value.emailNotFound = true;
            emailRef.value.validate();
            break;
          case 401:
            remoteErrors.value.invalidPassword = true;
            passwordRef.value.validate();
            break;
          default:
            $q.notify({
              color: "negative",
              icon: "warning",
              message: t("API Error: fatal error"),
            });
            break;
        }
        loading.value = false;
      });
  } else {
  }
}

</script>
