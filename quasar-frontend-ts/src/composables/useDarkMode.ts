import { Dark } from "quasar";
import { useLocalStorage } from "src/composables/useLocalStorage";

const { darkMode } = useLocalStorage();

export type DarkModeSetting = boolean | "auto" | null;

const savedMode = darkMode.get() as DarkModeSetting;

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
    darkMode.set(Dark.isActive);
  };

  return { isDarkModeActive, toggleDarkMode };
}
