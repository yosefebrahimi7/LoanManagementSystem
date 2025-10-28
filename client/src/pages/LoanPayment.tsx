import { useState } from "react";
import { useParams, useNavigate } from "react-router";
import { useLoan } from "../hooks/useLoans";
import { useInitiatePayment } from "../hooks/usePayments";
import useAuth from "../stores/auth";
import { getLoanStatusBadge } from "../utils/loanStatus";
import { useQueryClient } from "@tanstack/react-query";
import PaymentConfirmDialog from "../components/PaymentConfirmDialog";

export default function LoanPayment() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const { data: loan, isLoading, error, refetch } = useLoan(Number(id));
  const initiatePayment = useInitiatePayment();
  const [selectedSchedule, setSelectedSchedule] = useState<any>(null);
  const [showConfirmDialog, setShowConfirmDialog] = useState(false);

  const handleRefresh = () => {
    queryClient.invalidateQueries({ queryKey: ['loan', id] });
    refetch();
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <span className="loading loading-spinner loading-lg"></span>
      </div>
    );
  }

  if (error || !loan) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600 mb-4">وام یافت نشد</h1>
          <p className="text-gray-600 mb-4">وام مورد نظر یافت نشد یا شما به آن دسترسی ندارید</p>
          <button
            onClick={() => navigate('/dashboard')}
            className="btn btn-primary"
          >
            بازگشت به داشبورد
          </button>
        </div>
      </div>
    );
  }

  // Check if user owns this loan
  if (loan.user_id !== user?.id) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600 mb-4">دسترسی غیرمجاز</h1>
          <p className="text-gray-600 mb-4">شما به این وام دسترسی ندارید</p>
          <button
            onClick={() => navigate('/dashboard')}
            className="btn btn-primary"
          >
            بازگشت به داشبورد
          </button>
        </div>
      </div>
    );
  }

  const status = getLoanStatusBadge(loan.status);

  // Get pending/overdue schedules
  const paymentSchedules = loan.schedules?.filter(
    (schedule: any) => schedule.status !== 'paid'
  ) || [];

  const handlePayment = (scheduleId: number) => {
    const schedule = paymentSchedules.find(s => s.id === scheduleId);
    if (schedule) {
      setSelectedSchedule(schedule);
      setShowConfirmDialog(true);
    }
  };

  const handleConfirmPayment = () => {
    if (!selectedSchedule) return;
    
    const remainingAmount = selectedSchedule.amount_due - selectedSchedule.paid_amount;
    initiatePayment.mutate({ 
      loanId: loan.id, 
      scheduleId: selectedSchedule.id,
      amount: remainingAmount
    }, {
      onSuccess: () => {
        setShowConfirmDialog(false);
        setSelectedSchedule(null);
      }
    });
  };

  return (
    <div>
      {/* Header */}
      <div className="mb-8">
        <button
          onClick={() => navigate(-1)}
          className="btn btn-ghost mb-4"
        >
          ← بازگشت
        </button>
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">
              پرداخت اقساط وام #{loan.id}
            </h1>
            <p className="text-gray-600">پرداخت اقساط وام خود</p>
          </div>
          <button
            onClick={handleRefresh}
            className="btn btn-ghost btn-sm"
            disabled={isLoading}
          >
            {isLoading ? (
              <span className="loading loading-spinner loading-sm"></span>
            ) : (
              '🔄 به‌روزرسانی'
            )}
          </button>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main Info */}
        <div className="lg:col-span-2 space-y-6">
          {/* Loan Summary */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold mb-4">خلاصه وام</h2>
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">مبلغ وام</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.amount.toLocaleString()} تومان
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">وضعیت</label>
                <span className={`badge badge-lg ${status.class}`}>
                  {status.text}
                </span>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">مبلغ باقیمانده</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.remaining_balance ? loan.remaining_balance.toLocaleString() : loan.amount.toLocaleString()} تومان
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">پرداخت ماهانه</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.monthly_payment ? loan.monthly_payment.toLocaleString() : 'محاسبه نشده'} تومان
                </p>
              </div>
            </div>
          </div>

          {/* Payment Schedules */}
          {paymentSchedules.length > 0 ? (
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold mb-4">اقساط قابل پرداخت</h2>
              <div className="overflow-x-auto">
                <table className="table table-zebra w-full">
                  <thead>
                    <tr>
                      <th>قسط</th>
                      <th>مبلغ کل</th>
                      <th>اصل</th>
                      <th>بهره</th>
                      <th>تاریخ سررسید</th>
                      <th>وضعیت</th>
                      <th>عملیات</th>
                    </tr>
                  </thead>
                  <tbody>
                    {paymentSchedules.map((schedule: any) => (
                      <tr key={schedule.id}>
                        <td className="font-medium">{schedule.installment_number}</td>
                        <td>{schedule.amount_due.toLocaleString()} تومان</td>
                        <td>{schedule.principal_amount.toLocaleString()} تومان</td>
                        <td>{schedule.interest_amount.toLocaleString()} تومان</td>
                        <td>{new Date(schedule.due_date).toLocaleDateString('fa-IR')}</td>
                        <td>
                          <span className={`badge ${
                            schedule.status === 'paid' ? 'badge-success' :
                            schedule.status === 'overdue' ? 'badge-error' :
                            'badge-warning'
                          }`}>
                            {schedule.status === 'paid' ? 'پرداخت شده' :
                             schedule.status === 'overdue' ? 'سررسید گذشته' :
                             'در انتظار پرداخت'}
                          </span>
                        </td>
                        <td>
                          <button
                            onClick={() => handlePayment(schedule.id)}
                            disabled={initiatePayment.isPending}
                            className="btn btn-sm btn-primary"
                          >
                            {initiatePayment.isPending ? 'در حال هدایت...' : 'پرداخت'}
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <div className="bg-white rounded-lg shadow-md p-6">
              <div className="text-center py-8">
                <div className="text-green-500 mb-4">
                  <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                  تمام اقساط پرداخت شده است
                </h3>
                <p className="text-gray-600">
                  تمام اقساط این وام پرداخت شده است
                </p>
              </div>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* Loan Info */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold mb-4">اطلاعات وام</h2>
            <div className="space-y-3">
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">مدت وام</label>
                <p className="font-semibold text-gray-900">
                  {loan.term_months} ماه
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">نرخ بهره</label>
                <p className="font-semibold text-gray-900">
                  {loan.interest_rate}%
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">تاریخ شروع</label>
                <p className="font-semibold text-gray-900">
                  {loan.start_date ? new Date(loan.start_date).toLocaleDateString('fa-IR') : 'تعیین نشده'}
                </p>
              </div>
            </div>
          </div>

          {/* Payment Info */}
          <div className="bg-blue-50 rounded-lg shadow-md p-6">
            <h3 className="text-lg font-semibold text-blue-900 mb-2">
              اطلاعات پرداخت
            </h3>
            <div className="space-y-2 text-sm">
              <p className="text-blue-800">
                • پرداخت از طریق درگاه امن زرین‌پال انجام می‌شود
              </p>
              <p className="text-blue-800">
                • پس از تایید پرداخت، اطلاعات به روز می‌شود
              </p>
              <p className="text-blue-800">
                • امکان پرداخت جزئی وجود دارد
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Payment Confirm Dialog */}
      <PaymentConfirmDialog
        schedule={selectedSchedule}
        isOpen={showConfirmDialog}
        onClose={() => {
          setShowConfirmDialog(false);
          setSelectedSchedule(null);
        }}
        onConfirm={handleConfirmPayment}
        isProcessing={initiatePayment.isPending}
      />
    </div>
  );
}

