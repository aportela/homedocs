import { defineBoot } from "#q-app";
import { useBrowserSupportStore } from "@/stores/browserSupport";
import { isPdfSupportedInIframe } from "@/composables/common";

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
