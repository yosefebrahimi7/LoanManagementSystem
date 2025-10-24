import { Link, useNavigate, useLocation } from "react-router";
import { useQueryClient } from "@tanstack/react-query";
import useAuth from "../stores/auth";
import { showSuccessToast } from "../lib/toast";

function Navbar() {
  const navigate = useNavigate();
  const location = useLocation();
  const queryClient = useQueryClient();
  const { user, isAuthenticated, clear } = useAuth();

  const handleLogout = () => {
    clear();
    queryClient.clear();
    showSuccessToast("خروج با موفقیت انجام شد");
    navigate("/login");
  };

  const isActive = (path: string) => location.pathname === path;

  return (
    <div className="navbar bg-base-100 border-b border-base-300 px-4 py-3">
      <div className="flex-1 flex items-center gap-4">
        <Link
          to="/"
          className="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors w-fit"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-7 w-7 text-primary"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={2}
              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
            />
          </svg>
          <span className="text-lg font-bold">سیستم مدیریت وام</span>
        </Link>

        {isAuthenticated() && (
          <Link
            to="/users"
            className={`flex items-center gap-2 px-5 py-2.5 rounded-lg font-medium transition-all ${
              isActive("/users")
                ? "bg-primary text-primary-content shadow-md"
                : "hover:bg-base-200"
            }`}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
            داشبورد
          </Link>
        )}
      </div>

      <div className="flex-none">
        <div className="flex items-center gap-3">
          {isAuthenticated() ? (
            <>
              <div className="dropdown dropdown-end">
                <button
                  tabIndex={0}
                  className="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-base-200 transition-colors"
                >
                  <div className="flex flex-col items-end">
                    <span className="text-sm font-semibold">
                      {user?.firstName} {user?.lastName}
                    </span>
                    <span className="text-xs text-base-content/60">
                      {user?.email}
                    </span>
                  </div>
                  <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-primary-content font-bold shadow-lg">
                    {user?.firstName?.charAt(0)}
                  </div>
                </button>

                <ul
                  tabIndex={0}
                  className="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-lg w-56 mt-3 border border-base-300"
                >
                  <li>
                    <Link
                      to="/profile"
                      className="flex items-center gap-2 px-4 py-3 hover:bg-base-200 rounded-lg transition-colors"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                      </svg>
                      پروفایل من
                    </Link>
                  </li>
                  <div className="divider my-1"></div>
                  <li>
                    <button
                      onClick={handleLogout}
                      className="flex items-center gap-2 px-4 py-3 hover:bg-error/10 hover:text-error rounded-lg transition-colors"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                      </svg>
                      خروج از حساب
                    </button>
                  </li>
                </ul>
              </div>
            </>
          ) : (
            <>
              <Link
                to="/login"
                className="px-5 py-2.5 rounded-lg font-medium hover:bg-base-200 transition-colors"
              >
                ورود
              </Link>
              <Link
                to="/register"
                className="px-5 py-2.5 rounded-lg font-medium bg-primary text-primary-content hover:bg-primary-focus shadow-md hover:shadow-lg transition-all"
              >
                ثبت نام
              </Link>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

export default Navbar;
