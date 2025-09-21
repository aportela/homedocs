import { date } from "quasar";
import { reactive, watch } from "vue";

export function useDateFilter() {

  const dateFilterTypeOptions = reactive([
    { label: "Any date", value: 0 },
    { label: "Today", value: 1 },
    { label: "Yesterday", value: 2 },
    { label: "Last 7 days", value: 3 },
    { label: "Last 15 days", value: 4 },
    { label: "Last 31 days", value: 5 },
    { label: "Last 365 days", value: 6 },
    { label: "Fixed date", value: 7 },
    { label: "From date", value: 8 },
    { label: "To date", value: 9 },
    { label: "Between dates", value: 10 }
  ]);

  const dateFilter = reactive({
    filterType: null, // TODO: 0 ???
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
      denyChanges: false
    }
  });

  const onRecalcDates = () => {
    dateFilter.formattedDate.from = null;
    dateFilter.formattedDate.to = null;
    dateFilter.formattedDate.fixed = null;
    dateFilter.timestamps.from = null;
    dateFilter.timestamps.to = null;
    // generate model/formatted (visible) dates
    switch (dateFilter.filterType?.value) {
      // NONE
      case 0:
        break;
      // TODAY
      case 1:
        dateFilter.formattedDate.fixed = date.formatDate(
          Date.now(),
          "YYYY/MM/DD",
        );
        break;
      // YESTERDAY
      case 2:
        dateFilter.formattedDate.fixed = date.formatDate(
          date.addToDate(Date.now(), { days: -1 }),
          "YYYY/MM/DD",
        );
        break;
      // LAST 7 DAYS
      case 3:
        dateFilter.formattedDate.from = date.formatDate(
          date.addToDate(Date.now(), { days: -7 }),
          "YYYY/MM/DD",
        );
        dateFilter.formattedDate.to = date.formatDate(
          date.addToDate(Date.now(), { days: -1 }),
          "YYYY/MM/DD",
        );
        break;
      // LAST 15 DAYS
      case 4:
        dateFilter.formattedDate.from = date.formatDate(
          date.addToDate(Date.now(), { days: -15 }),
          "YYYY/MM/DD",
        );
        dateFilter.formattedDate.to = date.formatDate(
          date.addToDate(Date.now(), { days: -1 }),
          "YYYY/MM/DD",
        );
        break;
      // LAST 31 DAYS
      case 5:
        dateFilter.formattedDate.from = date.formatDate(
          date.addToDate(Date.now(), { days: -31 }),
          "YYYY/MM/DD",
        );
        dateFilter.formattedDate.to = date.formatDate(
          date.addToDate(Date.now(), { days: -1 }),
          "YYYY/MM/DD",
        );
        break;
      // LAST 365 DAYS
      case 6:
        dateFilter.formattedDate.from = date.formatDate(
          date.addToDate(Date.now(), { days: -365 }),
          "YYYY/MM/DD",
        );
        dateFilter.formattedDate.to = date.formatDate(
          date.addToDate(Date.now(), { days: -1 }),
          "YYYY/MM/DD",
        );
        break;
      // FIXED DATE
      case 7:
        break;
      // FROM DATE
      case 8:
        dateFilter.formattedDate.from = date.formatDate(Date.now(), "YYYY/MM/DD");
        break;
      // TO DATE
      case 9:
        dateFilter.formattedDate.to = date.formatDate(Date.now(), "YYYY/MM/DD");
        break;
      // BETWEEN DATES
      case 10:
        dateFilter.formattedDate.from = date.formatDate(Date.now(), "YYYY/MM/DD");
        dateFilter.formattedDate.to = date.formatDate(Date.now(), "YYYY/MM/DD");
        break;
    }
    // generate API timestamps (real filters)
    if (dateFilter.formattedDate.fixed) {
      dateFilter.timestamps.from = date.formatDate(
        date.adjustDate(
          date.extractDate(dateFilter.formattedDate.fixed, "YYYY/MM/DD"),
          { hour: 0, minute: 0, second: 0, millisecond: 0 },
        ),
        "X",
      );
      dateFilter.timestamps.from = date.formatDate(
        date.adjustDate(
          date.extractDate(dateFilter.formattedDate.fixed, "YYYY/MM/DD"),
          { hour: 0, minute: 0, second: 0, millisecond: 0 },
        ),
        "X",
      );
    } else {
      if (dateFilter.formattedDate.from) {
        dateFilter.timestamps.from = date.formatDate(
          date.adjustDate(
            date.extractDate(dateFilter.formattedDate.from, "YYYY/MM/DD"),
            { hour: 0, minute: 0, second: 0, millisecond: 0 },
          ),
          "X",
        );
      }
      if (dateFilter.formattedDate.to) {
        dateFilter.timestamps.from = date.formatDate(
          date.adjustDate(
            date.extractDate(dateFilter.formattedDate.to, "YYYY/MM/DD"),
            { hour: 0, minute: 0, second: 0, millisecond: 0 },
          ),
          "X",
        );
      }
    }
  };

  watch(
    () => dateFilter.filterType,
    (filterType) => {
      if (filterType) {
        dateFilter.state.hasFrom = [3, 4, 5, 6, 8, 10].includes(filterType.value);
        dateFilter.state.hasTo = [3, 4, 5, 6, 9, 10].includes(filterType.value);
        dateFilter.state.hasFixed = [1, 2, 7].includes(filterType.value);
        dateFilter.state.denyChanges = [1, 2, 3, 4, 5, 6].includes(filterType.value);

      } else {
        dateFilter.filterType = dateFilterTypeOptions[0];
      }
      onRecalcDates();
    }
  );

  return {
    dateFilterTypeOptions,
    dateFilter
  };
}
