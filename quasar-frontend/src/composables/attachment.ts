import { SERVER_API_BASE_PATH } from 'src/constants';

const getURL = (id: string, prependBaseAPI: boolean = false): string => {
  return prependBaseAPI ? `${SERVER_API_BASE_PATH}/attachment/${id}` : `/attachment/${id}`;
};

const getInlineURL = (id: string, prependBaseAPI: boolean = false): string => {
  return prependBaseAPI
    ? `${SERVER_API_BASE_PATH}/attachment/${id}/inline`
    : `/attachment/${id}/inline`;
};

export { getURL, getInlineURL };
