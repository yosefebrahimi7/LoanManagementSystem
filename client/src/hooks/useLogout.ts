import { useNavigate } from 'react-router';
import useAuth from '../stores/auth';
import { showSuccessToast } from '../lib/toast';
import appHttp from '../lib/appHttp';

export const useLogout = () => {
  const navigate = useNavigate();
  const { clear } = useAuth();

  const logout = async () => {
    try {
      // Call logout API
      await appHttp.post('/auth/logout');
    } catch (error) {
      // Even if API call fails, clear local state
      console.error('Logout API error:', error);
    } finally {
      // Always clear local state and redirect
      clear();
      showSuccessToast('خروج با موفقیت انجام شد');
      navigate('/login');
    }
  };

  return { logout };
};
