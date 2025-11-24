<template>
  <q-btn v-bind="attrs" :label="shortLabels ? selectedLocale.shortLabel : selectedLocale.label" icon="language"
    icon-right="unfold_more" no-caps dense>
    <DesktopToolTip>{{ tooltip }}</DesktopToolTip>
    <q-menu fit anchor="top left" self="bottom left">
      <q-item dense clickable v-close-popup v-for="availableLanguage in availableLocales" :key="availableLanguage.value"
        @click="onSelectLocale(availableLanguage.value)">
        <q-item-section>{{ availableLanguage.label }}</q-item-section>
        <q-item-section avatar v-if="availableLanguage.value === selectedLocale.value">
          <q-icon name="check" />
        </q-item-section>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup lang="ts">
import { computed, watch, useAttrs, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useLocalStorage } from "src/composables/useLocalStorage";
import { usei18n } from "src/composables/usei18n";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

const { t } = useI18n();

const { i18n } = usei18n();

const { locale: localStorageLocale } = useLocalStorage();

const attrs = useAttrs();

interface SwitchLanguageButtonProps {
  shortLabels?: boolean
};
withDefaults(defineProps<SwitchLanguageButtonProps>(), {
  shortLabels: false
});

const tooltip = computed(() => t("Switch language"));

const availableLocales = [
  { shortLabel: "EN", label: "English", value: "en-US" },
  { shortLabel: "ES", label: "Español", value: "es-ES" },
  { shortLabel: "GL", label: "Galego", value: "gl-GL" },
];

const selectedLocale = ref(availableLocales[0]!);

watch(
  () => i18n.global.locale.value,
  (newLocale) => {
    selectedLocale.value = availableLocales.find(l => l.value === newLocale) ?? availableLocales[0]!;
    localStorageLocale.set(newLocale);
  },
  { immediate: true }
);

type Locale = "en-US" | "es-ES" | "gl-GL";

function onSelectLocale(newLocale: Locale) {
  i18n.global.locale.value = newLocale;
  localStorageLocale.set(newLocale);
}

</script>