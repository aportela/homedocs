import { defineStore } from "pinia";

import { accessJWT as localStorageAccessJWT } from "src/composables/localStorage";

interface State {
  tokens: {
    access: string | null;
  }
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: localStorageAccessJWT.get() ?? null,
    }
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.tokens.access !== null;
    },
    accessToken(state): string | null {
      return state.tokens.access
    },
  },
  actions: {
    setAccessToken(jwt: string): void {
      this.tokens.access = jwt;
      localStorageAccessJWT.set(this.tokens.access);
    },
    removeAccessToken(): void {
      this.tokens.access = null;
      localStorageAccessJWT.remove();
    },
  },
});
