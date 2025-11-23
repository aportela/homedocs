<template>
  <q-btn v-bind="attrs" :icon="currentDarkModeIcon" @click="toggle">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <slot></slot>
  </q-btn>
</template>

<script setup lang="ts">

import { useAttrs, computed } from "vue";
import { useI18n } from "vue-i18n";

//import { useDarkMode } from 'src/composables/useDarkMode';
import { useDarkModeStore } from "src/stores/darkMode";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();
const attrs = useAttrs();

const { toggle, isActive } = useDarkModeStore();

const currentDarkModeIcon = computed(() => {
  return (isActive ? "dark_mode" : "light_mode");
});

const toolTipLight = computed(() => t("Switch to light mode"));
const toolTipDark = computed(() => t("Switch to dark mode"));

const tooltip = computed(() => isActive ? toolTipLight : toolTipDark);

</script>