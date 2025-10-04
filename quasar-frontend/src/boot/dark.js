import { boot } from 'quasar/wrappers'
import { Dark } from "quasar";

import { useLocalStorage } from 'src/composables/useLocalStorage';

const { darkMode } = useLocalStorage();

// "async" is optional;
// more info on params: https://v2.quasar.dev/quasar-cli/boot-files
export default boot(async (/* { app, router, ... } */) => {
  const savedMode = darkMode.get();
  if (savedMode === true) {
    Dark.set(true);
  } else if (savedMode === false) {
    Dark.set(false);
  } else {
    Dark.set("auto");
  }
})
