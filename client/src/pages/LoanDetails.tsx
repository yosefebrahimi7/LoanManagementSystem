import { useParams, useNavigate } from "react-router";
import { useLoan } from "../hooks/useLoans";
import useAuth from "../stores/auth";

export default function LoanDetails() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const { data: loan, isLoading, error } = useLoan(Number(id));

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

  // Check if user has access to this loan
  const isAdmin = user?.roleName === 'admin';
  const isOwner = user?.id === loan.user_id;
  
  if (!isAdmin && !isOwner) {
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

  const getStatusBadge = (status: string) => {
    const statusMap = {
      pending: { text: 'در انتظار تایید', class: 'badge-warning' },
      approved: { text: 'تایید شده', class: 'badge-success' },
      rejected: { text: 'رد شده', class: 'badge-error' },
    };
    return statusMap[status as keyof typeof statusMap] || { text: status, class: 'badge-neutral' };
  };

  const status = getStatusBadge(loan.status);

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
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          جزئیات وام #{loan.id}
        </h1>
        <p className="text-gray-600">اطلاعات کامل وام</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Main Info */}
        <div className="lg:col-span-2 space-y-6">
          {/* Loan Status */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-semibold">وضعیت وام</h2>
              <span className={`badge badge-lg ${status.class}`}>
                {status.text}
              </span>
            </div>
            
            {loan.rejection_reason && (
              <div className="bg-red-50 p-4 rounded-lg">
                <h3 className="font-semibold text-red-800 mb-2">دلیل رد</h3>
                <p className="text-red-600">{loan.rejection_reason}</p>
              </div>
            )}
          </div>

          {/* Loan Details */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold mb-4">اطلاعات وام</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">مبلغ وام</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.amount.toLocaleString()} تومان
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">مدت وام</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.term_months} ماه
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">نرخ بهره</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.interest_rate}%
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">پرداخت ماهانه</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.monthly_payment ? loan.monthly_payment.toLocaleString() : 'محاسبه نشده'} تومان
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">تاریخ درخواست</label>
                <p className="text-lg font-semibold text-gray-900">
                  {new Date(loan.created_at).toLocaleDateString('fa-IR')}
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">تاریخ شروع</label>
                <p className="text-lg font-semibold text-gray-900">
                  {loan.start_date ? new Date(loan.start_date).toLocaleDateString('fa-IR') : 'تعیین نشده'}
                </p>
              </div>
              {loan.approved_at && (
                <div>
                  <label className="block text-sm font-medium text-gray-600 mb-1">تاریخ تایید</label>
                  <p className="text-lg font-semibold text-gray-900">
                    {new Date(loan.approved_at).toLocaleDateString('fa-IR')}
                  </p>
                </div>
              )}
              {loan.approved_by_user && (
                <div>
                  <label className="block text-sm font-medium text-gray-600 mb-1">تایید شده توسط</label>
                  <p className="text-lg font-semibold text-gray-900">
                    {loan.approved_by_user.firstName} {loan.approved_by_user.lastName}
                  </p>
                </div>
              )}
            </div>
          </div>

          {/* Payment Schedule */}
          {loan.schedules && loan.schedules.length > 0 && (
            <div className="bg-white rounded-lg shadow-md p-6">
              <h2 className="text-xl font-semibold mb-4">برنامه پرداخت</h2>
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
                    </tr>
                  </thead>
                  <tbody>
                    {loan.schedules.map((schedule) => (
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
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}
        </div>

        {/* Sidebar */}
        <div className="space-y-6">
          {/* User Info */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold mb-4">اطلاعات متقاضی</h2>
            <div className="space-y-3">
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">نام</label>
                <p className="font-semibold text-gray-900">
                  {loan.user?.firstName} {loan.user?.lastName}
                </p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">ایمیل</label>
                <p className="font-semibold text-gray-900">{loan.user?.email}</p>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-600 mb-1">تاریخ عضویت</label>
                <p className="font-semibold text-gray-900">
                  {loan.user ? new Date(loan.user.createdAt).toLocaleDateString('fa-IR') : 'نامشخص'}
                </p>
              </div>
            </div>
          </div>

          {/* Actions */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-semibold mb-4">عملیات</h2>
            <div className="space-y-3">
              <button
                onClick={() => navigate('/dashboard')}
                className="btn btn-outline w-full"
              >
                بازگشت به داشبورد
              </button>
              {isAdmin && loan.status === 'pending' && (
                <button
                  onClick={() => navigate('/loan-approval')}
                  className="btn btn-primary w-full"
                >
                  مدیریت وام
                </button>
              )}
              {!isAdmin && loan.status === 'approved' && (
                <button
                  onClick={() => navigate('/loan-payment')}
                  className="btn btn-success w-full"
                >
                  پرداخت وام
                </button>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}