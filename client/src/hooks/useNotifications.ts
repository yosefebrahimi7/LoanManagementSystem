import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast, showErrorToast } from '../lib/toast';

// Hook for getting user notifications
export const useNotifications = (page = 1, limit = 10) => {
  return useQuery({
    queryKey: ['notifications', page, limit],
    queryFn: async () => {
      const response = await appHttp.get(`/notifications?page=${page}&limit=${limit}`);
      return response.data.data;
    },
    staleTime: 30 * 1000, // 30 seconds
  });
};

// Hook for getting unread notifications count
export const useUnreadNotificationsCount = () => {
  return useQuery({
    queryKey: ['unread-notifications-count'],
    queryFn: async () => {
      const response = await appHttp.get('/notifications/unread-count');
      return response.data.data.count;
    },
    staleTime: 30 * 1000, // 30 seconds
    refetchInterval: 30 * 1000, // Refetch every 30 seconds
  });
};

// Hook for marking notification as read
export const useMarkNotificationAsRead = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (notificationId: string) => {
      const response = await appHttp.patch(`/notifications/${notificationId}/read`);
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
      queryClient.invalidateQueries({ queryKey: ['unread-notifications-count'] });
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در به‌روزرسانی اعلان');
    },
  });
};

// Hook for marking all notifications as read
export const useMarkAllNotificationsAsRead = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async () => {
      const response = await appHttp.patch('/notifications/mark-all-read');
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
      queryClient.invalidateQueries({ queryKey: ['unread-notifications-count'] });
      showSuccessToast('همه اعلان‌ها به عنوان خوانده شده علامت‌گذاری شدند');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در به‌روزرسانی اعلان‌ها');
    },
  });
};

// Hook for deleting notification
export const useDeleteNotification = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (notificationId: string) => {
      const response = await appHttp.delete(`/notifications/${notificationId}`);
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['notifications'] });
      queryClient.invalidateQueries({ queryKey: ['unread-notifications-count'] });
      showSuccessToast('اعلان حذف شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در حذف اعلان');
    },
  });
};
