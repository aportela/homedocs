import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from 'src/composables/localStorage';

const localStorageShowToolTips = createStorageEntry<boolean>('showToolTips', false);

const localStorageAlwaysOpenUploadDialog = createStorageEntry<boolean>(
  'alwaysOpenUploadDialog',
  true,
);

const localStorageDateFormat = createStorageEntry<string>('dateFormat', 'YYYY/MM/DD');

const localStorageDateTimeFormat = createStorageEntry<string>(
  'dateTimeFormat',
  'YYYY/MM/DD HH:mm:ss',
);

const localStorageSearchDialogResultsPage = createStorageEntry<number>(
  'searchDialogResultsPage',
  8,
);

interface State {
  tokens: {
    access: string | null;
  };
  other: {
    toolTips: boolean;
    alwaysOpenUploadDialog: boolean;
    dateFormat: string;
    dateTimeFormat: string;
    searchDialogResultsPage: number;
  };
}

export const useSessionStore = defineStore('session', {
  state: (): State => ({
    tokens: {
      access: null,
    },
    other: {
      toolTips: localStorageShowToolTips.get(),
      alwaysOpenUploadDialog: localStorageAlwaysOpenUploadDialog.get(),
      dateFormat: localStorageDateFormat.get(),
      dateTimeFormat: localStorageDateTimeFormat.get(),
      searchDialogResultsPage: localStorageSearchDialogResultsPage.get(),
    },
  }),
  getters: {
    hasAccessToken: (state: State): boolean => state.tokens.access !== null,
    accessToken: (state: State): string | null => state.tokens.access,
    toolTipsEnabled: (state: State): boolean => state.other.toolTips,
    openUploadDialog: (state: State): boolean => state.other.alwaysOpenUploadDialog,
    savedDateFormat: (state: State): string => state.other.dateFormat,
    savedDateTimeFormat: (state: State): string => state.other.dateTimeFormat,
    searchDialogResultsPage: (state: State): number => state.other.searchDialogResultsPage,
  },
  actions: {
    setAccessToken(token: string): void {
      this.tokens.access = token;
    },
    removeAccessToken(): void {
      this.tokens.access = null;
    },
    toggleToolTips(enabled: boolean) {
      this.other.toolTips = enabled;
      localStorageShowToolTips.set(this.other.toolTips);
    },
    setOpenUploadDialog(value: boolean) {
      this.other.alwaysOpenUploadDialog = value;
      localStorageAlwaysOpenUploadDialog.set(this.other.alwaysOpenUploadDialog);
    },
    setDateFormat(value: string) {
      this.other.dateFormat = value;
      localStorageDateFormat.set(this.other.dateFormat);
    },
    removeDateFormat() {
      this.other.dateFormat = 'YYYY/MM/DD';
      localStorageDateFormat.remove();
    },
    setDateTimeFormat(value: string) {
      this.other.dateTimeFormat = value;
      localStorageDateTimeFormat.set(this.other.dateTimeFormat);
    },
    removeDateTimeFormat() {
      this.other.dateTimeFormat = 'YYYY/MM/DD HH:mm:ss';
      localStorageDateTimeFormat.remove();
    },
    setSearchDialogResultsPage(value: number) {
      this.other.searchDialogResultsPage = value;
      localStorageSearchDialogResultsPage.set(this.other.searchDialogResultsPage);
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
