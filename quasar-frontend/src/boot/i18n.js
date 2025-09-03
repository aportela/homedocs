import { createI18n } from "vue-i18n";
import messages from "src/i18n";
import { LocalStorage } from "quasar";

const availableLocales = Object.keys(messages);
const savedLocale = LocalStorage.getItem('locale');
let currentLocale = savedLocale || navigator.language || navigator.userLanguage;
currentLocale = availableLocales.includes(currentLocale) ? currentLocale : "en-US";

// Create I18n instance
const i18n = createI18n({
  locale: currentLocale,
  legacy: false, // you must set `false`, to use Composition API
  globalInjection: true,
  messages,
});

export default ({ app }) => {
  // Tell app to use the I18n instance
  app.use(i18n);
};

export { i18n };
