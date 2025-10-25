import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useNavigate } from 'react-router';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast, showErrorToast } from '../lib/toast';
import useAuthStore from '../stores/auth';
import type { LoginDto, RegisterDto } from '../types';

// Hook for login
export const useLogin = () => {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { setAuth } = useAuthStore();

  return useMutation({
    mutationFn: async (credentials: LoginDto) => {
      const response = await appHttp.post('/auth/login', credentials);
      return response.data.data;
    },
    onSuccess: (data) => {
      if (data.user && data.token) {
        setAuth(data.user, data.token, data.refreshToken || data.token);
        showSuccessToast(data.message || 'ورود با موفقیت انجام شد');
        navigate('/dashboard');
      }
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در ورود');
    },
  });
};

// Hook for register
export const useRegister = () => {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { setAuth } = useAuthStore();

  return useMutation({
    mutationFn: async (userData: RegisterDto) => {
      const response = await appHttp.post('/auth/register', {
        firstName: userData.firstName,
        lastName: userData.lastName,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.password,
      });
      return response.data.data;
    },
    onSuccess: (data) => {
      if (data.user && data.token) {
        setAuth(data.user, data.token, data.refreshToken || data.token);
        showSuccessToast(data.message || 'ثبت نام با موفقیت انجام شد');
        navigate('/dashboard');
      }
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در ثبت نام');
    },
  });
};

// Hook for logout
export const useLogout = () => {
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { clear } = useAuthStore();

  return useMutation({
    mutationFn: async () => {
      const response = await appHttp.post('/auth/logout');
      return response.data;
    },
    onSuccess: () => {
      clear();
      queryClient.clear();
      showSuccessToast('خروج با موفقیت انجام شد');
      navigate('/login');
    },
    onError: (error: any) => {
      // Even if API call fails, clear local state
      console.error('Logout API error:', error);
      clear();
      queryClient.clear();
      showSuccessToast('خروج با موفقیت انجام شد');
      navigate('/login');
    },
  });
};

// Hook for refresh token
export const useRefreshToken = () => {
  const { setToken, setRefreshToken } = useAuthStore();

  return useMutation({
    mutationFn: async () => {
      const response = await appHttp.post('/auth/refresh');
      return response.data.data;
    },
    onSuccess: (data) => {
      if (data.token) {
        setToken(data.token);
        if (data.refreshToken) {
          setRefreshToken(data.refreshToken);
        }
      }
    },
    onError: (error: any) => {
      console.error('Token refresh failed:', error);
      // Clear auth state on refresh failure
      useAuthStore.getState().clear();
    },
  });
};

// Hook for getting current user
export const useMe = () => {
  const { user, isAuthenticated } = useAuthStore();

  return {
    user,
    isAuthenticated: isAuthenticated(),
  };
};

// Hook for password reset request
export const usePasswordResetRequest = () => {
  return useMutation({
    mutationFn: async (email: string) => {
      const response = await appHttp.post('/auth/password-reset-request', { email });
      return response.data;
    },
    onSuccess: (data) => {
      showSuccessToast(data.message || 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در ارسال درخواست بازیابی رمز عبور');
    },
  });
};

// Hook for password reset
export const usePasswordReset = () => {
  const navigate = useNavigate();

  return useMutation({
    mutationFn: async ({ token, password, password_confirmation }: { 
      token: string; 
      password: string; 
      password_confirmation: string; 
    }) => {
      const response = await appHttp.post('/auth/password-reset', {
        token,
        password,
        password_confirmation,
      });
      return response.data;
    },
    onSuccess: (data) => {
      showSuccessToast(data.message || 'رمز عبور با موفقیت تغییر کرد');
      navigate('/login');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در تغییر رمز عبور');
    },
  });
};

// Hook for email verification
export const useEmailVerification = () => {
  return useMutation({
    mutationFn: async (token: string) => {
      const response = await appHttp.post('/auth/verify-email', { token });
      return response.data;
    },
    onSuccess: (data) => {
      showSuccessToast(data.message || 'ایمیل با موفقیت تایید شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در تایید ایمیل');
    },
  });
};

// Hook for resend email verification
export const useResendEmailVerification = () => {
  return useMutation({
    mutationFn: async () => {
      const response = await appHttp.post('/auth/resend-verification');
      return response.data;
    },
    onSuccess: (data) => {
      showSuccessToast(data.message || 'ایمیل تایید دوباره ارسال شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در ارسال مجدد ایمیل تایید');
    },
  });
};
