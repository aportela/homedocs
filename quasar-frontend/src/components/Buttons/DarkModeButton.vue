<template>
  <q-btn v-bind="attrs" :icon="currentDarkModeIcon" @click="darkModeStore.toggle">
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <slot></slot>
  </q-btn>
</template>

<script setup lang="ts">
  import { useAttrs, computed } from "vue";
  import { useI18n } from "vue-i18n";

  import { useDarkModeStore } from "@/stores/darkMode";

  import { default as DesktopToolTip } from "@/components/DesktopToolTip.vue";

  const { t } = useI18n();
  const attrs = useAttrs();

  const darkModeStore = useDarkModeStore();

  const currentDarkModeIcon = computed(() => {
    return (darkModeStore.isDarkModeActive ? "dark_mode" : "light_mode");
  });

  const toolTipLight = computed(() => t("Switch to light mode"));
  const toolTipDark = computed(() => t("Switch to dark mode"));

  const tooltip = computed(() => darkModeStore.isDarkModeActive ? toolTipLight : toolTipDark);
</script>