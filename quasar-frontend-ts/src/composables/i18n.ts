import { Lang } from "quasar";
import { default as messages } from "src/i18n";
import { locale as localStorageLocale } from "./localStorage";
import { DEFAULT_LOCALE } from 'src/constants';

const availableSystemLocales: string[] = Object.keys(messages);

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

export { availableSystemLocales, autodetectLocale, getMatchedLocale };
