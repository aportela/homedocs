import { defineStore } from "pinia";

import { accessJWT as localStorageAccessJWT, refreshJWT as localStorageRefreshJWT } from "src/composables/localStorage";

interface State {
  accessJWT: string | null;
  refreshJWT: string | null;
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    accessJWT: localStorageAccessJWT.get() ?? null,
    refreshJWT: localStorageRefreshJWT.get() ?? null,
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.accessJWT !== null;
    },
    hasRefreshToken(state): boolean {
      return state.refreshJWT !== null;
    },
    accessJWT(state): string | null {
      return state.accessJWT
    },
    refreshJWT(state): string | null {
      return state.refreshJWT
    },
  },
  actions: {
    setAccessToken(jwt: string): void {
      this.accessJWT = jwt;
      localStorageAccessJWT.set(this.accessJWT);
    },
    removeAccessToken(): void {
      this.accessJWT = null;
      localStorageAccessJWT.remove();
    },
    setRefreshToken(jwt: string): void {
      this.refreshJWT = jwt;
      localStorageRefreshJWT.set(this.refreshJWT);
    },
    removeRefreshToken(): void {
      this.refreshJWT = null;
      localStorageRefreshJWT.remove();
    },
  },
});
