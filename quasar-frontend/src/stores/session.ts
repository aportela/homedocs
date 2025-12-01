import { defineStore } from "pinia";

import { accessJWT as localStorageAccessJWT, refreshJWT as localStorageRefreshJWT } from "src/composables/localStorage";

interface State {
  currentAccessJWT: string | null;
  currentRefreshJWT: string | null;
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    currentAccessJWT: localStorageAccessJWT.get() ?? null,
    currentRefreshJWT: localStorageRefreshJWT.get() ?? null,
  }),
  getters: {
    isLogged(state): boolean {
      return state.currentAccessJWT !== null
    },
    jwt(state): string | null {
      return state.currentAccessJWT
    },
  },
  actions: {
    setAccessJWT(jwt: string | null): void {
      this.currentAccessJWT = jwt;
      if (this.currentAccessJWT !== null) {
        localStorageAccessJWT.set(this.currentAccessJWT);
      } else {
        localStorageAccessJWT.remove();
      }
    },
  },
});
