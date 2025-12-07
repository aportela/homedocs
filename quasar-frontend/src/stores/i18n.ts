import { defineStore, acceptHMRUpdate } from 'pinia';
import { Lang } from "quasar";
import { default as messages } from "src/i18n";
import { createStorageEntry } from 'src/composables/localStorage';
import { DEFAULT_LOCALE } from 'src/constants';
// @ts-expect-error: missing TypeScript type definitions
import { default as enUS_Quasar } from "../../node_modules/quasar/lang/en-US";
// @ts-expect-error: missing TypeScript type definitions
import { default as esES_Quasar } from "../../node_modules/quasar/lang/es";

const localStorageLocale = createStorageEntry<string | null>("locale", null);

const availableSystemLocales: string[] = Object.keys(messages);

const quasarLanguages = {
  "en-US": enUS_Quasar,
  "es-ES": esES_Quasar,
  "gl-GL": esES_Quasar,
};

const getMatchedLocale = (locale: string): string | null => {
  if (availableSystemLocales.includes(locale)) {
    return locale;
  } else if (locale.length >= 2) {
    // try similar match, example: locale es-MX (spanish, mexico) return es-ES (spanish, spain) if availableSystemLocales only contains [ 'en-US', 'es-ES', 'gl-GL']
    const shortLocale = locale.substring(0, 2).toLocaleLowerCase();
    const match = availableSystemLocales.find((locale) => locale.substring(0, 2).toLocaleLowerCase() === shortLocale);
    return (match ?? null);
  } else {
    return null;
  }
};

const autodetectLocale = (): string => {
  return getMatchedLocale(localStorageLocale.get() || Lang.getLocale() || "") ?? DEFAULT_LOCALE;
};

interface State {
  availableSystemLocales: string[];
  locale: string;
};

export const setQuasarLanguage = (locale: string) => {
  console.log("cambiando a", locale);
  switch (locale) {
    case "en-US":
      Lang.set(quasarLanguages["en-US"]);
      break;
    case "es-ES":
      console.log(1);
      Lang.set(quasarLanguages["es-ES"]);
      console.log(2);
      break;
    case "gl-GL":
      Lang.set(quasarLanguages["gl-GL"]);
      break;
    default:
      console.error("Invalid locale for quasar language:", locale);
      Lang.set(quasarLanguages["en-US"]);
      break;
  }
};

export const useI18nStore = defineStore('i18nStore', {
  state: (): State => ({
    availableSystemLocales: Object.keys(messages),
    locale: autodetectLocale(),
  }),
  getters: {
    currentLocale(state): string {
      return state.locale;
    },
    currentAvailableSystemLocales(state): string[] {
      return (state.availableSystemLocales);
    },
  },
  actions: {
    setLocale(locale: string): boolean {
      const matched = getMatchedLocale(locale);
      if (matched !== null) {
        this.locale = matched;
        localStorageLocale.set(this.locale);
        return (true);
      } else {
        return (false);
      }
    },
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useI18nStore, import.meta.hot));
}
