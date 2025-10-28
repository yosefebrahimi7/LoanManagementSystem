import { useNavigate } from 'react-router';
import useAuth from '../stores/auth';
import { useLogout } from '../hooks/useAuth';

function UserDropdown() {
  const navigate = useNavigate();
  const { user } = useAuth();
  const logoutMutation = useLogout();

  const isAdmin = user?.roleName === 'admin';

  return (
    <div className="dropdown dropdown-end mr-4">
      <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
        <div className="w-8 h-8 rounded-full bg-primary text-primary-content relative">
          <span className="text-xs font-bold absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2" style={{ lineHeight: 1 }}>
            {user?.firstName?.charAt(0)}{user?.lastName?.charAt(0)}
          </span>
        </div>
      </div>
      <ul tabIndex={0} className="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
        <li className="text-center">
          <div className="text-sm font-semibold mb-1">
            {user?.firstName}&nbsp;{user?.lastName}
          </div>
          <div className="text-xs text-base-content/60">
            {user?.email}
            {isAdmin && (
              <span className="badge badge-sm badge-primary mr-1">ادمین</span>
            )}
          </div>
        </li>
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
  );
}

export default UserDropdown;

