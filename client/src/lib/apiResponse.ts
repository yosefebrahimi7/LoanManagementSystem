export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface AuthResponse {
  user: {
    id: number;
    firstName: string;
    lastName: string;
    email: string;
    isActive: boolean;
    createdAt: string;
    updatedAt: string;
  };
  token: string;
  refreshToken: string;
}

export interface User {
  id: number;
  firstName: string;
  lastName: string;
  email: string;
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

export const isApiResponse = (data: any): data is ApiResponse => {
  return typeof data === 'object' && 
         data !== null && 
         'success' in data && 
         'message' in data;
};

export const isAuthResponse = (data: any): data is AuthResponse => {
  return typeof data === 'object' && 
         data !== null && 
         'user' in data && 
         'token' in data && 
         'refreshToken' in data;
};
