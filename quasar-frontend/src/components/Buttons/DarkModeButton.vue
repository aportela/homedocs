<template>
  <q-btn v-bind="attrs" :icon="currentDarkModeIcon" @click="onToggleDarkMode">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <slot></slot>
  </q-btn>
</template>

<script setup>

import { useAttrs, computed, watch } from "vue";
import { Dark, LocalStorage } from "quasar";
import { useI18n } from "vue-i18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();
const attrs = useAttrs();

const currentDarkModeIcon = computed(() => {
  return (Dark.isActive ? "dark_mode" : "light_mode");
});

const toolTipLight = computed(() => t("Switch to light mode"));
const toolTipDark = computed(() => t("Switch to dark mode"));

const tooltip = computed(() => Dark.isActive ? toolTipLight : toolTipDark);

watch(
  () => Dark.isActive,
  val => LocalStorage.set('darkMode', val)
)

const onToggleDarkMode = () => {
  Dark.toggle();
}

</script>
