import { type OrderType } from "./orderType";

interface Sort {
  field: string;
  label: string;
  order: OrderType;
}

class SortClass implements Sort {
  field: string;
  label: string;
  order: OrderType;

  constructor(field: string, label: string, order: OrderType) {
    this.field = field;
    this.label = label;
    this.order = order;
  }

  refreshLabel(availableItems: Sort[]) {
    this.label = availableItems.find((item) => item.field == this.field)?.label || "";
  };

  toggle(field: string, order?: OrderType) {
    if (this.field == field) {
      if (!order) {
        this.order = this.order == "ASC" ? "DESC" : "ASC";
      } else {
        this.order = order;
      }
    } else {
      this.field = field;
      this.order = !order ? "ASC" : order;
    }
  }
};

export { type Sort, SortClass };
