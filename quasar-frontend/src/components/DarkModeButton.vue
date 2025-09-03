<template>
  <q-btn v-bind="attrs" :icon="currentDarkModeIcon" @click="toggleDarkMode">
    <q-tooltip>Switch light/dark mode</q-tooltip>
    <slot></slot>
  </q-btn>
</template>

<script setup>

import { useAttrs, computed, watch } from "vue";
import { Dark, LocalStorage } from "quasar";

const attrs = useAttrs();

const currentDarkModeIcon = computed(() => {
  return (Dark.isActive ? "dark_mode" : "light_mode");
});

watch(
  () => Dark.isActive,
  val => LocalStorage.set('darkMode', val)
)

function toggleDarkMode() {
  Dark.toggle();
}

</script>