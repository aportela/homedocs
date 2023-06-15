<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <form
        @submit.prevent.stop="onSubmit"
        @reset.prevent.stop="onReset"
        autocorrect="off"
        autocapitalize="off"
        autocomplete="off"
        spellcheck="false"
      >
        <q-card-section class="text-center">
          <h3>Homedocs</h3>
          <h5>I ASSURE YOU; WE'RE OPEN</h5>
          <div class="text-grey-9 text-h5 text-weight-bold">Sign in</div>
          <div class="text-grey-8">Sign in below to access your account</div>
        </q-card-section>
        <q-card-section>
          <q-input
            dense
            outlined
            ref="emailRef"
            v-model="email"
            type="email"
            name="email"
            label="Email Address"
            :rules="emailRules"
          >
            <template v-slot:prepend>
              <q-icon name="mail" />
            </template>
          </q-input>
          <q-input
            dense
            outlined
            class="q-mt-md"
            ref="passwordRef"
            v-model="password"
            name="password"
            type="password"
            label="Password"
            :rules="passwordRules"
          >
            <template v-slot:prepend>
              <q-icon name="key" />
            </template>
          </q-input>
        </q-card-section>
        <q-card-section>
          <q-btn
            style="border-radius: 8px"
            color="dark"
            rounded
            size="md"
            label="Sign in"
            no-caps
            class="full-width"
            @click="onSubmit"
            type="submit"
            :rules="passwordRules"
          ></q-btn>
        </q-card-section>
        <q-card-section class="text-center q-pt-none">
          <div class="text-grey-8">
            Don't have an account yet?
            <router-link :to="{ name: 'signUp' }">
              <span
                class="text-dark text-weight-bold"
                style="text-decoration: none"
                >Sign up.</span
              >
            </router-link>
          </div>
        </q-card-section>
      </form>
    </q-card>
  </q-page>
</template>

<script>
import { ref, defineComponent, getCurrentInstance } from "vue";
import { useQuasar } from "quasar";
import { useRouter } from "vue-router";
import { useSessionStore } from "stores/session";

export default defineComponent({
  name: "SignInPage",
  setup() {
    const $q = useQuasar();

    const router = useRouter();
    const app = getCurrentInstance();

    const email = ref(null);
    const emailRef = ref(null);
    const passwordRef = ref(null);
    const password = ref(null);

    return {
      email,
      emailRef,
      password,
      passwordRef,
      emailRules: [[(val) => !!val || "Field is required"]],
      passwordRules: [[(val) => !!val || "Field is required"]],

      onSubmit() {
        emailRef.value.validate();
        passwordRef.value.validate();
        if (!(emailRef.value.hasError || passwordRef.value.hasError)) {
          app.appContext.config.globalProperties.$api.user
            .signIn(email, password)
            .then((success) => {
              $q.notify({
                color: "positive",
                icon: "announcement",
                message: "Ok",
              });
              let session = useSessionStore();
              session.signIn("jwttest");
              router.push({
                name: "index",
              });
            })
            .catch((error) => {
              $q.notify({
                color: "negative",
                icon: "announcement",
                message: "API Error",
              });
            });
        } else {
          $q.notify({
            color: "negative",
            icon: "announcement",
            message: "Validation error",
          });
        }
      },
      onReset() {
        emailRef.value.resetValidation();
        passwordRef.value.resetValidation();
      },
    };
  },
});
</script>
