import { Dark } from "quasar";
import { useLocalStorage } from "src/composables/useLocalStorage";

const { darkMode: localStorageDarkMode } = useLocalStorage();

const savedMode = localStorageDarkMode.get();

if (savedMode === true) {
  Dark.set(true);
} else if (savedMode === false) {
  Dark.set(false);
} else {
  Dark.set("auto");
}

export function useDarkMode() {

  const isDarkModeActive = (): boolean => Dark.isActive;

  const toggleDarkMode = (): void => {
    Dark.toggle();
    localStorageDarkMode.set(Dark.isActive);
  };

  return { isDarkModeActive, toggleDarkMode };
}
