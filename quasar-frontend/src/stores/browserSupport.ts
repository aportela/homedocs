import { defineStore, acceptHMRUpdate } from 'pinia';
import { createStorageEntry } from "src/composables/localStorage";
import { isPdfSupportedInIframe } from "src/composables/common";

const localStorageBrowserAllowPDFPreview = createStorageEntry<boolean | null>("browserAllowPDFPreview", null);

if (localStorageBrowserAllowPDFPreview.get() === null) {
  try {
    const supported = await isPdfSupportedInIframe();
    localStorageBrowserAllowPDFPreview.set(supported)
  } catch (e) {
    localStorageBrowserAllowPDFPreview.set(false)
    console.error(e);
  }
}

interface State {
  allowPDFPreviewSavedValue: boolean | null;
};

export const useBrowserSupportStore = defineStore('browserSupportStore', {
  state: (): State => ({
    allowPDFPreviewSavedValue: localStorageBrowserAllowPDFPreview.get(),
  }),
  getters: {
    allowPDFPreviews(state): boolean {
      return state.allowPDFPreviewSavedValue === true
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useBrowserSupportStore, import.meta.hot));
}
