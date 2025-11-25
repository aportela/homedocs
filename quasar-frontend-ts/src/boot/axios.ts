import { defineBoot } from '#q-app/wrappers';
import { axiosInstance } from 'src/composables/useAxios';

export default defineBoot(({ app }) => {
  app.config.globalProperties.$api = axiosInstance;
});
