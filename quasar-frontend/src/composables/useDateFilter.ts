/*
export function useDateFilter() {
  const { t } = useI18n();
/*
watch(
  () => dateFilter.formattedDate.from,
  (value) => {
    dateFilter.state.hasValue = !!value;
    dateFilter.onRecalcTimestamps();
  },
);

watch(
  () => dateFilter.formattedDate.to,
  (value) => {
    dateFilter.state.hasValue = !!value;
    dateFilter.onRecalcTimestamps();
  },
);

watch(
  () => dateFilter.formattedDate.fixed,
  (value) => {
    dateFilter.state.hasValue = !!value;
    dateFilter.onRecalcTimestamps();
  },
);


// UGLY HACK: selected value (model with label/value) do not react to global i18n changes
// so we are watching changes on first option label and when this label translation changes
// we translate again the label assigned to the model
watch(
() => dateFilterTypeOptions.value[0]!.label,
() => {
dateFilter.filterType.label = t(dateFilter.filterType.labelKey);
},
);

watch(
() => dateFilter.filterType,
(filterType) => {
if (filterType) {
  dateFilter.state.hasFrom = [3, 4, 5, 6, 8, 10].includes(
    filterType.value,
  );
  dateFilter.state.hasTo = [3, 4, 5, 6, 9, 10].includes(
    filterType.value,
  );
  dateFilter.state.hasFixed = [1, 2, 7].includes(filterType.value);
  dateFilter.state.denyChanges = [1, 2, 3, 4, 5, 6].includes(
    filterType.value,
  );
} else {
  dateFilter.filterType = dateFilterTypeOptions.value[0]!;
}
dateFilter.onRecalcDates();
dateFilter.onRecalcTimestamps();
},
);

*/
