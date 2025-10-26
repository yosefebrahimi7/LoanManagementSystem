import { useNavigate, useSearchParams } from "react-router";
import { usePaymentStatus } from "../hooks/usePayments";
import { useQueryClient } from "@tanstack/react-query";
import { useEffect } from "react";

export default function PaymentSuccess() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const paymentId = searchParams.get('payment_id');
  const queryClient = useQueryClient();

  const { data: payment, isLoading } = usePaymentStatus(paymentId ? Number(paymentId) : 0);

  // Invalidate queries to refetch data
  useEffect(() => {
    queryClient.invalidateQueries({ queryKey: ['loan'] });
    queryClient.invalidateQueries({ queryKey: ['user-loans'] });
  }, [queryClient]);

  // Auto-redirect after 3 seconds if payment has loan_id
  useEffect(() => {
    if (payment?.loan_id && !isLoading) {
      const timer = setTimeout(() => {
        queryClient.invalidateQueries({ queryKey: ['loan', payment.loan_id] });
        queryClient.invalidateQueries({ queryKey: ['user-loans'] });
        navigate(`/loan-payment/${payment.loan_id}`);
      }, 3000);
      
      return () => clearTimeout(timer);
    }
  }, [payment, isLoading, queryClient, navigate]);

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
          پرداخت با موفقیت انجام شد
        </h1>

        <p className="text-gray-600 mb-6">
          پرداخت شما با موفقیت ثبت شد و اطلاعات به روز شد.
        </p>

        {isLoading ? (
          <span className="loading loading-spinner loading-md"></span>
        ) : payment && (
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <div className="space-y-2 text-sm text-right">
              <div className="flex justify-between">
                <span className="text-gray-600">شناسه پرداخت:</span>
                <span className="font-semibold">#{payment.id}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">مبلغ:</span>
                <span className="font-semibold">{payment.amount?.toLocaleString()} تومان</span>
              </div>
              <div className="flex justify-between">
                <span className="text-gray-600">وضعیت:</span>
                <span className="badge badge-success">موفق</span>
              </div>
            </div>
          </div>
        )}

        <div className="flex gap-4">
          {payment?.loan_id && (
            <button
              onClick={() => {
                queryClient.invalidateQueries({ queryKey: ['loan', payment.loan_id] });
                queryClient.invalidateQueries({ queryKey: ['user-loans'] });
                navigate(`/loan-payment/${payment.loan_id}`);
              }}
              className="btn btn-outline flex-1"
            >
              بازگشت به صفحه پرداخت
            </button>
          )}
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

