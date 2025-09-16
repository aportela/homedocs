import { useI18n } from 'vue-i18n'

export function useFormatDates() {

  const { t } = useI18n()

  function timeAgo(timestamp) {
    const now = Date.now();
    const diff = now - new Date(timestamp).getTime();

    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    const months = Math.floor(days / 30);  // WARNING: NOT EXACT (30 days / month)
    const years = Math.floor(days / 365);  // WARNING: NOT EXACT (365 days / year)

    if (years > 0) {
      return (t("timeAgo.year", { count: years }));
    } else if (months > 0) {
      return (t("timeAgo.month", { count: months }));
    } else if (days > 0) {
      return (t("timeAgo.day", { count: days }));
    } else if (hours > 0) {
      return (t("timeAgo.hour", { count: hours }));
    } else if (minutes > 0) {
      return (t("timeAgo.minute", { count: minutes }));
    } else if (seconds > 0) {
      return (t("timeAgo.second", { count: seconds }));
    } else {
      return (t("timeAgo.now"));
    }
  }

  return { timeAgo }
}
