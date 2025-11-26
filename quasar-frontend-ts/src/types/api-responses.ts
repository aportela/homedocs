import { type AxiosResponse } from "axios";

interface DefaultAxiosResponse {
  data: AxiosResponse<any, any, {}>;
};

interface LoginResponse extends DefaultAxiosResponse {
};

export { type DefaultAxiosResponse, type LoginResponse };
