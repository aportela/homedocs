import { type AxiosResponse } from "axios";

interface DefaultAxiosResponse {
  data: AxiosResponse<any, any, {}>;
};

interface LoginResponse extends DefaultAxiosResponse {
};

interface RegisterResponse extends DefaultAxiosResponse {
};

interface UserProfileResponseData {
  id: string | null;
  email: string | null;
};

interface GetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  }
};

interface SetProfileResponse extends Omit<DefaultAxiosResponse, 'data'> {
  data: {
    user: UserProfileResponseData;
  }
};

export { type DefaultAxiosResponse, type LoginResponse, type RegisterResponse, type GetProfileResponse, type SetProfileResponse };
