<template>
  <q-btn v-bind="attrs" flat :label="shortLabels ? selectedLocale.shortLabel : selectedLocale.label" icon="language"
    icon-right="unfold_more" no-caps>
    <q-tooltip>{{ tooltip }}</q-tooltip>
    <q-menu fit anchor="top left" self="bottom left">
      <q-item dense clickable v-close-popup v-for="availableLanguage in availableLocales" :key="availableLanguage.value"
        @click="onSelectLocale(availableLanguage)">
        <q-item-section>{{ availableLanguage.label }}</q-item-section>
        <q-item-section avatar v-if="availableLanguage.value == selectedLocale.value">
          <q-icon name="check" />
        </q-item-section>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup>

import { useAttrs, ref, watch, computed } from "vue";
import { i18n, defaultLocale } from "src/boot/i18n";
import { LocalStorage } from "quasar";

import { useI18n } from 'vue-i18n'

const { t } = useI18n();

const attrs = useAttrs();

const props = defineProps(['shortLabels']);

const tooltip = computed(() => t('Switch language'));

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

watch(
  () => selectedLocale.value,
  val => LocalStorage.set('locale', val.value)
)

watch(
  () => i18n.global.locale.value,
  (newLocale) => {
    const locale = availableLocales.find((locale) => locale.value === newLocale);
    if (locale) {
      selectedLocale.value = locale;
      LocalStorage.set("locale", newLocale);
    }
  }
);

function onSelectLocale(locale) {
  i18n.global.locale.value = locale.value;
}

</script>