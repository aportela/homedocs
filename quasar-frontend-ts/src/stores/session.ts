import { defineStore } from "pinia";

import { useLocalStorage } from "src/composables/useLocalStorage";

const { authJWT: localStorageJWT } = useLocalStorage();

interface State {
  currentJWT: string | null;
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    currentJWT: localStorageJWT.get() ?? null,
  }),
  getters: {
    isLogged(state): boolean {
      return state.currentJWT !== null
    },
    jwt(state): string | null {
      return state.currentJWT
    },
  },
  actions: {
    setJWT(jwt = null): void {
      this.currentJWT = jwt;
      if (this.currentJWT !== null) {
        localStorageJWT.set(this.currentJWT);
      } else {
        localStorageJWT.remove();
      }
    },
  },
});
