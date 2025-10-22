<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <LoginForm @success="onSuccessLogin">
      </LoginForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { useRouter } from "vue-router";
import { default as LoginForm } from "src/components/Forms/LoginForm.vue";

const router = useRouter();

const onSuccessLogin = (apiResponseData) => {
  router.push({
    name: "index",
  });
};

</script>