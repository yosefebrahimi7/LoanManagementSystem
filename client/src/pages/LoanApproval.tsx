import { useState } from "react";
import useAuth from "../stores/auth";
import { useAllLoans, useLoanApproval } from "../hooks/useLoans";
import type { LoanApprovalDto } from "../types";
import { getLoanStatusBadge } from "../utils/loanStatus";
import LoanDetailsDialog from "../components/LoanDetailsDialog";

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
          <p className="text-gray-500">هیچ وامی یافت نشد</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          {loans.map((loan) => {
            const status = getLoanStatusBadge(loan.status);
            return (
              <div key={loan.id} className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h3 className="text-lg font-semibold text-gray-900">وام #{loan.id}</h3>
                    <p className="text-sm text-gray-500">{loan.user?.firstName} {loan.user?.lastName}</p>
                  </div>
                  <span className={`badge ${status.class}`}>{status.text}</span>
                </div>

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
                </div>

                <div className="flex gap-2">
                  <button onClick={() => openModal(loan)} className="btn btn-sm btn-outline flex-1">
                    مشاهده جزئیات
                  </button>
                </div>
              </div>
            );
          })}
        </div>
      )}

      <LoanDetailsDialog
        loan={selectedLoan}
        isOpen={showModal}
        onClose={() => {
          setShowModal(false);
          setRejectionReason("");
        }}
        onApprove={(loanId) => handleApproval(loanId, 'approve')}
        onReject={(loanId, reason) => {
          setRejectionReason(reason);
          handleApproval(loanId, 'reject');
        }}
        isProcessing={loanApprovalMutation.isPending}
      />
    </div>
  );
}
