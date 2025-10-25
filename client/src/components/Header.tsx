import { useLocation, useNavigate } from "react-router";
import useAuth from "../stores/auth";
import { useLogout } from "../hooks/useAuth";

interface BreadcrumbItem {
  label: string;
  path?: string;
}

const getBreadcrumbs = (pathname: string): BreadcrumbItem[] => {
  const pathSegments = pathname.split('/').filter(Boolean);
  const breadcrumbs: BreadcrumbItem[] = [
    { label: 'خانه', path: '/dashboard' }
  ];

  const pathMap: Record<string, string> = {
    'loan-request': 'درخواست وام',
    'loan-approval': 'تایید وام',
    'loan-details': 'جزئیات وام',
    'profile': 'پروفایل',
    'users': 'مدیریت کاربران',
  };

  pathSegments.forEach((segment, index) => {
    if (segment === 'dashboard') return;
    
    const isLast = index === pathSegments.length - 1;
    const label = pathMap[segment] || segment;
    
    if (isLast) {
      breadcrumbs.push({ label });
    } else {
      breadcrumbs.push({ 
        label, 
        path: `/${pathSegments.slice(0, index + 1).join('/')}` 
      });
    }
  });

  return breadcrumbs;
};

export default function Header() {
  const location = useLocation();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const logoutMutation = useLogout();

  if (!isAuthenticated()) {
    return null;
  }

  const breadcrumbs = getBreadcrumbs(location.pathname);
  const isAdmin = user?.roleName === 'admin';

  return (
    <div className="bg-white border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Breadcrumb */}
          <nav className="flex" aria-label="Breadcrumb">
            <ol className="flex items-center space-x-2 rtl:space-x-reverse">
              {breadcrumbs.map((item, index) => (
                <li key={index} className="flex items-center">
                  {index > 0 && (
                    <svg
                      className="flex-shrink-0 h-4 w-4 text-gray-400 mx-2"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fillRule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clipRule="evenodd"
                      />
                    </svg>
                  )}
                  {item.path ? (
                    <button
                      onClick={() => navigate(item.path!)}
                      className="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors"
                    >
                      {item.label}
                    </button>
                  ) : (
                    <span className="text-sm font-medium text-gray-900">
                      {item.label}
                    </span>
                  )}
                </li>
              ))}
            </ol>
          </nav>

          {/* User Menu */}
          <div className="flex items-center space-x-4 rtl:space-x-reverse">
            {/* Quick Actions */}
            <div className="hidden md:flex items-center space-x-2 rtl:space-x-reverse">
              {!isAdmin && (
                <button
                  onClick={() => navigate('/loan-request')}
                  className="btn btn-sm btn-outline"
                >
                  درخواست وام
                </button>
              )}
              {isAdmin && (
                <button
                  onClick={() => navigate('/loan-approval')}
                  className="btn btn-sm btn-primary"
                >
                  مدیریت وام‌ها
                </button>
              )}
            </div>

            {/* User Dropdown */}
            <div className="dropdown dropdown-end">
              <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
                <div className="w-8 rounded-full bg-primary text-primary-content flex items-center justify-center">
                  <span className="text-sm font-bold">
                    {user?.firstName?.charAt(0)}{user?.lastName?.charAt(0)}
                  </span>
                </div>
              </div>
              <ul tabIndex={0} className="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
                <li>
                  <div className="text-sm font-semibold">
                    {user?.firstName} {user?.lastName}
                  </div>
                  <div className="text-xs text-base-content/60">
                    {user?.email}
                    {isAdmin && (
                      <span className="badge badge-sm badge-primary mr-1">ادمین</span>
                    )}
                  </div>
                </li>
                <li><hr className="my-2" /></li>
                <li>
                  <button 
                    onClick={() => navigate('/profile')} 
                    className="justify-between"
                  >
                    پروفایل
                    <span className="badge badge-sm">جدید</span>
                  </button>
                </li>
                {isAdmin && (
                  <li>
                    <button 
                      onClick={() => navigate('/users')} 
                      className="justify-between"
                    >
                      مدیریت کاربران
                    </button>
                  </li>
                )}
                <li><hr className="my-2" /></li>
                <li>
                  <button 
                    onClick={() => logoutMutation.mutate()} 
                    className="text-error"
                  >
                    خروج
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
