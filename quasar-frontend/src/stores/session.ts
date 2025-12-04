import { defineStore } from "pinia";

interface State {
  tokens: {
    access: string | null;
  }
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: null,
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
    },
    removeAccessToken(): void {
      this.tokens.access = null;
    },
  },
});
