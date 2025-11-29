import { boot } from "quasar/wrappers";
import { browserAllowPDFPreview as localStorageBrowserAllowPDFPreview } from "src/composables/localStorage";
import { isPdfSupportedInIframe } from "src/composables/common";

export default boot(async () => {
  if (localStorageBrowserAllowPDFPreview.get() === null) {
    try {
      const supported = await isPdfSupportedInIframe();
      localStorageBrowserAllowPDFPreview.set(supported)
    } catch (e) {
      console.error(e);
    }
  }
});
