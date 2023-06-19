import { defineStore } from "pinia";

export const useSessionStore = defineStore("session", {
  state: () => ({
    jwt: null,
  }),

  getters: {
    isLogged: (state) => state.jwt != null,
  },
  actions: {
    signIn(jwt) {
      this.jwt = jwt;
    },
    signOut() {
      this.jwt = null;
    },
  },
});