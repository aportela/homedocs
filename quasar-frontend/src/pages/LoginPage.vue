<template>
  <q-page>
    <q-card class="q-pa-xl homedocs-login-page-container" flat>
      <LoginForm @success="onSuccessLogin" />
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
  import { useRouter } from "vue-router";
  import { default as LoginForm } from "@/components/Forms/LoginForm.vue";

  const router = useRouter();

  const onSuccessLogin = () => {
    router.push(
      { name: "index" }
    ).catch((e) => {
      console.error(e);
    });;
  };

</script>

<style lang="css" scoped>
  .homedocs-login-page-container {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }
</style>