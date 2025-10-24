import { Link, useNavigate } from "react-router";
import { useEffect } from "react";
import useAuth from "../stores/auth";

function Home() {
  const { user, isAuthenticated } = useAuth();
  const navigate = useNavigate();

  useEffect(() => {
    if (isAuthenticated()) {
      navigate('/dashboard');
    }
  }, [isAuthenticated, navigate]);

  return (
    <div className="min-h-screen bg-base-200">
      <div className="hero min-h-screen">
        <div className="hero-content text-center">
          <div className="max-w-2xl">
            <h1 className="text-5xl font-bold text-primary mb-6">
              سامانه مدیریت وام
            </h1>

            {isAuthenticated() ? (
              <>
                <p className="text-xl text-base-content/70 mb-8">
                  سلام {user?.firstName} عزیز، به پنل مدیریت خوش آمدید.
                </p>

                <div className="flex gap-4 justify-center flex-wrap">
                  <Link to="/dashboard" className="btn btn-primary btn-lg gap-2">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-6 w-6"
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
                </div>
              </>
            ) : (
              <>
                <p className="text-xl text-base-content/70 mb-8">
                  به سیستم مدیریت جامع وام خوش آمدید. برای شروع، لطفا
                  وارد حساب کاربری خود شوید یا ثبت نام کنید.
                </p>

                <div className="flex gap-4 justify-center flex-wrap">
                  <Link to="/login" className="btn btn-primary btn-lg gap-2">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-6 w-6"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                      />
                    </svg>
                    ورود به سیستم
                  </Link>
                  <Link
                    to="/register"
                    className="btn btn-outline btn-primary btn-lg gap-2"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-6 w-6"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                      />
                    </svg>
                    ثبت نام
                  </Link>
                </div>
              </>
            )}

            {/* Features */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
              <div className="card bg-base-100 shadow-lg">
                <div className="card-body items-center text-center">
                  <div className="text-primary mb-2">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-12 w-12"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                      />
                    </svg>
                  </div>
                  <h3 className="card-title text-lg">مدیریت کاربران</h3>
                  <p className="text-sm text-base-content/70">
                    مدیریت جامع کاربران و دسترسی‌ها
                  </p>
                </div>
              </div>

              <div className="card bg-base-100 shadow-lg">
                <div className="card-body items-center text-center">
                  <div className="text-primary mb-2">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-12 w-12"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                      />
                    </svg>
                  </div>
                  <h3 className="card-title text-lg">گزارش‌گیری</h3>
                  <p className="text-sm text-base-content/70">
                    گزارش‌های دقیق و جامع از فعالیت‌ها
                  </p>
                </div>
              </div>

              <div className="card bg-base-100 shadow-lg">
                <div className="card-body items-center text-center">
                  <div className="text-primary mb-2">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      className="h-12 w-12"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={2}
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                      />
                    </svg>
                  </div>
                  <h3 className="card-title text-lg">امنیت بالا</h3>
                  <p className="text-sm text-base-content/70">
                    امنیت و حفاظت از اطلاعات شما
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Home;
