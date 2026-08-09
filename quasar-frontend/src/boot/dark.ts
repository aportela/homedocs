import { defineBoot } from '#q-app';
import { useDarkModeStore } from '@/stores/darkMode';
import { Dark } from 'quasar';

export default defineBoot(() => {
  const darkModeStore = useDarkModeStore();
  if (darkModeStore.isDarkModeActive === true) {
    Dark.set(true);
  } else if (darkModeStore.isDarkModeActive === false) {
    Dark.set(false);
  } else {
    Dark.set('auto');
  }
});
