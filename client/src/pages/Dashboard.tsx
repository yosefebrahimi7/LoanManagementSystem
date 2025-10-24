import { useEffect } from 'react';
import { useNavigate } from 'react-router';
import useAuth from '../stores/auth';
import { useLogout } from '../hooks/useLogout';

function Dashboard() {
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const { logout } = useLogout();

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

  return (
    <div className="min-h-screen bg-base-200">
      {/* Header */}
      <div className="navbar bg-base-100 shadow-lg">
        <div className="flex-1">
          <h1 className="text-xl font-bold text-primary">داشبورد مدیریت وام</h1>
        </div>
        <div className="flex-none">
          <div className="dropdown dropdown-end">
            <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
              <div className="w-10 rounded-full bg-primary text-primary-content flex items-center justify-center">
                <span className="text-lg font-bold">
                  {user.firstName.charAt(0)}{user.lastName.charAt(0)}
                </span>
              </div>
            </div>
            <ul tabIndex={0} className="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
              <li>
                <div className="text-sm font-semibold">
                  {user.firstName} {user.lastName}
                </div>
                <div className="text-xs text-base-content/60">
                  {user.email}
                </div>
              </li>
              <li><hr className="my-2" /></li>
              <li>
                <button onClick={() => navigate('/profile')} className="justify-between">
                  پروفایل
                  <span className="badge">جدید</span>
                </button>
              </li>
              <li>
                <button onClick={logout} className="text-error">
                  خروج
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>

      {/* Main Content */}
      <div className="container mx-auto px-4 py-8">
        {/* Welcome Card */}
        <div className="card bg-gradient-to-r from-primary to-secondary text-primary-content shadow-xl mb-8">
          <div className="card-body">
            <h2 className="card-title text-2xl">
              خوش آمدید، {user.firstName} {user.lastName}!
            </h2>
            <p className="text-lg">
              به سیستم مدیریت وام خوش آمدید. از اینجا می‌توانید تمام امکانات سیستم را مدیریت کنید.
            </p>
            <div className="card-actions justify-end">
              <button className="btn btn-accent">
                شروع کنید
              </button>
            </div>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-primary">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div className="stat-title">کل وام‌ها</div>
            <div className="stat-value text-primary">25</div>
            <div className="stat-desc">وام‌های فعال</div>
          </div>

          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-secondary">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
              </svg>
            </div>
            <div className="stat-title">مبلغ کل</div>
            <div className="stat-value text-secondary">1.2M</div>
            <div className="stat-desc">تومان</div>
          </div>

          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-accent">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 01.293.707V16a2 2 0 01-2 2H5a2 2 0 01-2-2V8zM5 8a2 2 0 012-2h1.586a1 1 0 01.707.293l1.414 1.414a1 1 0 01.293.707V16a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
              </svg>
            </div>
            <div className="stat-title">وام‌های جدید</div>
            <div className="stat-value text-accent">12</div>
            <div className="stat-desc">این ماه</div>
          </div>

          <div className="stat bg-base-100 rounded-lg shadow">
            <div className="stat-figure text-info">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" className="inline-block w-8 h-8 stroke-current">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div className="stat-title">نرخ بازپرداخت</div>
            <div className="stat-value text-info">98%</div>
            <div className="stat-desc">موفق</div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div className="card bg-base-100 shadow-xl">
            <div className="card-body">
              <h2 className="card-title">عملیات سریع</h2>
              <p>دسترسی سریع به مهم‌ترین بخش‌های سیستم</p>
              <div className="card-actions justify-end">
                <button className="btn btn-primary">مدیریت وام‌ها</button>
                <button className="btn btn-secondary">گزارش‌ها</button>
              </div>
            </div>
          </div>

          <div className="card bg-base-100 shadow-xl">
            <div className="card-body">
              <h2 className="card-title">آخرین فعالیت‌ها</h2>
              <div className="space-y-2">
                <div className="flex items-center gap-2">
                  <div className="badge badge-success"></div>
                  <span className="text-sm">وام جدید ثبت شد</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="badge badge-info"></div>
                  <span className="text-sm">پرداخت دریافت شد</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="badge badge-warning"></div>
                  <span className="text-sm">یادآوری پرداخت ارسال شد</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Dashboard;
