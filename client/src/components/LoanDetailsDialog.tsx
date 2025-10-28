import { useState } from "react";
import { type Loan } from "../types/index";
import { getLoanStatusBadge } from "../utils/loanStatus";

interface LoanDetailsDialogProps {
  loan: Loan | null;
  isOpen: boolean;
  onClose: () => void;
  onApprove?: (loanId: number) => void;
  onReject?: (loanId: number, reason: string) => void;
  isProcessing?: boolean;
}

function LoanDetailsDialog({
  loan,
  isOpen,
  onClose,
  onApprove,
  onReject,
  isProcessing = false,
}: LoanDetailsDialogProps) {
  const [showRejectDialog, setShowRejectDialog] = useState(false);
  const [rejectionReason, setRejectionReason] = useState("");

  if (!isOpen || !loan) return null;

  const status = getLoanStatusBadge(loan.status);
  const paidSchedules = loan.schedules?.filter(s => s.status === 'paid') || [];
  const pendingSchedules = loan.schedules?.filter(s => s.status !== 'paid') || [];

  const handleApprove = () => {
    if (onApprove) {
      onApprove(loan.id);
    }
  };

  const handleReject = () => {
    if (onReject) {
      onReject(loan.id, rejectionReason);
      setRejectionReason("");
      setShowRejectDialog(false);
    }
  };

  return (
    <div className="modal modal-open">
      <div className="modal-box max-w-3xl max-h-[90vh] p-0 flex flex-col">
        {/* Fixed Header */}
        <div className="flex items-center justify-between p-6 pb-4 border-b border-base-300">
          <div>
            <h3 className="text-2xl font-bold text-gray-900">جزئیات وام</h3>
            <p className="text-sm text-gray-500 mt-1">#{loan.id}</p>
          </div>
          <button onClick={onClose} className="btn btn-sm btn-circle btn-ghost">
            ✕
          </button>
        </div>

        {/* Scrollable Content */}
        <div className="flex-1 overflow-y-auto p-6 scrollbar-thin">
          {/* Status Badge */}
          <div className="text-center mb-6">
            <span className={`badge badge-lg ${status.class} px-6 py-3`}>
              {status.text}
            </span>
          </div>

          {loan.rejection_reason && (
            <div className="alert alert-error mb-6 shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" className="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <h3 className="font-bold">دلیل رد وام</h3>
                <div className="text-sm">{loan.rejection_reason}</div>
              </div>
            </div>
          )}

          {/* Loan Info Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div className="bg-primary/10 p-5 rounded-lg border-2 border-primary/30 shadow-sm">
              <span className="text-sm font-medium text-primary">مبلغ وام</span>
              <p className="text-2xl font-bold text-primary mt-2">{loan.amount.toLocaleString()} تومان</p>
            </div>

            <div className="bg-secondary/10 p-5 rounded-lg border-2 border-secondary/30 shadow-sm">
              <span className="text-sm font-medium text-secondary">مبلغ باقیمانده</span>
              <p className="text-2xl font-bold text-secondary mt-2">{loan.remaining_balance.toLocaleString()} تومان</p>
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">مدت وام</span>
              <p className="text-xl font-semibold text-gray-900 mt-2">{loan.term_months} ماه</p>
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">نرخ بهره</span>
              <p className="text-xl font-semibold text-gray-900 mt-2">{loan.interest_rate}%</p>
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">پرداخت ماهانه</span>
              <p className="text-xl font-semibold text-gray-900 mt-2">
                {loan.monthly_payment ? loan.monthly_payment.toLocaleString() : 'محاسبه نشده'} تومان
              </p>
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">تاریخ درخواست</span>
              <p className="text-lg font-semibold text-gray-900 mt-2">
                {new Date(loan.created_at).toLocaleDateString('fa-IR')}
              </p>
            </div>

            {loan.start_date && (
              <div className="bg-base-200 p-5 rounded-lg shadow-sm">
                <span className="text-sm font-medium text-gray-600">تاریخ شروع</span>
                <p className="text-lg font-semibold text-gray-900 mt-2">
                  {new Date(loan.start_date).toLocaleDateString('fa-IR')}
                </p>
              </div>
            )}

            {loan.approved_at && (
              <div className="bg-success/10 p-5 rounded-lg border-2 border-success/30 shadow-sm">
                <span className="text-sm font-medium text-success">تاریخ تایید</span>
                <p className="text-lg font-semibold text-success mt-2">
                  {new Date(loan.approved_at).toLocaleDateString('fa-IR')}
                </p>
              </div>
            )}
          </div>

          {/* User Info */}
          {loan.user && (
            <div className="bg-info/10 p-5 rounded-lg mb-6 border border-info/20">
              <h3 className="text-lg font-semibold text-info mb-3">اطلاعات متقاضی</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <span className="text-sm font-medium text-gray-600">نام و نام خانوادگی</span>
                  <p className="font-semibold text-gray-900 mt-1">
                    {loan.user.firstName} {loan.user.lastName}
                  </p>
                </div>
                <div>
                  <span className="text-sm font-medium text-gray-600">ایمیل</span>
                  <p className="font-semibold text-gray-900 mt-1">{loan.user.email}</p>
                </div>
              </div>
            </div>
          )}

          {/* Approved By */}
          {loan.approved_by_user && (
            <div className="bg-success/10 p-5 rounded-lg mb-6 border border-success/20">
              <h3 className="text-lg font-semibold text-success mb-3">تایید شده توسط</h3>
              <p className="font-semibold text-gray-900">
                {loan.approved_by_user.firstName} {loan.approved_by_user.lastName}
              </p>
            </div>
          )}

          {/* Schedules Summary */}
          {loan.schedules && loan.schedules.length > 0 && (
            <div className="bg-base-200 p-5 rounded-lg mb-6">
              <h3 className="text-lg font-semibold mb-4">خلاصه اقساط</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="text-center p-4 bg-white rounded-lg">
                  <span className="text-sm font-medium text-gray-600 block mb-2">کل اقساط</span>
                  <p className="text-2xl font-bold text-gray-900">{loan.schedules.length}</p>
                </div>
                <div className="text-center p-4 bg-white rounded-lg">
                  <span className="text-sm font-medium text-green-600 block mb-2">پرداخت شده</span>
                  <p className="text-2xl font-bold text-green-600">{paidSchedules.length}</p>
                </div>
                <div className="text-center p-4 bg-white rounded-lg">
                  <span className="text-sm font-medium text-orange-600 block mb-2">در انتظار</span>
                  <p className="text-2xl font-bold text-orange-600">{pendingSchedules.length}</p>
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Fixed Actions */}
        <div className="flex gap-3 justify-end p-6 pt-4 border-t border-base-300 bg-base-100">
          <button onClick={onClose} className="btn btn-lg btn-outline">
            بستن
          </button>
          {loan.status === 'pending' && onApprove && onReject && (
            <>
              <button
                onClick={() => setShowRejectDialog(true)}
                className="btn btn-lg btn-error"
                disabled={isProcessing}
              >
                رد وام
              </button>
              <button
                onClick={handleApprove}
                className="btn btn-lg btn-success"
                disabled={isProcessing}
              >
                {isProcessing ? (
                  <>
                    <span className="loading loading-spinner loading-sm"></span>
                    در حال تایید...
                  </>
                ) : (
                  'تایید وام'
                )}
              </button>
            </>
          )}
        </div>

        {/* Reject Reason Dialog */}
        {showRejectDialog && (
          <div className="modal modal-open">
            <div className="modal-box">
              <h3 className="text-lg font-bold mb-4">دلیل رد وام</h3>
              <textarea
                value={rejectionReason}
                onChange={(e) => setRejectionReason(e.target.value)}
                className="textarea textarea-bordered w-full mb-4"
                rows={3}
                placeholder="دلیل رد وام را وارد کنید..."
              />
              <div className="flex gap-3 justify-end">
                <button
                  onClick={() => {
                    setShowRejectDialog(false);
                    setRejectionReason("");
                  }}
                  className="btn btn-outline"
                >
                  انصراف
                </button>
                <button
                  onClick={handleReject}
                  className="btn btn-error"
                  disabled={!rejectionReason.trim() || isProcessing}
                >
                  {isProcessing ? 'در حال رد...' : 'رد وام'}
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
      <div className="modal-backdrop" onClick={onClose}></div>
    </div>
  );
}

export default LoanDetailsDialog;

