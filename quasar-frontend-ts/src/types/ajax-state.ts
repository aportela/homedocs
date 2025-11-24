import type { APIErrorDetails } from "./api-error-details";

export interface AjaxState {
  ajaxRunning: boolean;
  ajaxErrors: boolean;
  ajaxErrorMessage: string | null;
  ajaxAPIErrorDetails: APIErrorDetails | null;
};

export const defaultAjaxState: AjaxState = {
  ajaxRunning: false,
  ajaxErrors: false,
  ajaxErrorMessage: null,
  ajaxAPIErrorDetails: null,
};
