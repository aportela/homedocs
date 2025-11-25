import { SERVER_API_BASE_PATH } from "src/constants";

export const getURL = (id: string, prependBaseAPI: boolean = false): string => {
  return prependBaseAPI
    ? `${SERVER_API_BASE_PATH}/attachment/${id}`
    : `/attachment/${id}`;
};

export const getInlineURL = (id: string, prependBaseAPI: boolean = false): string => {
  return prependBaseAPI
    ? `${SERVER_API_BASE_PATH}/attachment/${id}/inline`
    : `/attachment/${id}/inline`;
};
