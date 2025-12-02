import { defineBoot } from '#q-app/wrappers';
import { axiosInstance } from 'src/composables/axios';

export default defineBoot(({ app }) => {
  app.config.globalProperties.$axios = axiosInstance;
});
