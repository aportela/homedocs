<template>
  <q-page>
    <q-card class="q-pa-xl" flat>
      <RegisterForm @success="onSuccessRegister"></RegisterForm>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { default as RegisterForm } from "src/components/Forms/RegisterForm.vue";

const onSuccessRegister = (apiResponseData) => {
};

</script>
