import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { appHttp } from '../lib/appHttp';
import { showSuccessToast, showErrorToast } from '../lib/toast';

export interface Payment {
  id: number;
  user_id: number;
  loan_id: number;
  loan_schedule_id: number | null;
  amount: number;
  payment_method: string;
  status: string;
  gateway_reference: string | null;
  gateway_response: any;
  notes: string | null;
  created_at: string;
  updated_at: string;
  loan?: any;
  schedule?: any;
}

export interface InitiatePaymentDto {
  schedule_id: number;
  amount?: number;
}

export interface PaymentResponse {
  success: boolean;
  payment_url?: string;
  authority?: string;
  payment_id?: number;
  message?: string;
}

// Hook for getting payment history
export const usePaymentHistory = () => {
  return useQuery({
    queryKey: ['payment-history'],
    queryFn: async () => {
      const response = await appHttp.get('/payment/history');
      return response.data.data;
    },
  });
};

// Hook for getting payment status
export const usePaymentStatus = (paymentId: number) => {
  return useQuery({
    queryKey: ['payment-status', paymentId],
    queryFn: async () => {
      const response = await appHttp.get(`/payment/status/${paymentId}`);
      return response.data.data; // Return the payment data directly
    },
    enabled: !!paymentId,
  });
};

// Hook for initiating payment
export const useInitiatePayment = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async ({ 
      loanId, 
      scheduleId, 
      amount 
    }: { 
      loanId: number; 
      scheduleId: number; 
      amount?: number 
    }) => {
      const response = await appHttp.post(`/payment/loans/${loanId}/initiate`, {
        schedule_id: scheduleId,
        amount,
      });
      
      const data = response.data as PaymentResponse;
      
      if (data.success && data.payment_url) {
        // Redirect to payment gateway
        window.location.href = data.payment_url;
        return data;
      }
      
      throw new Error(data.message || 'Failed to initiate payment');
    },
    onSuccess: () => {
      showSuccessToast('در حال هدایت به درگاه پرداخت...');
    },
    onError: (error: any) => {
      showErrorToast(error.response?.data?.message || 'خطا در شروع پرداخت');
    },
  });
};

