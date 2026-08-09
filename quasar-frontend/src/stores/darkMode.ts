import { defineStore, acceptHMRUpdate } from 'pinia';
import { Dark } from 'quasar';

const storePersistenceKey = 'homedocs.settings.darkMode';

interface State {
  active: boolean | null;
}

export const useDarkModeStore = defineStore('darkModeStore', {
  persist: {
    key: storePersistenceKey,
  },
  state: (): State => ({
    active: Dark.isActive,
  }),
  getters: {
    isDarkModeActive: (state: State): boolean | null => state.active,
  },
  actions: {
    set(active: boolean): void {
      this.active = active;
      Dark.set(this.active);
    },
    toggle(): void {
      this.set(!this.active);
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useDarkModeStore, import.meta.hot));
}
