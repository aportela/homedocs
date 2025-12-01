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
    hasAccessToken(state): boolean {
      return state.currentAccessJWT !== null;
    },
    hasRefreshToken(state): boolean {
      return state.currentRefreshJWT !== null;
    },
    accessJWT(state): string | null {
      return state.currentAccessJWT
    },
    refreshJWT(state): string | null {
      return state.currentRefreshJWT
    },
  },
  actions: {
    setAccessToken(jwt: string): void {
      this.currentAccessJWT = jwt;
      localStorageAccessJWT.set(this.currentAccessJWT);
    },
    removeAccessToken(): void {
      this.currentAccessJWT = null;
      localStorageAccessJWT.remove();
    },
    setRefreshToken(jwt: string): void {
      this.currentRefreshJWT = jwt;
      localStorageRefreshJWT.set(this.currentRefreshJWT);
    },
    removeRefreshToken(): void {
      this.currentRefreshJWT = null;
      localStorageRefreshJWT.remove();
    },
  },
});
