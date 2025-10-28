import { Link } from "react-router";
import Layout from "../components/Layout";

function NotFound() {
  return (
    <div className="min-h-[calc(100vh-200px)] flex items-center justify-center px-4">
      <div className="text-center max-w-2xl">
        {/* 404 Number */}
        <div className="mb-8">
          <h1 className="text-9xl font-bold text-primary opacity-20 select-none">
            404
          </h1>
        </div>

        {/* Error Message */}
        <div className="mb-8">
          <h2 className="text-4xl font-bold text-base-content mb-4">
            صفحه مورد نظر یافت نشد!
          </h2>
          <p className="text-lg text-base-content/70">
            متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد یا از سایت حذف
            شده است.
          </p>
        </div>

        {/* Illustration */}
        <div className="mb-8 flex justify-center">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-48 w-48 text-primary/30"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth={1}
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>

        {/* Actions */}
        <div className="flex gap-4 justify-center flex-wrap">
          <Link to="/" className="btn btn-primary btn-lg gap-2">
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
            بازگشت به صفحه اصلی
          </Link>
          <button
            onClick={() => window.history.back()}
            className="btn btn-outline btn-primary btn-lg gap-2"
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
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            بازگشت به صفحه قبل
          </button>
        </div>

        {/* Help Text */}
        <div className="mt-12 pt-8 border-t border-base-300">
          <p className="text-sm text-base-content/60">
            اگر فکر می‌کنید این یک خطا است، لطفا با پشتیبانی تماس بگیرید.
          </p>
        </div>
      </div>
    </div>
  );
}

export default NotFound;

