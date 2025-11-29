import { defineStore, acceptHMRUpdate } from 'pinia';
import { isPdfSupportedInIframe } from "src/composables/common";

import { browserAllowPDFPreview as localStorageBrowserAllowPDFPreview } from "src/composables/localStorage";

if (localStorageBrowserAllowPDFPreview.get() === null) {
  try {
    const supported = await isPdfSupportedInIframe();
    localStorageBrowserAllowPDFPreview.set(supported)
  } catch (e) {
    console.error(e);
  }
}

interface State {
  allowPDFPreviews: boolean;
};

export const useBrowserSupportStore = defineStore('browserSupportStore', {
  state: (): State => ({
    allowPDFPreviews: localStorageBrowserAllowPDFPreview.get() === true
  }),
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useBrowserSupportStore, import.meta.hot));
}
