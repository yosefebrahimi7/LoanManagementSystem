import { useNavigate, useSearchParams } from "react-router";

export default function PaymentFailed() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const error = searchParams.get('error');

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-lg shadow-lg max-w-md w-full p-8 text-center">
        <div className="mb-6">
          <div className="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100">
            <svg
              className="h-8 w-8 text-red-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </div>
        </div>

        <h1 className="text-2xl font-bold text-gray-900 mb-4">
          پرداخت انجام نشد
        </h1>

        <p className="text-gray-600 mb-6">
          متأسفانه پرداخت شما انجام نشد. لطفاً دوباره تلاش کنید.
        </p>

        {error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <p className="text-sm text-red-700">{error}</p>
          </div>
        )}

        <div className="bg-gray-50 rounded-lg p-4 mb-6">
          <p className="text-sm text-gray-600">
            در صورت بروز مشکل، با پشتیبانی تماس بگیرید.
          </p>
        </div>

        <div className="flex gap-4">
          <button
            onClick={() => navigate(-1)}
            className="btn btn-outline flex-1"
          >
            تلاش مجدد
          </button>
          <button
            onClick={() => navigate('/dashboard')}
            className="btn btn-primary flex-1"
          >
            داشبورد
          </button>
        </div>
      </div>
    </div>
  );
}

