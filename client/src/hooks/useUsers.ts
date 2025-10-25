import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast } from '../lib/toast';
import type { User } from '../types';

export const useUsers = () => {
  return useQuery({
    queryKey: ['users'],
    queryFn: async () => {
      const { data } = await appHttp.get<User[]>('/users');
      return data;
    },
  });
};

export const useUser = (id: number) => {
  return useQuery({
    queryKey: ['user', id],
    queryFn: async () => {
      const { data } = await appHttp.get<User>(`/users/${id}`);
      return data;
    },
    enabled: !!id,
  });
};

export const useCreateUser = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (userData: Omit<User, 'id'>) => {
      const { data } = await appHttp.post<User>('/users', userData);
      return data;
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
      const { data } = await appHttp.put<User>(`/users/${id}`, userData);
      return data;
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
      const { data } = await appHttp.patch<User>(`/users/${id}/toggle`);
      return data;
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

