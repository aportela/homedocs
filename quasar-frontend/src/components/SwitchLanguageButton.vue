<template>
  <q-btn v-bind="attrs" flat :label="shortLabels ? selectedLocale.shortLabel : selectedLocale.label" icon="language"
    icon-right="unfold_more" no-caps>
    <q-tooltip>Switch language</q-tooltip>
    <q-menu fit anchor="top left" self="bottom left">
      <q-item dense clickable v-close-popup v-for="availableLanguage in availableLocales" :key="availableLanguage.value"
        @click="onSelectLocale(availableLanguage, true)">
        <q-item-section>{{ availableLanguage.label }}</q-item-section>
        <q-item-section avatar v-if="availableLanguage.value == selectedLocale.value">
          <q-icon name="check" />
        </q-item-section>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup>

import { useAttrs, ref } from "vue";
import { i18n, defaultLocale } from "src/boot/i18n";
import { useSessionStore } from "stores/session";

const attrs = useAttrs();

const props = defineProps(['shortLabels']);

const availableLocales = [
  {
    shortLabel: 'EN',
    label: 'English',
    value: 'en-US'
  },
  {
    shortLabel: 'ES',
    label: 'Español',
    value: 'es-ES'
  },
  {
    shortLabel: 'GL',
    label: 'Galego',
    value: 'gl-GL'
  }
];

const defaultBrowserLocale = availableLocales.find((lang) => lang.value == defaultLocale);
const selectedLocale = ref(defaultBrowserLocale || availableLocales[0]);

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

function onSelectLocale(locale, save) {
  selectedLocale.value = locale;
  i18n.global.locale.value = locale.value;
  if (save) {
    session.saveLocale(locale.value);
  }
}

</script>