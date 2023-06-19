<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
        spellcheck="false">
        <q-card-section class="text-center">
          <h3>Homedocs</h3>
          <h5>I ASSURE YOU; WE'RE OPEN</h5>
          <div class="text-grey-9 text-h5 text-weight-bold">Sign in</div>
          <div class="text-grey-8">Sign in below to access your account</div>
        </q-card-section>
        <q-card-section>
          <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email"
            :label="t('pages.signIn.labels.emailField')" :disable="loading" :autofocus="true" :rules="emailRules">
            <template v-slot:prepend>
              <q-icon name="alternate_email" />
            </template>
          </q-input>
          <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
            :label="t('pages.signIn.labels.passwordField')" :disable="loading" :rules="passwordRules">
            <template v-slot:prepend>
              <q-icon name="key" />
            </template>
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-btn color="dark" size="md" :label="t('pages.signIn.labels.submitButton')" no-caps class="full-width"
            icon="account_circle" :disable="loading || (!(email && password))" :loading="loading" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              Loading...
            </template>
          </q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none">
          <div class="text-grey-8">
            {{ t("pages.signIn.labels.doNotHaveAccount") }}
            <router-link :to="{ name: 'signUp' }">
              <span class="text-dark text-weight-bold" style="text-decoration: none">{{
                t("pages.signIn.labels.createAnAccount") }}</span>
            </router-link>
          </div>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, getCurrentInstance, computed } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'
import { api } from 'boot/axios'
import { useSessionStore } from "stores/session";

const { t } = useI18n();

const $q = useQuasar();

const router = useRouter();

const loading = ref(false);

const remoteErrors = ref({
  emailNotFound: false,
  invalidPassword: false
});

const form = ref(null)

const email = ref("");
const emailRef = ref(null);
const emailRules = [
  val => !!val || 'Field is required',
  val => !invalidRemoteEmail.value || t("pages.signIn.errorMessages.emailNotRegistered")
];
const invalidRemoteEmail = computed(() => remoteErrors.value.emailNotFound);

const password = ref("");
const passwordRef = ref(null);
const passwordRules = [
  val => !!val || 'Field is required',
  val => !invalidRemotePassword.value || t("pages.signIn.errorMessages.incorrectPassword")
];
const invalidRemotePassword = computed(() => remoteErrors.value.invalidPassword);


function onSubmitForm() {
  emailRef.value.validate();
  passwordRef.value.validate();
  if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
    loading.value = true;
    api.user
      .signIn(email.value, password.value)
      .then((success) => {
        console.log(2);
        $q.notify({
          color: "positive",
          icon: "announcement",
          message: "Ok",
        });
        loading.value = false;
        /*
        let session = useSessionStore();
        session.signIn("jwttest");
        router.push({
          name: "index",
        });
        */
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
                message: t("pages.signIn.errorMessages.APIMissingEmailParam"),
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
                message: t("pages.signIn.errorMessages.APIMissingPasswordParam"),
              });
              passwordRef.value.focus();
            } else {
              $q.notify({
                color: "negative",
                icon: "error",
                message: t("pages.signIn.errorMessages.APIMissingParam"),
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
              message: t("pages.signIn.errorMessages.APIFatalError"),
            });
            break;
        }
        loading.value = false;
      });
  }
}

</script>
