import { defineBoot } from '#q-app/wrappers'
import { useBrowserSupportStore } from "src/stores/browserSupport";
import { isPdfSupportedInIframe } from "src/composables/common";

export default defineBoot(async () => {
  const browserSupportStore = useBrowserSupportStore();
  if (!browserSupportStore.hasPDFPreviewSavedValue) {
    try {
      browserSupportStore.setAllowPDFPreview(await isPdfSupportedInIframe());
    } catch (e) {
      console.error("Error testing PDF support in browser", e);
    }
  }
});
