<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <SignInForm @success="onSuccessSignIn"></SignInForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { useRouter } from "vue-router";
import { default as SignInForm } from "src/components/Forms/SignInForm.vue";

const router = useRouter();

const onSuccessSignIn = (apiResponseData) => {
  router.push({
    name: "index",
  });
};

</script>