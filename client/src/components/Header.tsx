import { useLocation, useNavigate } from "react-router";
import useAuth from "../stores/auth";
import NotificationDropdown from "./NotificationDropdown";
import WalletDropdown from "./WalletDropdown";
import UserDropdown from "./UserDropdown";

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

  if (!isAuthenticated()) {
    return null;
  }

  const breadcrumbs = getBreadcrumbs(location.pathname);
  const isAdmin = user?.roleName === 'admin';

  return (
    <div className="bg-white border-b border-gray-200 sticky top-0 z-20">
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
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
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

            {/* Notification Dropdown */}
            <NotificationDropdown />

            {/* Wallet Dropdown */}
            <WalletDropdown />

            {/* User Dropdown */}
            <UserDropdown />
          </div>
        </div>
      </div>
    </div>
  );
}
