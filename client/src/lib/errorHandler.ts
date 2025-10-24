import { showErrorToast } from './toast';

export interface ApiError {
  message: string;
  status?: number;
  errors?: Record<string, string[]>;
}

export const handleApiError = (error: any): ApiError => {
  const defaultError: ApiError = {
    message: 'خطای غیرمنتظره رخ داده است',
    status: 500,
  };

  if (!error?.response) {
    return {
      ...defaultError,
      message: 'خطا در اتصال به سرور',
    };
  }

  const { status, data } = error.response;

  // Handle different status codes
  switch (status) {
    case 400:
      return {
        message: data?.message || 'درخواست نامعتبر',
        status,
        errors: data?.errors,
      };

    case 401:
      return {
        message: data?.message || 'دسترسی غیرمجاز',
        status,
      };

    case 403:
      return {
        message: 'شما به این بخش دسترسی ندارید',
        status,
      };

    case 404:
      return {
        message: 'اطلاعات مورد نظر یافت نشد',
        status,
      };

    case 409:
      return {
        message: 'این اطلاعات قبلا ثبت شده است',
        status,
      };

    case 422:
      return {
        message: 'اطلاعات وارد شده نامعتبر است',
        status,
        errors: data?.errors,
      };

    case 429:
      return {
        message: 'تعداد درخواست‌ها بیش از حد مجاز است',
        status,
      };

    case 500:
      return {
        message: data?.message || 'خطای سرور. لطفا بعدا تلاش کنید',
        status,
      };

    default:
      return {
        message: data?.message || defaultError.message,
        status,
        errors: data?.errors,
      };
  }
};

export const showApiError = (error: any): void => {
  const apiError = handleApiError(error);
  
  if (apiError.errors) {
    // Show first validation error
    const firstError = Object.values(apiError.errors)[0];
    if (Array.isArray(firstError) && firstError.length > 0) {
      showErrorToast(firstError[0]);
      return;
    }
  }
  
  showErrorToast(apiError.message);
};
