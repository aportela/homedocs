import { defineStore, acceptHMRUpdate } from "pinia";
import { createStorageEntry } from "src/composables/localStorage";

const localStorageShowToolTips = createStorageEntry<boolean>("showToolTips", false);

const localStorageAlwaysOpenUploadDialog = createStorageEntry<boolean>(
  "alwaysOpenUploadDialog",
  true
);

const localStorageDateFormat = createStorageEntry<string>(
  "dateFormat",
  "YYYY/MM/DD"
);

const localStorageDateTimeFormat = createStorageEntry<string>(
  "dateTimeFormat",
  "YYYY/MM/DD HH:mm:ss"
);

interface State {
  tokens: {
    access: string | null;
  };
  toolTips: boolean;
  alwaysOpenUploadDialog: boolean;
  dateFormat: string;
  dateTimeFormat: string;
};

export const useSessionStore = defineStore("session", {
  state: (): State => ({
    tokens: {
      access: null,
    },
    toolTips: localStorageShowToolTips.get(),
    alwaysOpenUploadDialog: localStorageAlwaysOpenUploadDialog.get(),
    dateFormat: localStorageDateFormat.get(),
    dateTimeFormat: localStorageDateTimeFormat.get(),
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
    },
    savedDateFormat(state): string | null {
      return (state.dateFormat);
    },
    savedDateTimeFormat(state): string | null {
      return (state.dateTimeFormat);
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
    },
    setDateFormat(value: string) {
      this.dateFormat = value;
      localStorageDateFormat.set(this.dateFormat);
    },
    removeDateFormat() {
      this.dateFormat = "YYYY/MM/DD";
      localStorageDateFormat.remove();
    },
    setDateTimeFormat(value: string) {
      this.dateTimeFormat = value;
      localStorageDateTimeFormat.set(this.dateTimeFormat);
    },
    removeDateTimeFormat() {
      this.dateTimeFormat = "YYYY/MM/DD HH:mm:ss";
      localStorageDateTimeFormat.remove();
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
