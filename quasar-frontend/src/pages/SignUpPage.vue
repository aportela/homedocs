<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <SignUpForm @success="onSuccessSignUp"></SignUpForm>
    </q-card>
  </q-page>
</template>

<script setup>
import { default as SignUpForm } from "src/components/Forms/SignUpForm.vue";

const onSuccessSignUp = (apiResponseData) => {
};

</script>