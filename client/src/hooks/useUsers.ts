import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast } from '../lib/toast';
import type { User } from '../types';

export const useUsers = () => {
  return useQuery({
    queryKey: ['users'],
    queryFn: async () => {
      const { data } = await appHttp.get<{ data: User[] }>('/users');
      return data.data;
    },
  });
};

export const useUser = (id: number) => {
  return useQuery({
    queryKey: ['user', id],
    queryFn: async () => {
      const { data } = await appHttp.get<{ data: User }>(`/users/${id}`);
      return data.data;
    },
    enabled: !!id,
  });
};

export const useCreateUser = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (userData: Omit<User, 'id'>) => {
      const { data } = await appHttp.post<{ data: User }>('/users', userData);
      return data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      showSuccessToast('کاربر با موفقیت ایجاد شد');
    },
    onError: (error: any) => {
      console.error('Error creating user:', error);
    },
  });
};

export const useUpdateUser = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ id, ...userData }: User) => {
      const { data } = await appHttp.put<{ data: User }>(`/users/${id}`, userData);
      return data.data;
    },
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      queryClient.invalidateQueries({ queryKey: ['user', data.id] });
      showSuccessToast('کاربر با موفقیت بروزرسانی شد');
    },
    onError: (error: any) => {
      console.error('Error updating user:', error);
    },
  });
};

export const useDeleteUser = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number) => {
      await appHttp.delete(`/users/${id}`);
      return id;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      showSuccessToast('کاربر با موفقیت حذف شد');
    },
    onError: (error: any) => {
      console.error('Error deleting user:', error);
    },
  });
};

export const useToggleUserStatus = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (id: number) => {
      const { data } = await appHttp.patch<{ data: User }>(`/users/${id}/toggle-status`);
      return data.data;
    },
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['users'] });
      showSuccessToast(
        data.isActive 
          ? 'کاربر فعال شد' 
          : 'کاربر غیرفعال شد'
      );
    },
    onError: (error: any) => {
      console.error('Error toggling user status:', error);
    },
  });
};

