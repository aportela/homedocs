<template>
  <q-layout class="q-layout-themed-signin-signup">
    <div class="row" style="height: 100vh">
      <div class="col-12 col-lg-8 col-md-12 col-sm-12 slogan-image-cover flex flex-center">
        <div class="q-pa-xl text-center text-white">
          <h2 class="text-h2 text-weight-bolder q-mb-md">{{ t("Welcome to HomeDocs") }}</h2>
          <h4 class="text-grey-4 text-h4">{{ t("Where your documents find their place.") }}</h4>
        </div>
      </div>
      <div class="col-12 col-lg-4 justify-center q-pa-xl layout-signin-signup">
        <q-page-container>
          <router-view />
        </q-page-container>
      </div>
    </div>
  </q-layout>
</template>

<script setup>
import { useI18n } from "vue-i18n";

const { t } = useI18n();
</script>

<style scoped>
.slogan-image-cover {
  background-image: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%), url('https://images.pexels.com/photos/3184460/pexels-photo-3184460.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1');
  background-size: cover;
  background-position: center;
  filter: grayscale(100%);
}
</style>

<style>
.body--light {
  .q-layout-themed-signin-signup {
    background-color: var(--color-zinc-100);
  }

}

.body--dark {
  .q-layout-themed-signin-signup {
    background-color: var(--color-zinc-800);
  }

}
</style>
