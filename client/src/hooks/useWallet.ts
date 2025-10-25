import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast, showErrorToast } from '../lib/toast';

// Hook for getting wallet balance
export const useWallet = () => {
  return useQuery({
    queryKey: ['wallet'],
    queryFn: async () => {
      const response = await appHttp.get('/wallet');
      return response.data.data;
    },
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
};

// Hook for getting wallet transactions
export const useWalletTransactions = (page = 1, limit = 10) => {
  return useQuery({
    queryKey: ['wallet-transactions', page, limit],
    queryFn: async () => {
      const response = await appHttp.get(`/wallet/transactions?page=${page}&limit=${limit}`);
      return response.data.data;
    },
    staleTime: 1 * 60 * 1000, // 1 minute
  });
};

// Hook for adding money to wallet
export const useAddToWallet = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ amount, method }: { amount: number; method: string }) => {
      const response = await appHttp.post('/wallet/add', {
        amount,
        method,
      });
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['wallet'] });
      queryClient.invalidateQueries({ queryKey: ['wallet-transactions'] });
      showSuccessToast('موجودی با موفقیت اضافه شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در اضافه کردن موجودی');
    },
  });
};

// Hook for withdrawing from wallet
export const useWithdrawFromWallet = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ amount, method }: { amount: number; method: string }) => {
      const response = await appHttp.post('/wallet/withdraw', {
        amount,
        method,
      });
      return response.data.data;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['wallet'] });
      queryClient.invalidateQueries({ queryKey: ['wallet-transactions'] });
      showSuccessToast('برداشت با موفقیت انجام شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در برداشت از موجودی');
    },
  });
};
