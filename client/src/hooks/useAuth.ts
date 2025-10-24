import { useState } from 'react';
import { useNavigate } from 'react-router';
import useAuthStore from '../stores/auth';
import { showSuccessToast } from '../lib/toast';
import { showApiError } from '../lib/errorHandler';
import appHttp from '../lib/appHttp';

export const useAuth = () => {
  const navigate = useNavigate();
  const { setAuth, user, isAuthenticated } = useAuthStore();
  const [isLoading, setIsLoading] = useState(false);

  const login = async (credentials: { email: string; password: string }) => {
    setIsLoading(true);
    try {
      const { data } = await appHttp.post('/auth/login', credentials);
      
      if (data.success && data.data) {
        setAuth(data.data.user, data.data.token, data.data.refreshToken);
        showSuccessToast(data.message);
        navigate('/dashboard');
        return { success: true };
      } else {
        showApiError({ response: { data: { message: 'خطا در ورود' } } });
        return { success: false };
      }
    } catch (error: any) {
      showApiError(error);
      return { success: false };
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (userData: {
    firstName: string;
    lastName: string;
    email: string;
    password: string;
    confirmPassword: string;
  }) => {
    setIsLoading(true);
    try {
      const { data } = await appHttp.post('/auth/register', {
        firstName: userData.firstName,
        lastName: userData.lastName,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.confirmPassword,
      });
      
      if (data.success && data.data) {
        setAuth(data.data.user, data.data.token, data.data.refreshToken);
        showSuccessToast(data.message);
        navigate('/dashboard');
        return { success: true };
      } else {
        showApiError({ response: { data: { message: 'خطا در ثبت نام' } } });
        return { success: false };
      }
    } catch (error: any) {
      showApiError(error);
      return { success: false };
    } finally {
      setIsLoading(false);
    }
  };

  return {
    login,
    register,
    user,
    isAuthenticated: isAuthenticated(),
    isLoading,
  };
};
