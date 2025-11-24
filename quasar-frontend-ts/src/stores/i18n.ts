import { defineStore, acceptHMRUpdate } from 'pinia';
import { Lang } from "quasar";
import { useLocalStorage } from 'src/composables/useLocalStorage';

import messages from "src/i18n";
import { useI18n } from "vue-i18n";

import { DEFAULT_LOCALE } from 'src/constants';

const { locale: i18nInstanceCurrentLocale } = useI18n();

const { locale: localStorageLocale } = useLocalStorage();
/*

const languageLocaleMappings = availableLocales.reduce((acc, locale) => {
  if (locale) {
    const languageCode = locale.split('-')[0].toLowerCase();
    acc[languageCode] = locale;
  }
  return acc;
}, {} as Record<string, string>);


const browserLocale = Lang.getLocale() ?? "en";
const key = browserLocale.substring(0, 2) as keyof typeof languageLocaleMappings;
const normalizedBrowsedLocale = languageLocaleMappings[key] || browserLocale;

const savedLocale = localStorageLocale.get();

const currentLocale = savedLocale || normalizedBrowsedLocale;

const locale = availableLocales.includes(currentLocale)
  ? currentLocale
  : "en-US";
*/

export const useI18nStore = defineStore('i18nStore', {
  state: () => ({
    availableSystemLocales: Object.keys(messages),
    savedLocale: localStorageLocale.get(),
    browserLocale: Lang.getLocale() ?? DEFAULT_LOCALE
  }),
  getters: {
  },
  actions: {
    setLocale(locale: string) {
      if (this.availableSystemLocales.includes(locale)) {
        this.savedLocale = locale;
      } else {
        // TODO: search similar
        this.savedLocale = DEFAULT_LOCALE;
      }
      i18nInstanceCurrentLocale.value = this.savedLocale;
      localStorageLocale.set(this.savedLocale);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useI18nStore, import.meta.hot));
}
