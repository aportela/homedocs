<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <form @submit.prevent.stop="onSubmit" @reset.prevent.stop="onReset" autocorrect="off" autocapitalize="off"
        autocomplete="off" spellcheck="false">
        <q-card-section class="text-center">
          <h3>Homedocs</h3>
          <h5>I ASSURE YOU; WE'RE OPEN</h5>
          <div class="text-grey-9 text-h5 text-weight-bold">Sign in</div>
          <div class="text-grey-8">Sign in below to access your account</div>
        </q-card-section>
        <q-card-section>
          <q-input dense outlined ref="emailRef" v-model="email" type="email" name="email" label="Email Address"
            :disable="loading" :autofocus="true" :rules="emailRules">
            <template v-slot:prepend>
              <q-icon name="alternate_email" />
            </template>
          </q-input>
          <q-input dense outlined class="q-mt-md" ref="passwordRef" v-model="password" name="password" type="password"
            label="Password" :disable="loading" :rules="passwordRules">
            <template v-slot:prepend>
              <q-icon name="key" />
            </template>
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-btn color="dark" size="md" label="Sign in" no-caps class="full-width" icon="account_circle"
            :disable="loading || (!(email && password))" :loading="loading" @click="onSubmit" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              Loading...
            </template>
          </q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none">
          <div class="text-grey-8">
            Don't have an account yet?
            <router-link :to="{ name: 'signUp' }">
              <span class="text-dark text-weight-bold" style="text-decoration: none">Sign up.</span>
            </router-link>
          </div>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script setup>

import { ref, getCurrentInstance } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useSessionStore } from "stores/session";

const $q = useQuasar();

const router = useRouter();
const app = getCurrentInstance();

const loading = ref(false);

const email = ref("");
const emailRef = ref(null);
const password = ref("");
const passwordRef = ref(null);

const emailRules = [val => !!val || 'Field is required'];
const passwordRules = [val => !!val || 'Field is required'];

function onSubmit() {
  emailRef.value.validate();
  passwordRef.value.validate();
  if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
    loading.value = true;
    app.appContext.config.globalProperties.$api.user
      .signIn(email.value, password.value)
      .then((success) => {
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
        $q.notify({
          color: "negative",
          icon: "announcement",
          message: "API Error",
        });
        loading.value = false;
      });
  } else {
    $q.notify({
      color: "negative",
      icon: "announcement",
      message: "Validation error",
    });
  }
}

function onReset() {
  emailRef.value.resetValidation();
  passwordRef.value.resetValidation();
}




</script>
