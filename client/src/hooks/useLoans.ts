import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast, showErrorToast } from '../lib/toast';
import type { Loan, LoanRequestDto, LoanApprovalDto } from '../types';

// Hook for getting user's loans
export const useUserLoans = () => {
  return useQuery({
    queryKey: ['user-loans'],
    queryFn: async () => {
      const response = await appHttp.get('/loans');
      return response.data.data as Loan[];
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

// Hook for getting all loans (admin only)
export const useAllLoans = (enabled: boolean = true) => {
  return useQuery({
    queryKey: ['all-loans'],
    queryFn: async () => {
      const response = await appHttp.get('/admin/loans');
      return response.data.data as Loan[];
    },
    enabled,
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
};

// Hook for getting specific loan details
export const useLoan = (loanId: number) => {
  return useQuery({
    queryKey: ['loan', loanId],
    queryFn: async () => {
      const response = await appHttp.get(`/loans/${loanId}`);
      return response.data.data as Loan;
    },
    enabled: !!loanId,
    staleTime: 0, // Always fetch fresh data
    refetchOnWindowFocus: true, // Refetch when window regains focus
  });
};

// Hook for creating loan request
export const useCreateLoan = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (loanData: LoanRequestDto) => {
      const response = await appHttp.post('/loans', loanData);
      return response.data.data as Loan;
    },
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['user-loans'] });
      showSuccessToast('درخواست وام با موفقیت ثبت شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در ثبت درخواست وام');
    },
  });
};

// Hook for approving/rejecting loan (admin only)
export const useLoanApproval = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ loanId, approvalData }: { loanId: number; approvalData: LoanApprovalDto }) => {
      const response = await appHttp.post(`/admin/loans/${loanId}/approve`, approvalData);
      return response.data.data as Loan;
    },
    onSuccess: (data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['all-loans'] });
      queryClient.invalidateQueries({ queryKey: ['user-loans'] });
      queryClient.invalidateQueries({ queryKey: ['loan', variables.loanId] });
      
      const action = variables.approvalData.action === 'approve' ? 'تایید' : 'رد';
      showSuccessToast(`وام با موفقیت ${action} شد`);
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در پردازش درخواست');
    },
  });
};

// Hook for loan statistics (admin dashboard)
export const useLoanStats = (enabled: boolean = true) => {
  return useQuery({
    queryKey: ['loan-stats'],
    queryFn: async () => {
      const response = await appHttp.get('/admin/loans/stats');
      return response.data.data;
    },
    enabled,
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
};

// Hook for loan payments
export const useLoanPayments = (loanId: number) => {
  return useQuery({
    queryKey: ['loan-payments', loanId],
    queryFn: async () => {
      const response = await appHttp.get(`/loans/${loanId}/payments`);
      return response.data.data;
    },
    enabled: !!loanId,
  });
};

// Hook for creating payment
export const useCreatePayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ loanId, amount, method }: { loanId: number; amount: number; method: string }) => {
      const response = await appHttp.post(`/loans/${loanId}/payments`, {
        amount,
        method,
      });
      return response.data.data;
    },
    onSuccess: (data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['loan-payments', variables.loanId] });
      queryClient.invalidateQueries({ queryKey: ['user-loans'] });
      queryClient.invalidateQueries({ queryKey: ['loan', variables.loanId] });
      showSuccessToast('پرداخت با موفقیت ثبت شد');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در ثبت پرداخت');
    },
  });
};
