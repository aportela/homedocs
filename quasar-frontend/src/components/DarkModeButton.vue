<template>
  <q-btn :ripple="false" flat :icon="iconDarkMode" @click="toggleDarkMode">
    <q-tooltip class="bg-dark-2">Switch light/dark mode</q-tooltip>
  </q-btn>
</template>

<script setup>

import { computed, watch } from "vue";
import { LocalStorage, useQuasar } from "quasar";

const $q = useQuasar();

const iconDarkMode = computed(() => {
  return ($q.dark.isActive ? "dark_mode" : "light_mode");
});

watch(
  () => $q.dark.isActive,
  val => LocalStorage.set('darkMode', val)
)

function toggleDarkMode() {
  $q.dark.toggle();
}

</script>