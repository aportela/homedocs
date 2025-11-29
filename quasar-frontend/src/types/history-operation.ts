import { type DateTime as DateTimeInterface } from "src/types/date-time";
import { uid } from "quasar";

type HistoryOperationType = 1 | 2;

interface HistoryOperation {
  id: string;
  createdAt: DateTimeInterface;
  //operationType: HistoryOperationType;
  label: string;
  icon: string;
};

class HistoryOperationClass implements HistoryOperation {
  id: string;
  createdAt: DateTimeInterface;
  //operationType: HistoryOperationType;
  label: string;
  icon: string;

  constructor(createdAt: DateTimeInterface, operationType: HistoryOperationType) {
    this.id = uid();
    //this.operationType = operationType;
    switch (operationType) {
      case 1:
        this.label = "Document created";
        this.icon = "post_add";
        break;
      case 2:
        this.label = "Document updated";
        this.icon = "edit_note";
        break;
      default:
        this.label = "Unknown operation";
        this.icon = "error";
        break;
    }
    this.createdAt = createdAt;
  }
};

export { type HistoryOperationType, type HistoryOperation, HistoryOperationClass };
