<template>
  <q-btn v-bind="attrs" :icon="currentDarkModeIcon" @click="onToggleDarkMode">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <slot></slot>
  </q-btn>
</template>

<script setup>

import { useAttrs, computed } from "vue";
import { Dark } from "quasar";
import { useI18n } from "vue-i18n";

import { useLocalStorage } from 'src/composables/useLocalStorage';
import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();
const attrs = useAttrs();

const { darkMode } = useLocalStorage();

const currentDarkModeIcon = computed(() => {
  return (Dark.isActive ? "dark_mode" : "light_mode");
});

const toolTipLight = computed(() => t("Switch to light mode"));
const toolTipDark = computed(() => t("Switch to dark mode"));

const tooltip = computed(() => Dark.isActive ? toolTipLight : toolTipDark);

const onToggleDarkMode = () => {
  Dark.toggle();
  darkMode.set(Dark.isActive);
}

</script>
