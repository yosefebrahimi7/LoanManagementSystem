import { useState } from 'react';
import type { FormEvent } from 'react';
import { Link, useNavigate } from 'react-router';
import { showSuccessToast, showErrorToast } from '../lib/toast';
import appHttp from '../lib/appHttp';
import useAuth from '../stores/auth';

function Register() {
  const navigate = useNavigate();
  const setAuth = useAuth((state) => state.setAuth);
  
  const [formData, setFormData] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    confirmPassword: '',
  });
  const [acceptedTerms, setAcceptedTerms] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();

    if (!acceptedTerms) {
      showErrorToast('لطفا قوانین و مقررات را مطالعه کرده و بپذیرید');
      return;
    }

    if (formData.password !== formData.confirmPassword) {
      showErrorToast('رمز عبور و تکرار آن یکسان نیستند');
      return;
    }

    if (formData.password.length < 8) {
      showErrorToast('رمز عبور باید حداقل ۸ کاراکتر باشد');
      return;
    }

    setIsLoading(true);

    try {
      const { data } = await appHttp.post('/auth/register', {
        firstName: formData.firstName,
        lastName: formData.lastName,
        email: formData.email,
        password: formData.password,
      });
      
      setAuth(data.user, data.token, data.refreshToken);
      showSuccessToast('ثبت نام با موفقیت انجام شد');
      navigate('/users');
    } catch (error: any) {
      if (error?.response?.status === 400) {
        showErrorToast('این ایمیل قبلا ثبت شده است');
      } else if (error?.response?.data?.errors) {
        const firstError = Object.values(error.response.data.errors)[0];
        if (Array.isArray(firstError)) {
          showErrorToast(firstError[0] as string);
        } else {
          showErrorToast('اطلاعات وارد شده نامعتبر است');
        }
      } else if (error?.response?.data?.message) {
        showErrorToast(error.response.data.message);
      } else {
        showErrorToast('خطا در ثبت نام. لطفا دوباره تلاش کنید');
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-base-200 p-4">
      <div className="card w-full max-w-lg bg-base-100 shadow-xl">
        <div className="card-body">
          {/* Header */}
          <div className="text-center mb-6">
            <h1 className="text-3xl font-bold text-primary">ثبت نام</h1>
            <p className="text-base-content/60 mt-2">
              برای استفاده از سیستم، حساب کاربری ایجاد کنید
            </p>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-4">
            {/* Name Row */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {/* First Name */}
              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">نام</span>
                </label>
                <input
                  type="text"
                  placeholder="نام خود را وارد کنید"
                  className="input input-bordered w-full"
                  value={formData.firstName}
                  onChange={(e) => setFormData({ ...formData, firstName: e.target.value })}
                  required
                />
              </div>

              {/* Last Name */}
              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">نام خانوادگی</span>
                </label>
                <input
                  type="text"
                  placeholder="نام خانوادگی خود را وارد کنید"
                  className="input input-bordered w-full"
                  value={formData.lastName}
                  onChange={(e) => setFormData({ ...formData, lastName: e.target.value })}
                  required
                />
              </div>
            </div>

            {/* Email Input */}
            <div className="form-control">
              <label className="label">
                <span className="label-text font-semibold">ایمیل</span>
              </label>
              <input
                type="email"
                placeholder="آدرس ایمیل خود را وارد کنید"
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
                minLength={8}
                dir="ltr"
              />
              <label className="label">
                <span className="label-text-alt text-base-content/60">
                  رمز عبور باید حداقل ۸ کاراکتر باشد
                </span>
              </label>
            </div>

            {/* Confirm Password Input */}
            <div className="form-control">
              <label className="label">
                <span className="label-text font-semibold">تکرار رمز عبور</span>
              </label>
              <input
                type="password"
                placeholder="مجددا رمز عبور خود را وارد کنید"
                className="input input-bordered w-full"
                value={formData.confirmPassword}
                onChange={(e) => setFormData({ ...formData, confirmPassword: e.target.value })}
                required
                minLength={8}
                dir="ltr"
              />
            </div>

            {/* Terms Checkbox */}
            <div className="form-control">
              <label className="label cursor-pointer justify-start gap-3">
                <input 
                  type="checkbox" 
                  className="checkbox checkbox-primary"
                  checked={acceptedTerms}
                  onChange={(e) => setAcceptedTerms(e.target.checked)}
                />
                <span className="label-text">
                  <a href="#" className="link link-primary">
                    قوانین و مقررات
                  </a>
                  {' '}را مطالعه کرده و می‌پذیرم
                </span>
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
                    در حال ثبت نام...
                  </>
                ) : (
                  'ثبت نام'
                )}
              </button>
            </div>
          </form>

          {/* Divider */}
          <div className="divider">یا</div>

          {/* Login Link */}
          <div className="text-center">
            <p className="text-sm">
              قبلا ثبت نام کرده‌اید؟{' '}
              <Link to="/login" className="link link-primary font-semibold">
                وارد شوید
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Register;

