import { useState } from 'react';
import type { FormEvent } from 'react';
import { Link, useNavigate } from 'react-router';
import { showSuccessToast, showErrorToast } from '../lib/toast';
import appHttp from '../lib/appHttp';
import useAuth from '../stores/auth';

function Login() {
  const navigate = useNavigate();
  const setAuth = useAuth((state) => state.setAuth);
  
  const [formData, setFormData] = useState({
    email: '',
    password: '',
  });
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setIsLoading(true);

    try {
      const { data } = await appHttp.post('/auth/login', formData);
      
      setAuth(data.user, data.token, data.refreshToken);
      showSuccessToast('ورود با موفقیت انجام شد');
      navigate('/users');
    } catch (error: any) {
      if (error?.response?.data?.message) {
        showErrorToast(error.response.data.message);
      } else if (error?.response?.status === 401) {
        showErrorToast('ایمیل یا رمز عبور اشتباه است');
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-base-200 p-4">
      <div className="card w-full max-w-md bg-base-100 shadow-xl">
        <div className="card-body">
          {/* Header */}
          <div className="text-center mb-6">
            <h1 className="text-3xl font-bold text-primary">ورود به سیستم</h1>
            <p className="text-base-content/60 mt-2">
              به پنل مدیریت وام خوش آمدید
            </p>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Email Input */}
            <div className="form-control">
              <label className="label">
                <span className="label-text font-semibold">ایمیل</span>
              </label>
              <input
                type="email"
                placeholder="ایمیل خود را وارد کنید"
                className="input input-bordered w-full"
                value={formData.email}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                required
                dir="ltr"
              />
            </div>

            {/* Password Input */}
            <div className="form-control">
              <label className="label">
                <span className="label-text font-semibold">رمز عبور</span>
              </label>
              <input
                type="password"
                placeholder="رمز عبور خود را وارد کنید"
                className="input input-bordered w-full"
                value={formData.password}
                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                required
                dir="ltr"
              />
              <label className="label">
                <a href="#" className="label-text-alt link link-hover">
                  فراموشی رمز عبور؟
                </a>
              </label>
            </div>

            {/* Submit Button */}
            <div className="form-control mt-6">
              <button
                type="submit"
                className="btn btn-primary w-full"
                disabled={isLoading}
              >
                {isLoading ? (
                  <>
                    <span className="loading loading-spinner"></span>
                    در حال ورود...
                  </>
                ) : (
                  'ورود'
                )}
              </button>
            </div>
          </form>

          {/* Divider */}
          <div className="divider">یا</div>

          {/* Register Link */}
          <div className="text-center">
            <p className="text-sm">
              حساب کاربری ندارید؟{' '}
              <Link to="/register" className="link link-primary font-semibold">
                ثبت نام کنید
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Login;

