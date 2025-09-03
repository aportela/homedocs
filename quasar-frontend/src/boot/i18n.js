import { createI18n } from "vue-i18n";
import messages from "src/i18n";
import { LocalStorage } from "quasar";

const availableLocales = [
  { shortLabel: "EN", label: "English", value: "en-US" },
  { shortLabel: "ES", label: "Español", value: "es-ES" },
  { shortLabel: "GL", label: "Galego", value: "gl-GL" },
];

const preferedLocaleValue = LocalStorage.getItem("locale") || defaultBrowserLocale?.value || "en-US";
let defaultLocale = (availableLocales.find((locale) => locale.value === preferedLocaleValue) || availableLocales[0]).value;

// Create I18n instance
const i18n = createI18n({
  locale: defaultLocale,
  legacy: false, // you must set `false`, to use Composition API
  globalInjection: true,
  messages,
});

export default ({ app }) => {
  // Tell app to use the I18n instance
  app.use(i18n);
};

export { i18n, defaultLocale };
