import { defineStore, acceptHMRUpdate } from 'pinia';
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

export const useDarkModeStore = defineStore('darkModeStore', {
  state: () => ({
    active: Dark.isActive
  }),
  getters: {
    isActive: (state) => state.active
  },
  actions: {
    set(active: boolean) {
      this.active = active;
      Dark.set(this.active);
      localStorageDarkMode.set(this.active);
    },
    toggle() {
      this.set(!this.active);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useDarkModeStore, import.meta.hot));
}
