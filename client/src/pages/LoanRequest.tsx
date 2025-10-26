import { useState } from "react";
import { useNavigate } from "react-router";
import { useCreateLoan, useUserLoans } from "../hooks/useLoans";
import type { LoanRequestDto } from "../types";
import { getLoanStatusBadge } from "../utils/loanStatus";

interface LoanFormData {
  amount: string;
  term_months: string;
  interest_rate: string;
  start_date: string;
}

export default function LoanRequest() {
  const navigate = useNavigate();
  const createLoanMutation = useCreateLoan();
  const { data: userLoans, isLoading: loansLoading } = useUserLoans();
  const [showForm, setShowForm] = useState(false);
  const [formData, setFormData] = useState<LoanFormData>({
    amount: "",
    term_months: "",
    interest_rate: "",
    start_date: new Date().toISOString().split('T')[0],
  });

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    const loanData: LoanRequestDto = {
      amount: parseInt(formData.amount),
      term_months: parseInt(formData.term_months),
      interest_rate: formData.interest_rate ? parseFloat(formData.interest_rate) : undefined,
      start_date: formData.start_date,
    };

    createLoanMutation.mutate(loanData, {
      onSuccess: () => {
        setShowForm(false);
        setFormData({
          amount: "",
          term_months: "",
          interest_rate: "",
          start_date: new Date().toISOString().split('T')[0],
        });
      }
    });
  };


  return (
    <div>
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">درخواست وام</h1>
          <p className="text-gray-600">درخواست وام جدید بدهید و وضعیت وام‌های خود را پیگیری کنید</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Loan Form */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow-md p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-semibold text-gray-900">
                  {showForm ? 'فرم درخواست وام' : 'درخواست وام جدید'}
                </h2>
                {!showForm && (
                  <button
                    onClick={() => setShowForm(true)}
                    className="btn btn-primary"
                  >
                    درخواست وام جدید
                  </button>
                )}
              </div>

              {showForm && (
                <form onSubmit={handleSubmit} className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      مبلغ وام (تومان)
                    </label>
                    <input
                      type="number"
                      name="amount"
                      value={formData.amount}
                      onChange={handleInputChange}
                      required
                      min="1000000"
                      step="100000"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="مبلغ وام را وارد کنید"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      مدت وام (ماه)
                    </label>
                    <input
                      type="number"
                      name="term_months"
                      value={formData.term_months}
                      onChange={handleInputChange}
                      required
                      min="1"
                      max="60"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="مدت وام را وارد کنید"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      نرخ بهره (درصد) - اختیاری
                    </label>
                    <input
                      type="number"
                      name="interest_rate"
                      value={formData.interest_rate}
                      onChange={handleInputChange}
                      min="0"
                      max="50"
                      step="0.1"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="نرخ بهره (اختیاری)"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      تاریخ شروع
                    </label>
                    <input
                      type="date"
                      name="start_date"
                      value={formData.start_date}
                      onChange={handleInputChange}
                      required
                      min={new Date().toISOString().split('T')[0]}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div className="flex gap-4">
                    <button
                      type="submit"
                      disabled={createLoanMutation.isPending}
                      className="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {createLoanMutation.isPending ? 'در حال ثبت...' : 'ثبت درخواست وام'}
                    </button>
                    <button
                      type="button"
                      onClick={() => setShowForm(false)}
                      className="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400"
                    >
                      انصراف
                    </button>
                  </div>
                </form>
              )}

              {!showForm && (
                <div className="text-center py-8">
                  <div className="text-gray-400 mb-4">
                    <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </div>
                  <p className="text-gray-500">برای درخواست وام جدید روی دکمه بالا کلیک کنید</p>
                </div>
              )}
            </div>
          </div>

          {/* Loan List */}
          <div className="lg:col-span-2">
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">وام‌های من</h2>
              
              {loansLoading ? (
                <div className="flex justify-center py-8">
                  <span className="loading loading-spinner loading-lg"></span>
                </div>
              ) : userLoans && userLoans.length > 0 ? (
                <div className="overflow-x-auto">
                  <table className="table table-zebra w-full">
                    <thead>
                      <tr>
                        <th>مبلغ</th>
                        <th>مدت</th>
                        <th>نرخ بهره</th>
                        <th>تاریخ درخواست</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                      </tr>
                    </thead>
                    <tbody>
                      {userLoans.map((loan) => {
                        const status = getLoanStatusBadge(loan.status);
                        return (
                          <tr key={loan.id}>
                            <td className="font-medium">
                              {loan.amount.toLocaleString()} تومان
                            </td>
                            <td>{loan.term_months} ماه</td>
                            <td>{loan.interest_rate}%</td>
                            <td>
                              {new Date(loan.created_at).toLocaleDateString('fa-IR')}
                            </td>
                            <td>
                              <span className={`badge ${status.class}`}>
                                {status.text}
                              </span>
                            </td>
                            <td>
                              <button
                                onClick={() => navigate(`/loan-details/${loan.id}`)}
                                className="btn btn-sm btn-outline"
                              >
                                جزئیات
                              </button>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="text-center py-8">
                  <div className="text-gray-400 mb-4">
                    <svg className="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <p className="text-gray-500">هنوز وامی درخواست نکرده‌اید</p>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
  );
}