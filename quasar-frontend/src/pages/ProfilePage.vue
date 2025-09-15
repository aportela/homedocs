<template>
  <q-page>
    <div class="slogan-image-cover flex flex-center q-mx-auto q-mt-lg" style="width: 90%; height: 300px;">
      <q-avatar icon="edit" size="48px" class="bg-dark text-white"
        style="position: absolute; top: 16px; right: 16px;" />
      <h2 class="text-h2 text-white text-weight-bolder q-my-none">My profile</h2>
    </div>
    <div class="text-center">
      <q-avatar icon="account_circle" size="200px" class="bg-grey-4 q-mx-auto"
        style="position: relative; top: -100px;" />
    </div>

    <div class="row" style="margin-top: -50px;">
      <div class="col-lg-4 col-xl-4 col-12">
        <q-expansion-item expand-separator header-class="bg-grey-4" icon="contact_mail"
          :label="t('Personal information')" cation="update profile" :model-value="profileFormExpanded"
          class="bg-grey-2 rounded-borders">
          <q-card class="q-mx-auto">
            <form @submit.prevent.stop="onValidateForm" autocorrect="off" autocapitalize="off" autocomplete="off"
              spellcheck="false">

              <q-card-section>
                <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" :label="t('Email')"
                  disable>
                  <template v-slot:prepend>
                    <q-icon name="alternate_email" />
                  </template>
                </q-input>
                <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password"
                  :type="visiblePassword ? 'text' : 'password'" :label="t('New password')" :disable="loading" autofocus
                  :error="remoteValidation.password.hasErrors"
                  :errorMessage="remoteValidation.password.message ? t(remoteValidation.password.message) : ''">
                  <template v-slot:prepend>
                    <q-icon name="key" />
                  </template>
                  <template v-slot:append>
                    <q-icon :name="visiblePassword ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                      @click="visiblePassword = !visiblePassword" />
                  </template>
                  <q-tooltip anchor="bottom right" self="top end">
                    {{
                      t(visiblePassword ? "Hide password" : "Show password")
                    }}</q-tooltip>
                </q-input>
              </q-card-section>
              <q-card-section>
                <q-btn color="primary" size="md" :label="$t('Update profile')" no-caps class="full-width"
                  icon="account_circle" :disable="loading || !password" :loading="loading" type="submit">
                  <template v-slot:loading>
                    <q-spinner-hourglass class="on-left" />
                    {{ t('Update profile') }}
                  </template>
                </q-btn>
              </q-card-section>
            </form>
          </q-card>
        </q-expansion-item>
      </div>
      <div class="col-lg-8 col-xl-8 col-12">
        <SystemStats></SystemStats>
      </div>
    </div>

  </q-page>
</template>

<script setup>
import { ref, computed, nextTick } from "vue";
import { useQuasar } from "quasar";
import { useI18n } from 'vue-i18n'
import { useInitialStateStore } from "stores/initialState";

import { api } from 'boot/axios'

import { default as SystemStats } from "src/components/SystemStats.vue";

const { t } = useI18n();

const $q = useQuasar();
const initialState = useInitialStateStore();

const loading = ref(false);

const profileFormExpanded = ref(true);

const email = computed(() => initialState.session.email);

const password = ref(null);
const emailRef = ref(null);
const passwordRef = ref(null);
const visiblePassword = ref(false);

const remoteValidation = ref({
  password: {
    hasErrors: false,
    message: null
  }
});

function onResetForm() {
  remoteValidation.value.password.hasErrors = false;
  remoteValidation.value.password.message = null;
  passwordRef.value.resetValidation();
}

function onValidateForm() {
  onResetForm();
  passwordRef.value.validate();
  nextTick(() => {
    if (!(passwordRef.value.hasError)) {
      onSubmitForm();
    }
  });
}

function onSubmitForm() {
  loading.value = true;
  api.user
    .updateProfile(email.value, password.value)
    .then((success) => {
      password.value = null;
      $q.notify({
        type: "positive",
        message: t("Profile has been successfully updated"),
      });

      loading.value = false;
      nextTick(() => {
        passwordRef.value.focus();
      });
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
          remoteValidation.value.email.message = "Email not registered";
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

<style scoped>
.slogan-image-cover {
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('https://images.pexels.com/photos/3184460/pexels-photo-3184460.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
  background-size: cover;
  background-position: bottom;
  filter: grayscale(100%);
  border-radius: 16px;

}
</style>