import { defineStore, acceptHMRUpdate } from 'pinia';
import { browserAllowPDFPreview as localStorageBrowserAllowPDFPreview } from "src/composables/localStorage";

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
