import axios from 'axios';
import { showErrorToast } from './toast';
import useAuth from '../stores/auth';
import { queryClient } from './queryClient';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

// ساخت instance اصلی axios
const createApiInstance = () => {
  const instance = axios.create({
    baseURL: API_BASE_URL,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    withCredentials: false,
  });

  // Request Interceptor - افزودن token به header
  instance.interceptors.request.use(
    (config) => {
      const token = useAuth.getState().token;
      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    },
    (error) => {
      return Promise.reject(error);
    }
  );

  // Response Interceptor - مدیریت خطاها
  instance.interceptors.response.use(
    (response) => response,
    async (error) => {
      const originalRequest = error.config;

      // خطای 401 - Unauthorized
      if (error?.response?.status === 401 && !originalRequest._retry) {
        originalRequest._retry = true;

        try {
          const refreshToken = useAuth.getState().refreshToken;
          if (refreshToken) {
            const response = await axios.post(`${API_BASE_URL}/auth/refresh`, {
              refreshToken,
            });

            const { data } = response.data;
            if (data && data.token) {
              useAuth.getState().setToken(data.token);
              originalRequest.headers.Authorization = `Bearer ${data.token}`;
              return instance(originalRequest);
            }
          }
        } catch (refreshError) {
          useAuth.getState().clear();
          queryClient.clear();
          showErrorToast('لطفا دوباره وارد شوید');
          window.location.href = '/login';
          return Promise.reject(refreshError);
        }
      }

      // خطای 403 - Forbidden
      if (error?.response?.status === 403) {
        showErrorToast('شما به این بخش دسترسی ندارید');
      }

      // خطای 404 - Not Found
      if (error?.response?.status === 404) {
        showErrorToast('اطلاعات مورد نظر یافت نشد');
      }

      // خطای 409 - Conflict
      if (error?.response?.status === 409) {
        showErrorToast('این اطلاعات قبلا ثبت شده است');
      }

      // خطای 422 - Validation Error
      if (error?.response?.status === 422) {
        const errors = error?.response?.data?.errors;
        if (errors) {
          const firstError = Object.values(errors)[0];
          if (Array.isArray(firstError)) {
            showErrorToast(firstError[0] as string);
          }
        } else {
          showErrorToast('اطلاعات وارد شده نامعتبر است');
        }
      }

      // خطای 500 - Internal Server Error
      if (error?.response?.status === 500) {
        showErrorToast('خطای سرور. لطفا بعدا تلاش کنید');
      }

      // خطای شبکه
      if (error.code === 'ERR_NETWORK') {
        showErrorToast('خطا در اتصال به سرور. اینترنت خود را بررسی کنید');
      }

      // خطای Bad Response
      if (error.code === 'ERR_BAD_RESPONSE') {
        showErrorToast('پاسخ نامعتبر از سرور');
      }

      return Promise.reject(error);
    }
  );

  return instance;
};

// Export instance اصلی
const appHttp = createApiInstance();

// Export تابع سفارشی برای ساخت instance با تنظیمات دلخواه
export const createCustomInstance = (config?: any) => {
  const instance = createApiInstance();
  
  if (config) {
    Object.assign(instance.defaults, config);
  }
  
  return instance;
};

export { appHttp };
export default appHttp;