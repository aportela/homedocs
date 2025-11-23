import { defineBoot } from '#q-app/wrappers';
import { usei18n } from "src/composables/usei18n";

const { i18n } = usei18n();

export default defineBoot(({ app }) => {
  // Set i18n instance on app
  app.use(i18n);
});
