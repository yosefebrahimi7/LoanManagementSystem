import { useNavigate, useSearchParams } from "react-router";
import { useQueryClient } from "@tanstack/react-query";
import { useEffect } from "react";

export default function WalletRechargeSuccess() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const transactionId = searchParams.get('transaction_id');
  const queryClient = useQueryClient();

  // Invalidate queries to refetch wallet data
  useEffect(() => {
    queryClient.invalidateQueries({ queryKey: ['wallet'] });
    queryClient.invalidateQueries({ queryKey: ['wallet-transactions'] });
  }, [queryClient]);

  // Auto-redirect to dashboard after 3 seconds
  useEffect(() => {
    const timer = setTimeout(() => {
      queryClient.invalidateQueries({ queryKey: ['wallet'] });
      queryClient.invalidateQueries({ queryKey: ['wallet-transactions'] });
      navigate('/dashboard');
    }, 3000);
    
    return () => clearTimeout(timer);
  }, [queryClient, navigate]);

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-lg shadow-lg max-w-md w-full p-8 text-center">
        <div className="mb-6">
          <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
            <svg
              className="h-8 w-8 text-green-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
        </div>

        <h1 className="text-2xl font-bold text-gray-900 mb-4">
          شارژ کیف پول با موفقیت انجام شد
        </h1>

        <p className="text-gray-600 mb-6">
          مبلغ به کیف پول شما اضافه شد و موجودی به‌روزرسانی شده است.
        </p>

        {transactionId && (
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <div className="space-y-2 text-sm text-right">
              <div className="flex justify-between">
                <span className="text-gray-600">شناسه تراکنش:</span>
                <span className="font-semibold">#{transactionId}</span>
              </div>
            </div>
          </div>
        )}

        <div className="flex gap-4">
          <button
            onClick={() => navigate('/dashboard')}
            className="btn btn-primary flex-1"
          >
            داشبورد
          </button>
        </div>
      </div>
    </div>
  );
}

