import { defineBoot } from "#q-app/wrappers";
import { browserAllowPDFPreview as localStorageBrowserAllowPDFPreview } from "src/composables/localStorage";
import { isPdfSupportedInIframe } from "src/composables/common";

export default defineBoot(async () => {
  if (localStorageBrowserAllowPDFPreview.get() === null) {
    try {
      const supported = await isPdfSupportedInIframe();
      localStorageBrowserAllowPDFPreview.set(supported)
    } catch (e) {
      console.error(e);
    }
  }
});
