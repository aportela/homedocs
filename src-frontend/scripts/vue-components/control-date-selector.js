import dayjs from "dayjs";

const template = `
    <div class="select">
        <select v-model.number="range" :disabled="disabled" v-on:change="onChange">
            <option value="0">{{ $t("components.dateSelector.anyDate") }}</option>
            <option value="1">{{ $t("components.dateSelector.today") }}</option>
            <option value="2">{{ $t("components.dateSelector.yesterday") }}</option>
            <option value="3">{{ $t("components.dateSelector.lastWeek") }}</option>
            <option value="4">{{ $t("components.dateSelector.lastMonth") }}</option>
            <option value="5">{{ $t("components.dateSelector.last3Months") }}</option>
            <option value="6">{{ $t("components.dateSelector.last6Months") }}</option>
            <option value="7">{{ $t("components.dateSelector.lastYear") }}</option>
        </select>
    </div>

`;

export default {
  name: "homedocs-control-date-selector",
  template: template,
  data: function () {
    return {
      range: 0,
    };
  },
  props: ["disabled"],
  created: function () {},
  methods: {
    onChange: function () {
      let from = null;
      let to = null;
      switch (this.range) {
        // today
        case 1:
          from = dayjs().hour(0).minute(0).second(0).unix();
          to = dayjs().hour(23).minute(59).second(59).unix();
          break;
        // yesterday
        case 2:
          from = dayjs().subtract(1, "day").hour(0).minute(0).second(0).unix();
          to = dayjs().subtract(1, "day").hour(23).minute(59).second(59).unix();
          break;
        // last week
        case 3:
          from = dayjs().subtract(7, "day").hour(0).minute(0).second(0).unix();
          to = dayjs().hour(23).minute(59).second(59).unix();
          break;
        // last month
        case 4:
          from = dayjs()
            .subtract(1, "month")
            .hour(0)
            .minute(0)
            .second(0)
            .unix();
          to = dayjs().hour(23).minute(59).second(59).unix();
          break;
        // last 3 months
        case 5:
          from = dayjs()
            .subtract(3, "month")
            .hour(0)
            .minute(0)
            .second(0)
            .unix();
          to = dayjs().hour(23).minute(59).second(59).unix();
          break;
        // last 6 months
        case 6:
          from = dayjs()
            .subtract(6, "month")
            .hour(0)
            .minute(0)
            .second(0)
            .unix();
          to = dayjs().hour(23).minute(59).second(59).unix();
          break;
        // last year
        case 7:
          from = dayjs().subtract(1, "year").hour(0).minute(0).second(0).unix();
          to = dayjs().hour(23).minute(59).second(59).unix();
          break;
      }
      this.$emit("changed", {
        range: this.range,
        fromTimestamp: from,
        toTimestamp: to,
      });
    },
  },
};
