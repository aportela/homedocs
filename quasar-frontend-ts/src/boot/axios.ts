import { defineBoot } from '#q-app/wrappers';
import { useAxios } from 'src/composables/useAxios';

const { axiosInstance } = useAxios();

export default defineBoot(({ app }) => {
  app.config.globalProperties.$api = axiosInstance;
});
