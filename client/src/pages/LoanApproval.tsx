import { useState } from "react";
import useAuth from "../stores/auth";
import { useAllLoans, useLoanApproval } from "../hooks/useLoans";
import type { LoanApprovalDto } from "../types";

export default function LoanApproval() {
  const { user } = useAuth();
  const { data: loans, isLoading: loading, refetch } = useAllLoans();
  const loanApprovalMutation = useLoanApproval();
  const [selectedLoan, setSelectedLoan] = useState<any>(null);
  const [showModal, setShowModal] = useState(false);
  const [rejectionReason, setRejectionReason] = useState("");

  const handleApproval = async (loanId: number, action: 'approve' | 'reject') => {
    const approvalData: LoanApprovalDto = {
      action,
      rejection_reason: action === 'reject' ? rejectionReason : undefined,
    };

    loanApprovalMutation.mutate({ loanId, approvalData }, {
      onSuccess: () => {
        setShowModal(false);
        setSelectedLoan(null);
        setRejectionReason("");
      }
    });
  };

  const openModal = (loan: any) => {
    setSelectedLoan(loan);
    setShowModal(true);
  };

  const getStatusBadge = (status: string) => {
    const statusMap = {
      pending: { text: 'در انتظار تایید', class: 'badge-warning' },
      approved: { text: 'تایید شده', class: 'badge-success' },
      rejected: { text: 'رد شده', class: 'badge-error' },
    };
    return statusMap[status as keyof typeof statusMap] || { text: status, class: 'badge-neutral' };
  };

  if (user?.roleName !== 'admin') {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600 mb-4">دسترسی غیرمجاز</h1>
          <p className="text-gray-600">شما به این بخش دسترسی ندارید</p>
        </div>
      </div>
    );
  }

  return (
    <div>
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">مدیریت وام‌ها</h1>
          <p className="text-gray-600">درخواست‌های وام را بررسی و تایید یا رد کنید</p>
        </div>

        {loading ? (
          <div className="flex justify-center py-8">
            <span className="loading loading-spinner loading-lg"></span>
          </div>
        ) : !loans || loans.length === 0 ? (
          <div className="text-center py-8">
            <div className="text-gray-400 mb-4">
              <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <p className="text-gray-500">هیچ وامی یافت نشد</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            {loans.map((loan) => {
              const status = getStatusBadge(loan.status);
              return (
                <div key={loan.id} className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                  {/* Loan Header */}
                  <div className="flex justify-between items-start mb-4">
                    <div>
                      <h3 className="text-lg font-semibold text-gray-900">
                        وام #{loan.id}
                      </h3>
                      <p className="text-sm text-gray-500">
                        {loan.user?.firstName} {loan.user?.lastName}
                      </p>
                    </div>
                    <span className={`badge ${status.class}`}>
                      {status.text}
                    </span>
                  </div>

                  {/* Loan Details */}
                  <div className="space-y-3 mb-6">
                    <div className="flex justify-between">
                      <span className="text-gray-600">مبلغ:</span>
                      <span className="font-medium">{loan.amount.toLocaleString()} تومان</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-600">مدت:</span>
                      <span className="font-medium">{loan.term_months} ماه</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-600">نرخ بهره:</span>
                      <span className="font-medium">{loan.interest_rate}%</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-gray-600">تاریخ درخواست:</span>
                      <span className="font-medium">
                        {new Date(loan.created_at).toLocaleDateString('fa-IR')}
                      </span>
                    </div>
                    {loan.start_date && (
                      <div className="flex justify-between">
                        <span className="text-gray-600">تاریخ شروع:</span>
                        <span className="font-medium">
                          {new Date(loan.start_date).toLocaleDateString('fa-IR')}
                        </span>
                      </div>
                    )}
                    {loan.rejection_reason && (
                      <div className="mt-3 p-3 bg-red-50 rounded-md">
                        <span className="text-sm text-red-600 font-medium">دلیل رد:</span>
                        <p className="text-sm text-red-600 mt-1">{loan.rejection_reason}</p>
                      </div>
                    )}
                  </div>

                  {/* Actions */}
                  <div className="flex gap-2">
                    <button
                      onClick={() => openModal(loan)}
                      className="btn btn-sm btn-outline flex-1"
                    >
                      مشاهده جزئیات
                    </button>
                    {loan.status === 'pending' && (
                      <button
                        onClick={() => handleApproval(loan.id, 'approve')}
                        disabled={loanApprovalMutation.isPending}
                        className="btn btn-sm btn-success"
                      >
                        تایید
                      </button>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}

        {/* Modal for Loan Details */}
        {showModal && selectedLoan && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
              <div className="flex justify-between items-center mb-6">
                <h2 className="text-xl font-semibold">جزئیات وام #{selectedLoan.id}</h2>
                <button
                  onClick={() => setShowModal(false)}
                  className="btn btn-sm btn-circle btn-ghost"
                >
                  ✕
                </button>
              </div>

              <div className="space-y-4">
                {/* User Info */}
                <div className="bg-gray-50 p-4 rounded-lg">
                  <h3 className="font-semibold mb-2">اطلاعات متقاضی</h3>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <span className="text-gray-600">نام:</span>
                      <p className="font-medium">{selectedLoan.user?.firstName} {selectedLoan.user?.lastName}</p>
                    </div>
                    <div>
                      <span className="text-gray-600">ایمیل:</span>
                      <p className="font-medium">{selectedLoan.user?.email}</p>
                    </div>
                  </div>
                </div>

                {/* Loan Info */}
                <div className="bg-gray-50 p-4 rounded-lg">
                  <h3 className="font-semibold mb-2">اطلاعات وام</h3>
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <span className="text-gray-600">مبلغ:</span>
                      <p className="font-medium">{selectedLoan.amount.toLocaleString()} تومان</p>
                    </div>
                    <div>
                      <span className="text-gray-600">مدت:</span>
                      <p className="font-medium">{selectedLoan.term_months} ماه</p>
                    </div>
                    <div>
                      <span className="text-gray-600">نرخ بهره:</span>
                      <p className="font-medium">{selectedLoan.interest_rate}%</p>
                    </div>
                    <div>
                      <span className="text-gray-600">وضعیت:</span>
                      <span className={`badge ${getStatusBadge(selectedLoan.status).class}`}>
                        {getStatusBadge(selectedLoan.status).text}
                      </span>
                    </div>
                    <div>
                      <span className="text-gray-600">تاریخ درخواست:</span>
                      <p className="font-medium">
                        {new Date(selectedLoan.created_at).toLocaleDateString('fa-IR')}
                      </p>
                    </div>
                    {selectedLoan.start_date && (
                      <div>
                        <span className="text-gray-600">تاریخ شروع:</span>
                        <p className="font-medium">
                          {new Date(selectedLoan.start_date).toLocaleDateString('fa-IR')}
                        </p>
                      </div>
                    )}
                  </div>
                </div>

                {/* Rejection Reason */}
                {selectedLoan.rejection_reason && (
                  <div className="bg-red-50 p-4 rounded-lg">
                    <h3 className="font-semibold text-red-800 mb-2">دلیل رد</h3>
                    <p className="text-red-600">{selectedLoan.rejection_reason}</p>
                  </div>
                )}

                {/* Actions */}
                {selectedLoan.status === 'pending' && (
                  <div className="space-y-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        دلیل رد (در صورت رد کردن)
                      </label>
                      <textarea
                        value={rejectionReason}
                        onChange={(e) => setRejectionReason(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        rows={3}
                        placeholder="دلیل رد وام را وارد کنید..."
                      />
                    </div>
                    <div className="flex gap-4">
                      <button
                        onClick={() => handleApproval(selectedLoan.id, 'approve')}
                        disabled={loanApprovalMutation.isPending}
                        className="btn btn-success flex-1"
                      >
                        {loanApprovalMutation.isPending ? 'در حال تایید...' : 'تایید وام'}
                      </button>
                      <button
                        onClick={() => handleApproval(selectedLoan.id, 'reject')}
                        disabled={loanApprovalMutation.isPending}
                        className="btn btn-error flex-1"
                      >
                        {loanApprovalMutation.isPending ? 'در حال رد...' : 'رد وام'}
                      </button>
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </div>
  );
}