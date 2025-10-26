/**
 * Get loan status badge configuration
 */
export const getLoanStatusBadge = (status: string) => {
  const statusMap = {
    pending: { text: 'در انتظار تایید', class: 'badge-warning' }, // نارنجی - در انتظار
    approved: { text: 'تایید شده', class: 'badge-info' }, // آبی - تایید شده
    rejected: { text: 'رد شده', class: 'badge-error' }, // قرمز - رد شده
    active: { text: 'فعال', class: 'badge-success' }, // سبز - فعال و در حال پرداخت
    delinquent: { text: 'معوق', class: 'badge-error' }, // قرمز - معوق و پرداخت نشده
    paid: { text: 'پرداخت شده', class: 'badge-ghost' }, // خاکستری روشن - پرداخت کامل
  };
  
  return statusMap[status as keyof typeof statusMap] || { text: status, class: 'badge-neutral' };
};

/**
 * Get loan status text only
 */
export const getLoanStatusText = (status: string): string => {
  return getLoanStatusBadge(status).text;
};

/**
 * Get loan status badge class only
 */
export const getLoanStatusClass = (status: string): string => {
  return getLoanStatusBadge(status).class;
};

