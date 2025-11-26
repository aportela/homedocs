import { type AxiosResponse } from "axios";

interface DefaultAxiosResponse {
  data: AxiosResponse<any, any, {}>;
};

interface LoginResponse extends DefaultAxiosResponse {
};

interface RegisterResponse extends DefaultAxiosResponse {
};

export { type DefaultAxiosResponse, type LoginResponse, type RegisterResponse };
