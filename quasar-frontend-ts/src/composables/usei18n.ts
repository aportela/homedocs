import { createI18n } from "vue-i18n";
import messages from "src/i18n";
import { Lang } from "quasar";
import { useLocalStorage } from "./useLocalStorage";

export type MessageLanguages = keyof typeof messages;
// Type-define 'en-US' as the master schema for the resource
export type MessageSchema = (typeof messages)['en-US'];

// See https://vue-i18n.intlify.dev/guide/advanced/typescript.html#global-resource-schema-type-definition
/* eslint-disable @typescript-eslint/no-empty-object-type */
declare module 'vue-i18n' {
  // define the locale messages schema
  export interface DefineLocaleMessage extends MessageSchema { }

  // define the datetime format schema
  export interface DefineDateTimeFormat { }

  // define the number format schema
  export interface DefineNumberFormat { }
}
/* eslint-enable @typescript-eslint/no-empty-object-type */


const { locale } = useLocalStorage();

const availableLocales = Object.keys(messages);

const localeMappings: Record<'en' | 'es' | 'gl', string> = {
  en: 'en-EN',
  es: 'es-ES',
  gl: 'gl-GL'
};

const browserLocale = Lang.getLocale() ?? "en";
const key = browserLocale.substring(0, 2) as keyof typeof localeMappings;
const normalizedBrowsedLocale = localeMappings[key] || browserLocale;

const savedLocale = locale.get();

let currentLocale = savedLocale || normalizedBrowsedLocale;

currentLocale = availableLocales.includes(currentLocale)
  ? currentLocale
  : "en-US";


const i18n = createI18n<{ message: MessageSchema }, MessageLanguages>({
  locale: currentLocale,
  legacy: false, // you must set `false`, to use Composition API
  globalInjection: true,
  messages,
});

export function usei18n() {
  return { i18n };
}
