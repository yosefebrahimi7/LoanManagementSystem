import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast, showErrorToast } from '../lib/toast';

// Hook for getting system settings
export const useSettings = () => {
  return useQuery({
    queryKey: ['settings'],
    queryFn: async () => {
      const response = await appHttp.get('/settings');
      return response.data.data;
    },
    staleTime: 10 * 60 * 1000, // 10 minutes
  });
};

// Hook for updating settings (admin only)
export const useUpdateSettings = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (settings: Record<string, any>) => {
      const response = await appHttp.put('/admin/settings', settings);
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['settings'] });
      showSuccessToast('تنظیمات با موفقیت به‌روزرسانی شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در به‌روزرسانی تنظیمات');
    },
  });
};

// Hook for getting specific setting value
export const useSetting = (key: string) => {
  return useQuery({
    queryKey: ['setting', key],
    queryFn: async () => {
      const response = await appHttp.get(`/settings/${key}`);
      return response.data.data;
    },
    staleTime: 10 * 60 * 1000, // 10 minutes
  });
};
