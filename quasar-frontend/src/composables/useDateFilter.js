import { computed, reactive, watch } from "vue";
import { useI18n } from "vue-i18n";
import { date } from "quasar";

export function useDateFilter() {
  const { t } = useI18n();

  const availableSelectOptions = [
    { labelKey: "Any date", value: 0 },
    { labelKey: "Today", value: 1 },
    { labelKey: "Yesterday", value: 2 },
    { labelKey: "Last 7 days", value: 3 },
    { labelKey: "Last 15 days", value: 4 },
    { labelKey: "Last 31 days", value: 5 },
    { labelKey: "Last 365 days", value: 6 },
    { labelKey: "Fixed date", value: 7 },
    { labelKey: "From date", value: 8 },
    { labelKey: "To date", value: 9 },
    { labelKey: "Between dates", value: 10 },
  ];

  const dateFilterTypeOptions = computed(() =>
    availableSelectOptions.map((option) => ({
      ...option,
      label: t(option.labelKey),
    })),
  );

  const dateMask = "YYYY/MM/DD";

  const getFormattedDate = (daysToSubtract = 0) => {
    return date.formatDate(
      date.addToDate(Date.now(), { days: daysToSubtract }),
      dateMask,
    );
  };

  const getDateFilterInstance = () => {
    const dateFilter = reactive({
      filterType: dateFilterTypeOptions.value[0],
      formattedDate: {
        fixed: null,
        from: null,
        to: null,
      },
      timestamps: {
        from: null,
        to: null,
      },
      state: {
        hasFrom: false,
        hasTo: false,
        hasFixed: false,
        hasValue: false,
        denyChanges: false,
      },
      // UGLY HACK to skip clearing/reseting values on filterType watchers
      skipClearOnRecalc: {
        from: false,
        to: false,
        fixed: false,
      },
    });

    const onRecalcDates = () => {
      if (!dateFilter.skipClearOnRecalc.from) {
        dateFilter.formattedDate.from = null;
      }
      if (!dateFilter.skipClearOnRecalc.to) {
        dateFilter.formattedDate.to = null;
      }
      if (!dateFilter.skipClearOnRecalc.fixed) {
        dateFilter.formattedDate.fixed = null;
      }
      dateFilter.skipClearOnRecalc.from = false;
      dateFilter.skipClearOnRecalc.to = false;
      dateFilter.skipClearOnRecalc.fixed = false;
      dateFilter.timestamps.from = null;
      dateFilter.timestamps.to = null;
      // generate model/formatted (visible) dates
      switch (dateFilter.filterType?.value) {
        // NONE
        case 0:
          dateFilter.formattedDate.from = null;
          dateFilter.formattedDate.to = null;
          dateFilter.formattedDate.fixed = null;
          break;
        // TODAY
        case 1:
          dateFilter.formattedDate.fixed = getFormattedDate();
          break;
        // YESTERDAY
        case 2:
          dateFilter.formattedDate.fixed = getFormattedDate(-1);
          break;
        // LAST 7 DAYS
        case 3:
          dateFilter.formattedDate.from = getFormattedDate(-7);
          dateFilter.formattedDate.to = getFormattedDate();
          break;
        // LAST 15 DAYS
        case 4:
          dateFilter.formattedDate.from = getFormattedDate(-15);
          dateFilter.formattedDate.to = getFormattedDate();
          break;
        // LAST 31 DAYS
        case 5:
          dateFilter.formattedDate.from = getFormattedDate(-31);
          dateFilter.formattedDate.to = getFormattedDate();
          break;
        // LAST 365 DAYS
        case 6:
          dateFilter.formattedDate.from = getFormattedDate(-365);
          dateFilter.formattedDate.to = getFormattedDate();
          break;
        // FIXED DATE
        case 7:
          break;
        // FROM DATE
        case 8:
          //dateFilter.formattedDate.from = getFormattedDate();
          break;
        // TO DATE
        case 9:
          //dateFilter.formattedDate.to = getFormattedDate();
          break;
        // BETWEEN DATES
        case 10:
          //dateFilter.formattedDate.from = getFormattedDate();
          //dateFilter.formattedDate.to = getFormattedDate();
          break;
      }
    };

    const onRecalcTimestamps = () => {
      // generate API timestamps (real filters)
      if (dateFilter.formattedDate.fixed) {
        dateFilter.timestamps.from = Number(date.formatDate(
          date.adjustDate(
            date.extractDate(dateFilter.formattedDate.fixed, dateMask),
            { hour: 0, minute: 0, second: 0, millisecond: 0 },
          ),
          "x", // timestamp in ms
        ));
        dateFilter.timestamps.to = Number(date.formatDate(
          date.adjustDate(
            date.extractDate(dateFilter.formattedDate.fixed, dateMask),
            { hour: 23, minute: 59, second: 59, millisecond: 999 },
          ),
          "x", // timestamp in ms
        ));
      } else {
        if (dateFilter.formattedDate.from) {
          dateFilter.timestamps.from = Number(date.formatDate(
            date.adjustDate(
              date.extractDate(dateFilter.formattedDate.from, "YYYY/MM/DD"),
              { hour: 0, minute: 0, second: 0, millisecond: 0 },
            ),
            "x", // timestamp in ms
          ));
        }
        if (dateFilter.formattedDate.to) {
          dateFilter.timestamps.to = Number(date.formatDate(
            date.adjustDate(
              date.extractDate(dateFilter.formattedDate.to, "YYYY/MM/DD"),
              { hour: 23, minute: 59, second: 59, millisecond: 999 },
            ),
            "x", // timestamp in ms
          ));
        }
      }
    };

    watch(
      () => dateFilter.formattedDate.from,
      (value) => {
        dateFilter.state.hasValue = !!value;
        onRecalcTimestamps();
      },
    );

    watch(
      () => dateFilter.formattedDate.to,
      (value) => {
        dateFilter.state.hasValue = !!value;
        onRecalcTimestamps();
      },
    );

    watch(
      () => dateFilter.formattedDate.fixed,
      (value) => {
        dateFilter.state.hasValue = !!value;
        onRecalcTimestamps();
      },
    );

    // UGLY HACK: selected value (model with label/value) do not react to global i18n changes
    // so we are watching changes on first option label and when this label translation changes
    // we translate again the label assigned to the model
    watch(
      () => dateFilterTypeOptions.value[0].label,
      (value) => {
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
          dateFilter.filterType = dateFilterTypeOptions.value[0];
        }
        onRecalcDates();
        onRecalcTimestamps();
      },
    );

    return dateFilter;
  };

  return {
    dateMask,
    dateFilterTypeOptions,
    getDateFilterInstance,
  };
}
