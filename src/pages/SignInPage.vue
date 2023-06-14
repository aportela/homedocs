<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card class="q-pa-md shadow-2 my_card" bordered>
      <q-card-section class="text-center">
        <h3>Homedocs</h3>
        <h5>I ASSURE YOU; WE'RE OPEN</h5>
        <div class="text-grey-9 text-h5 text-weight-bold">Sign in</div>
        <div class="text-grey-8">Sign in below to access your account</div>
      </q-card-section>
      <q-card-section>
        <q-input dense outlined v-model="email" label="Email Address"></q-input>
        <q-input
          dense
          outlined
          class="q-mt-md"
          v-model="password"
          type="password"
          label="Password"
        ></q-input>
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
        ></q-btn>
      </q-card-section>
      <q-card-section class="text-center q-pt-none">
        <div class="text-grey-8">
          Don't have an account yet?
          <a
            href="#"
            class="text-dark text-weight-bold"
            style="text-decoration: none"
            >Sign up.</a
          >
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import { ref, defineComponent, getCurrentInstance } from "vue";

export default defineComponent({
  name: "SignInPage",
  setup() {
    const app = getCurrentInstance();

    function onSubmit() {
      app.appContext.config.globalProperties.$api.user
        .signIn("foo", "bar")
        .then((success) => {
          console.log(success);
        })
        .catch((error) => {
          console.log(error);
        });
    }
    return {
      email: ref(""),
      password: ref(""),
      onSubmit,
    };
  },
});
</script>
