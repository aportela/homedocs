<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <RegisterForm @success="onSuccessSignUp"></RegisterForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { default as RegisterForm } from "src/components/Forms/RegisterForm.vue";

const onSuccessSignUp = (apiResponseData) => {
};

</script>
