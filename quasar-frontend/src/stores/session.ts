import { defineStore, acceptHMRUpdate } from "pinia";
import { createStorageEntry } from "src/composables/localStorage";

const localStorageShowToolTips = createStorageEntry<boolean>("showToolTips", false);

const localStorageAlwaysOpenUploadDialog = createStorageEntry<boolean>(
  "alwaysOpenUploadDialog",
  true
);


interface State {
  tokens: {
    access: string | null;
  };
  toolTips: boolean;
  alwaysOpenUploadDialog: boolean;
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: null,
    },
    toolTips: localStorageShowToolTips.get(),
    alwaysOpenUploadDialog: localStorageAlwaysOpenUploadDialog.get(),
  }),
  getters: {
    hasAccessToken(state): boolean {
      return state.tokens.access !== null;
    },
    accessToken(state): string | null {
      return state.tokens.access
    },
    toolTipsEnabled(state): boolean {
      return (state.toolTips);
    },
    openUploadDialog(state): boolean {
      return (state.alwaysOpenUploadDialog);
    }
  },
  actions: {
    setAccessToken(token: string): void {
      this.tokens.access = token;
    },
    removeAccessToken(): void {
      this.tokens.access = null;
    },
    toggleToolTips(enabled: boolean) {
      this.toolTips = enabled;
      localStorageShowToolTips.set(this.toolTips);
    },
    setOpenUploadDialog(value: boolean) {
      this.alwaysOpenUploadDialog = value;
      localStorageAlwaysOpenUploadDialog.set(this.alwaysOpenUploadDialog);
    }
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
