import { defineStore, acceptHMRUpdate } from 'pinia';
import { AxiosError } from 'axios';
import { createStorageEntry } from 'src/composables/localStorage';
import { type GetNewAccessTokenResponse as GetNewAccessTokenResponseInterface } from 'src/types/apiResponses';
import { api } from 'src/composables/api';

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
  session: {
    accessToken: {
      token: string | null;
      expiresAtTimestamp: number | null;
    };
    other: {
      toolTips: boolean;
      alwaysOpenUploadDialog: boolean;
      dateFormat: string;
      dateTimeFormat: string;
      searchDialogResultsPage: number;
    };
  };
}

export const useSessionStore = defineStore('session', {
  state: (): State => ({
    session: {
      accessToken: {
        token: null,
        expiresAtTimestamp: null,
      },
      other: {
        toolTips: localStorageShowToolTips.get(),
        alwaysOpenUploadDialog: localStorageAlwaysOpenUploadDialog.get(),
        dateFormat: localStorageDateFormat.get(),
        dateTimeFormat: localStorageDateTimeFormat.get(),
        searchDialogResultsPage: localStorageSearchDialogResultsPage.get(),
      },
    },
  }),
  getters: {
    hasAccessToken: (state: State): boolean =>
      state.session.accessToken.token !== null &&
      state.session.accessToken.expiresAtTimestamp !== null,
    accessToken: (state: State): string | null => state.session.accessToken.token,
    accessTokenExpirationTimestamp: (state): number | null =>
      state.session.accessToken.expiresAtTimestamp,
    toolTipsEnabled: (state: State): boolean => state.session.other.toolTips,
    openUploadDialog: (state: State): boolean => state.session.other.alwaysOpenUploadDialog,
    savedDateFormat: (state: State): string => state.session.other.dateFormat,
    savedDateTimeFormat: (state: State): string => state.session.other.dateTimeFormat,
    searchDialogResultsPage: (state: State): number => state.session.other.searchDialogResultsPage,
  },
  actions: {
    async refreshAccessToken(): Promise<boolean> {
      try {
        const successResponse: GetNewAccessTokenResponseInterface =
          await api.auth.renewAccessToken();
        this.setAccessToken(
          successResponse.data.accessToken.token,
          successResponse.data.accessToken.expiresAtTimestamp,
        );
        return true;
      } catch (e: unknown) {
        if (e instanceof AxiosError) {
          if (e.response?.status !== 401) {
            // 401 is the "normal" error response if we do not have a previous refresh token or refresh token is expired
            console.error('Invalid error http response code', e.status);
          }
        } else {
          console.error('Invalid error response', e);
        }
        return false;
      }
    },
    accessTokenExpiresBeforeInterval(interval: number) {
      if (this.session.accessToken.expiresAtTimestamp) {
        const currentTimestamp = Math.round(Date.now() / 1000);
        return this.session.accessToken.expiresAtTimestamp - currentTimestamp <= interval;
      } else {
        return false;
      }
    },
    setAccessToken(token: string, expiresAtTimestamp: number): void {
      this.session.accessToken.token = token;
      this.session.accessToken.expiresAtTimestamp = expiresAtTimestamp;
    },
    removeAccessToken(): void {
      this.session.accessToken.token = null;
      this.session.accessToken.expiresAtTimestamp = null;
    },
    toggleToolTips(enabled: boolean) {
      this.session.other.toolTips = enabled;
      localStorageShowToolTips.set(this.session.other.toolTips);
    },
    setOpenUploadDialog(value: boolean) {
      this.session.other.alwaysOpenUploadDialog = value;
      localStorageAlwaysOpenUploadDialog.set(this.session.other.alwaysOpenUploadDialog);
    },
    setDateFormat(value: string) {
      this.session.other.dateFormat = value;
      localStorageDateFormat.set(this.session.other.dateFormat);
    },
    removeDateFormat() {
      this.session.other.dateFormat = 'YYYY/MM/DD';
      localStorageDateFormat.remove();
    },
    setDateTimeFormat(value: string) {
      this.session.other.dateTimeFormat = value;
      localStorageDateTimeFormat.set(this.session.other.dateTimeFormat);
    },
    removeDateTimeFormat() {
      this.session.other.dateTimeFormat = 'YYYY/MM/DD HH:mm:ss';
      localStorageDateTimeFormat.remove();
    },
    setSearchDialogResultsPage(value: number) {
      this.session.other.searchDialogResultsPage = value;
      localStorageSearchDialogResultsPage.set(this.session.other.searchDialogResultsPage);
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useSessionStore, import.meta.hot));
}
