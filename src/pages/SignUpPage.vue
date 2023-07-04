<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section class="text-center">
          <h3>{{ $t('Homedocs') }}</h3>
          <h5>{{ $t('"I ASSURE YOU; WE\'RE OPEN"') }}</h5>
          <div class="text-grey-9 text-h5 text-weight-bold">{{ $t('Sign up') }}</div>
          <div class="text-grey-8">{{ $t('Sign up below to create your account') }}</div>
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
          <q-btn color="dark" size="md" :label="$t('Sign up')" no-caps class="full-width" icon="account_circle"
            :disable="loading || (!(email && password))" :loading="loading" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t('Loading...') }}
            </template>
          </q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none">
          <div class="text-grey-8">
            {{ t('Already have an account ?') }}
            <router-link :to="{ name: 'signIn' }">
              <span class="text-dark text-weight-bold" style="text-decoration: none">{{
                t("Click here to sign in") }}</span>
            </router-link>
          </div>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, computed } from "vue";
import { uid, useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'

const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

const loading = ref(false);

const remoteErrors = ref({
  emailAlreadyUsed: false,
  invalidPassword: false
});

const email = ref(null);
const emailRef = ref(null);
const invalidRemoteEmail = computed(() => remoteErrors.value.emailAlreadyUsed);
const emailRules = [
  val => !!val || t('Field is required'),
  val => !invalidRemoteEmail.value || t("Email already used")
];

const password = ref(null);
const passwordRef = ref(null);
const invalidRemotePassword = computed(() => remoteErrors.value.invalidPassword);

const passwordRules = [
  val => !!val || t('Field is required')
];

function onResetForm() {
  remoteErrors.value.emailAlreadyUsed = false;
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
      .signUp(uid(), email.value, password.value)
      .then((success) => {
        $q.notify({
          type: "positive",
          message: t("Your account has been created"),
          actions: [
            { label: t("Sign in"), color: 'white', handler: () => { /* ... */ } }
          ]
        });
        loading.value = false;
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
            remoteErrors.value.emailAlreadyUsed = true;
            emailRef.value.validate();
            break;
          default:
            $q.notify({
              type: "negative",
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
