import { useEffect } from 'react';
import { useNavigate } from 'react-router';
import useAuth from '../stores/auth';
import { useUserLoans, useAllLoans, useLoanStats } from '../hooks/useLoans';
import { useLogout } from '../hooks/useAuth';

function Dashboard() {
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const logoutMutation = useLogout();

  const isAdmin = user?.roleName === 'admin';
  
  const { data: userLoans, isLoading: userLoansLoading } = useUserLoans();
  const { data: allLoans, isLoading: allLoansLoading } = useAllLoans(isAdmin);
  const { data: loanStats, isLoading: statsLoading } = useLoanStats(isAdmin);
  
  const loans = isAdmin ? allLoans : userLoans;
  const loansLoading = isAdmin ? allLoansLoading : userLoansLoading;

  useEffect(() => {
    if (!isAuthenticated()) {
      navigate('/login');
    }
  }, [isAuthenticated, navigate]);

  if (!user) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <span className="loading loading-spinner loading-lg"></span>
      </div>
    );
  }

  // Admin Dashboard
  if (isAdmin) {
    return (
      <div>
        {/* Dashboard Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">
            داشبورد مدیریت سیستم
          </h1>
          <p className="text-gray-600">
            مدیریت وام‌ها، کاربران و گزارش‌های سیستم
          </p>
        </div>

        {/* Welcome Card */}
          <div className="card bg-gradient-to-r from-primary to-secondary text-primary-content shadow-xl mb-8">
            <div className="card-body">
              <h2 className="card-title text-2xl">
                خوش آمدید، {user.firstName} {user.lastName}!
              </h2>
              <p className="text-lg">
                به پنل مدیریت سیستم خوش آمدید. از اینجا می‌توانید تمام وام‌ها و کاربران را مدیریت کنید.
              </p>
              <div className="card-actions justify-end">
                <button 
                  onClick={() => navigate('/loan-approval')} 
                  className="btn btn-accent"
                >
                  مدیریت وام‌ها
                </button>
              </div>
            </div>
          </div>

          {/* Admin Stats Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div className="stat bg-base-100 rounded-lg shadow">
              <div className="stat-figure text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div className="stat-title">کل وام‌ها</div>
              <div className="stat-value text-primary">
                {loansLoading ? '...' : (loans?.length || 0)}
              </div>
              <div className="stat-desc">تمام وام‌های سیستم</div>
            </div>

            <div className="stat bg-base-100 rounded-lg shadow">
              <div className="stat-figure text-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
              </div>
              <div className="stat-title">در انتظار تایید</div>
              <div className="stat-value text-secondary">
                {loansLoading ? '...' : (loans?.filter(loan => loan.status === 'pending').length || 0)}
              </div>
              <div className="stat-desc">وام‌های در انتظار</div>
            </div>

            <div className="stat bg-base-100 rounded-lg shadow">
              <div className="stat-figure text-accent">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 01.293.707V16a2 2 0 01-2 2H5a2 2 0 01-2-2V8zM5 8a2 2 0 012-2h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 01.293.707V16a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                </svg>
              </div>
              <div className="stat-title">وام‌های تایید شده</div>
              <div className="stat-value text-accent">
                {loansLoading ? '...' : (loans?.filter(loan => loan.status === 'approved').length || 0)}
              </div>
              <div className="stat-desc">این ماه</div>
            </div>

            <div className="stat bg-base-100 rounded-lg shadow">
              <div className="stat-figure text-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <div className="stat-title">وام‌های رد شده</div>
              <div className="stat-value text-info">
                {loansLoading ? '...' : (loans?.filter(loan => loan.status === 'rejected').length || 0)}
              </div>
              <div className="stat-desc">این ماه</div>
            </div>
          </div>

          {/* Admin Quick Actions */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="card bg-base-100 shadow-xl">
              <div className="card-body">
                <h2 className="card-title">مدیریت وام‌ها</h2>
                <p>تایید، رد و مدیریت تمام وام‌های سیستم</p>
                <div className="card-actions justify-end">
                  <button 
                    onClick={() => navigate('/loan-approval')} 
                    className="btn btn-primary"
                  >
                    مدیریت وام‌ها
                  </button>
                </div>
              </div>
            </div>

            <div className="card bg-base-100 shadow-xl">
              <div className="card-body">
                <h2 className="card-title">آخرین وام‌ها</h2>
                <div className="space-y-2">
                  {loansLoading ? (
                    <div className="flex justify-center">
                      <span className="loading loading-spinner"></span>
                    </div>
                  ) : loans && loans.length > 0 ? (
                    loans.slice(0, 3).map((loan) => (
                      <div key={loan.id} className="flex items-center gap-2">
                        <div className={`badge ${
                          loan.status === 'approved' ? 'badge-success' :
                          loan.status === 'rejected' ? 'badge-error' :
                          'badge-warning'
                        }`}></div>
                        <span className="text-sm">
                          وام {loan.amount.toLocaleString()} تومان - {loan.user?.firstName} {loan.user?.lastName}
                        </span>
                      </div>
                    ))
                  ) : (
                    <p className="text-sm text-gray-500">هیچ وامی یافت نشد</p>
                  )}
                </div>
              </div>
            </div>
          </div>
        </div>
    );
  }

  // Regular User Dashboard
  return (
    <div>
      {/* Dashboard Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">
          داشبورد شخصی
        </h1>
        <p className="text-gray-600">
          مدیریت وام‌ها و درخواست‌های شخصی شما
        </p>
      </div>

      {/* Welcome Card */}
        <div className="card bg-gradient-to-r from-primary to-secondary text-primary-content shadow-xl mb-8">
          <div className="card-body">
            <h2 className="card-title text-2xl">
              خوش آمدید، {user.firstName} {user.lastName}!
            </h2>
            <p className="text-lg">
              به داشبورد شخصی خود خوش آمدید. از اینجا می‌توانید وام درخواست کنید و وضعیت آن‌ها را پیگیری کنید.
            </p>
            <div className="card-actions justify-end">
              <button 
                onClick={() => navigate('/loan-request')} 
                className="btn btn-accent"
              >
                درخواست وام جدید
              </button>
            </div>
          </div>
        </div>

        {/* User Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-primary">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div className="stat-title">وام‌های من</div>
            <div className="stat-value text-primary">
              {loansLoading ? '...' : (loans?.length || 0)}
            </div>
            <div className="stat-desc">کل وام‌های درخواستی</div>
          </div>

          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-secondary">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
              </svg>
            </div>
            <div className="stat-title">در انتظار تایید</div>
            <div className="stat-value text-secondary">
              {loansLoading ? '...' : (loans?.filter(loan => loan.status === 'pending').length || 0)}
            </div>
            <div className="stat-desc">وام‌های در انتظار</div>
          </div>

          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-accent">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 01.293.707V16a2 2 0 01-2 2H5a2 2 0 01-2-2V8zM5 8a2 2 0 012-2h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 01.293.707V16a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
              </svg>
            </div>
            <div className="stat-title">تایید شده</div>
            <div className="stat-value text-accent">
              {loansLoading ? '...' : (loans?.filter(loan => loan.status === 'approved').length || 0)}
            </div>
            <div className="stat-desc">وام‌های تایید شده</div>
          </div>

          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-info">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div className="stat-title">رد شده</div>
            <div className="stat-value text-info">
              {loansLoading ? '...' : (loans?.filter(loan => loan.status === 'rejected').length || 0)}
            </div>
            <div className="stat-desc">وام‌های رد شده</div>
          </div>
        </div>

        {/* User Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="card bg-base-100 shadow-xl">
            <div className="card-body">
              <h2 className="card-title">درخواست وام</h2>
              <p>درخواست وام جدید بدهید و وضعیت آن را پیگیری کنید</p>
              <div className="card-actions justify-end">
                <button 
                  onClick={() => navigate('/loan-request')} 
                  className="btn btn-primary"
                >
                  درخواست وام جدید
                </button>
              </div>
            </div>
          </div>

          <div className="card bg-base-100 shadow-xl">
            <div className="card-body">
              <h2 className="card-title">وام‌های من</h2>
              <div className="space-y-2">
                {loansLoading ? (
                  <div className="flex justify-center">
                    <span className="loading loading-spinner"></span>
                  </div>
                ) : loans && loans.length > 0 ? (
                  loans.slice(0, 3).map((loan) => (
                    <div key={loan.id} className="flex items-center gap-2">
                      <div className={`badge ${
                        loan.status === 'approved' ? 'badge-success' :
                        loan.status === 'rejected' ? 'badge-error' :
                        'badge-warning'
                      }`}></div>
                      <span className="text-sm">
                        {loan.amount.toLocaleString()} تومان - {loan.status === 'pending' ? 'در انتظار' : 
                        loan.status === 'approved' ? 'تایید شده' : 'رد شده'}
                      </span>
                    </div>
                  ))
                ) : (
                  <p className="text-sm text-gray-500">هنوز وامی درخواست نکرده‌اید</p>
                )}
              </div>
            </div>
          </div>
        </div>
        
        {/* Extra spacing to ensure scroll works */}
        <div className="h-20"></div>
      </div>
  );
}

export default Dashboard;