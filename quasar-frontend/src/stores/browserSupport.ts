import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from "src/composables/localStorage";

const localStorageBrowserAllowPDFPreview = createStorageEntry<boolean | null>("browserAllowPDFPreview", null);

interface State {
  allowPDFPreviewSavedValue: boolean | null;
};

export const useBrowserSupportStore = defineStore('browserSupportStore', {
  state: (): State => ({
    allowPDFPreviewSavedValue: localStorageBrowserAllowPDFPreview.get(),
  }),
  getters: {
    hasPDFPreviewSavedValue: (state: State): boolean => state.allowPDFPreviewSavedValue !== null,
    allowPDFPreviews: (state: State): boolean => state.allowPDFPreviewSavedValue === true,
  },
  actions: {
    setAllowPDFPreview(allowed: boolean) {
      this.allowPDFPreviewSavedValue = allowed;
      localStorageBrowserAllowPDFPreview.set(this.allowPDFPreviewSavedValue);
    }
  }
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useBrowserSupportStore, import.meta.hot));
}
