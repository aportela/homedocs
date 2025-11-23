import { Dark } from "quasar";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { darkMode } = useLocalStorage();

const savedMode = darkMode.get();
if (savedMode === true) {
  Dark.set(true);
} else if (savedMode === false) {
  Dark.set(false);
} else {
  Dark.set("auto");
}

export function useDarkMode() {

  const isDarkModeActive = () => Dark.isActive;

  const toggleDarkMode = () => {
    Dark.toggle();
    darkMode.set(Dark.isActive);
  };

  return { isDarkModeActive, toggleDarkMode };
}
