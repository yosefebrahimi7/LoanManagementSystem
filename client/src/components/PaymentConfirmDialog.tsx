import { type LoanSchedule } from "../types/index";

interface PaymentConfirmDialogProps {
  schedule: LoanSchedule | null;
  isOpen: boolean;
  onClose: () => void;
  onConfirm: () => void;
  isProcessing: boolean;
}

function PaymentConfirmDialog({
  schedule,
  isOpen,
  onClose,
  onConfirm,
  isProcessing,
}: PaymentConfirmDialogProps) {
  if (!isOpen || !schedule) return null;

  const remainingAmount = schedule.amount_due - (schedule.paid_amount || 0);
  const isOverdue = new Date(schedule.due_date) < new Date();
  const daysPastDue = isOverdue
    ? Math.floor((new Date().getTime() - new Date(schedule.due_date).getTime()) / (1000 * 60 * 60 * 24))
    : 0;

  return (
    <div className="modal modal-open">
      <div className="modal-box max-w-2xl max-h-[90vh] p-0 flex flex-col">
        <div className="flex items-center justify中原 p-6 pb-4 border-b border-base-300">
          <h3 className="text-2xl font-bold text-gray-900">تأیید پرداخت قسط</h3>
          <button onClick={onClose} className="btn btn-sm btn-circle btn-ghost" disabled={isProcessing}>
            ✕
          </button>
        </div>

        <div className="flex-1 overflow-y-auto p-6 scrollbar-thin">
          <div className="text-center mb-6">
            <div className="inline-block px-8 py-3 bg-gradient-to-r from-primary to-secondary text-white rounded-full shadow-lg">
              <span className="text-base font-medium">قسط شماره</span>
              <span className="text-3xl font-bold mr-2">{schedule.installment_number}</span>
            </div>
          </div>

          {isOverdue && (
            <div className="alert alert-warning mb-6 shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" className="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span><strong>توجه:</strong> این قسط {daysPastDue} روز از سررسید گذشته است</span>
            </div>
          )}

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">مبلغ کل قسط</span>
              <p className="text-2xl font-bold text-gray-900 mt-2">{schedule.amount_due.toLocaleString()} تومان</p>
            </div>

            <div className="bg-primary/10 p-5 rounded-lg border-2 border-primary/30 shadow-sm">
              <span className="text-sm font-medium text-primary">مبلغ پرداخت</span>
              <p className="text-2xl font-bold text-primary mt-2">{remainingAmount.toLocaleString()} تومان</p>
              {schedule.paid_amount > 0 && (
                <p className="text-xs text-gray-500 mt-1">(پرداخت شده: {schedule.paid_amount.toLocaleString()} تومان)</p>
              )}
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">اصل مبلغ</span>
              <p className="text-xl font-semibold text-gray-900 mt-2">{schedule.principal_amount.toLocaleString()} تومان</p>
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">بهره</span>
              <p className="text-xl font-semibold text-gray-900 mt-2">{schedule.interest_amount.toLocaleString()} تومان</p>
            </div>

            <div className="bg-base-200 p-5 rounded-lg shadow-sm">
              <span className="text-sm font-medium text-gray-600">تاریخ سررسید</span>
              <p className={`text-lg font-semibold mt-2 ${isOverdue ? 'text-red-600' : 'text-gray-900'}`}>
                {new Date(schedule.due_date).toLocaleDateString('fa-IR', { year: 'numeric', month: 'long', day: 'numeric' })}
              </p>
              {isOverdue && daysPastDue > 0 && (
                <p className="text-xs text-red-500 mt-1">({daysPastDue} روز گذشته)</p>
              )}
            </div>

            {schedule.penalty_amount > 0 && (
              <div className="bg-error/10 p-5 rounded-lg border-2 border-error/30 shadow-sm">
                <span className="text-sm font-medium text-error">جریمه تأخیر</span>
                <p className="text-xl font-bold text-error mt-2">+{schedule.penalty_amount.toLocaleString()} تومان</p>
              </div>
            )}
          </div>

          <div className="bg-info/10 p-4 rounded-lg mb-6 border border-info/20">
            <p className="text-sm text-gray-700">
              پرداخت از طریق کیف پول شما انجام می‌شود. مبلغ به صورت خودکار از کیف پول کسر و به حساب ما اضافه می‌شود.
            </p>
          </div>
        </div>

        <div className="flex gap-3 justify-end p-6 pt-4 border-t border-base-300 bg-base-100">
          <button onClick={onClose} className="btn btn-lg btn-outline" disabled={isProcessing}>
            انصراف
          </button>
          <button onClick={onConfirm} className="btn btn-lg btn-primary" disabled={isProcessing}>
            {isProcessing ? (
              <>
                <span className="loading loading-spinner loading-sm"></span>
                در حال پردازش...
              </>
            ) : (
              'تأیید و پرداخت'
            )}
          </button>
        </div>
      </div>
      <div className="modal-backdrop" onClick={onClose}></div>
    </div>
  );
}

export default PaymentConfirmDialog;

