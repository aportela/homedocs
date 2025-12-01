import { defineStore } from "pinia";

import { accessJWT as localStorageAccessJWT, refreshJWT as localStorageRefreshJWT } from "src/composables/localStorage";

interface State {
  tokens: {
    access: string | null;
    refresh: string | null;
  }
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: localStorageAccessJWT.get() ?? null,
      refresh: localStorageRefreshJWT.get() ?? null,
    }
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.tokens.access !== null;
    },
    hasRefreshToken(state): boolean {
      return state.tokens.refresh !== null;
    },
    accessToken(state): string | null {
      return state.tokens.access
    },
    refreshToken(state): string | null {
      return state.tokens.refresh
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
    setRefreshToken(jwt: string): void {
      this.tokens.refresh = jwt;
      localStorageRefreshJWT.set(this.tokens.refresh);
    },
    removeRefreshToken(): void {
      this.tokens.refresh = null;
      localStorageRefreshJWT.remove();
    },
  },
});
