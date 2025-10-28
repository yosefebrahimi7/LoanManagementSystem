import { useState } from 'react';
import { useWallet, useWalletTransactions, useAddToWallet } from '../hooks/useWallet';
import { formatAmountWithPersianDigits, formatAmountToPersianWords } from '../utils/numberUtils';
import { formatDistanceToNow } from 'date-fns';
import { faIR } from 'date-fns/locale';

interface Transaction {
  id: string;
  type: 'credit' | 'debit';
  amount: number;
  description: string;
  created_at: string;
}

function WalletDropdown() {
  const [isOpen, setIsOpen] = useState(false);
  const [showRechargeModal, setShowRechargeModal] = useState(false);
  const [rechargeAmount, setRechargeAmount] = useState('');
  
  const { data: wallet, isLoading: walletLoading } = useWallet();
  const { data: transactionsData, isLoading: transactionsLoading } = useWalletTransactions(1, 5);
  const rechargeMutation = useAddToWallet();

  const transactions: Transaction[] = transactionsData?.data || [];
  const balance = wallet?.balance || 0;

  const handleRecharge = () => {
    const amountInTomans = parseFloat(rechargeAmount);
    
    if (!rechargeAmount || amountInTomans < 10000 || amountInTomans > 2000000000) {
      return;
    }

    rechargeMutation.mutate(
      { amount: parseInt(rechargeAmount) * 10, method: 'zarinpal' },
      {
        onSuccess: (data) => {
          if (data.payment_url) {
            window.location.href = data.payment_url;
          }
        },
      }
    );
  };

  const formatDate = (dateString: string) => {
    try {
      return formatDistanceToNow(new Date(dateString), {
        addSuffix: true,
        locale: faIR,
      });
    } catch {
      return dateString;
    }
  };

  const getTransactionIcon = (type: string) => {
    return type === 'credit' ? '💰' : '💸';
  };

  const getTransactionColor = (type: string) => {
    return type === 'credit' ? 'text-success' : 'text-error';
  };

  return (
    <>
      <div className="dropdown dropdown-end">
        <label tabIndex={0} className="btn btn-ghost btn-circle relative">
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
              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
            />
          </svg>
        </label>

        <div
          tabIndex={0}
          className="dropdown-content menu z-[1] mt-3 w-96 rounded-box bg-base-100 p-2 shadow-lg"
        >
          <div className="flex items-center justify-between border-b pb-3 mb-3">
            <h3 className="text-lg font-bold">کیف پول</h3>
          </div>

          {/* Balance */}
          <div className="bg-primary/10 rounded-lg p-4 mb-3">
            <div className="text-sm text-base-content/60 mb-1">موجودی کیف پول</div>
            <div className="text-2xl font-bold text-primary">
              {formatAmountWithPersianDigits(balance / 10)}
            </div>
            {wallet?.is_shared && (
              <div className="text-xs text-warning mt-1">💼 کیف پول مشترک ادمین</div>
            )}
          </div>

          {/* Transactions */}
          <div className="mb-3">
            <div className="text-sm font-semibold mb-2">آخرین تراکنش‌ها</div>
            {transactionsLoading ? (
              <div className="flex justify-center py-4">
                <span className="loading loading-spinner loading-sm"></span>
              </div>
            ) : transactions.length === 0 ? (
              <div className="text-sm text-base-content/60 text-center py-4">
                تراکنشی وجود ندارد
              </div>
            ) : (
              <div className="space-y-2 max-h-48 overflow-y-auto">
                {transactions.map((transaction: Transaction) => (
                  <div
                    key={transaction.id}
                    className="flex items-center gap-2 p-2 rounded-lg border border-base-300 hover:bg-base-200 transition-colors"
                  >
                    <div className={`text-lg ${getTransactionColor(transaction.type)}`}>
                      {getTransactionIcon(transaction.type)}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="text-sm font-medium text-base-content">
                        {transaction.description}
                      </div>
                      <div className="text-xs text-base-content/60">
                        {formatDate(transaction.created_at)}
                      </div>
                    </div>
                    <div className={`text-sm font-bold ${getTransactionColor(transaction.type)}`}>
                      {transaction.type === 'credit' ? '+' : '-'}
                      {formatAmountWithPersianDigits(transaction.amount / 10)} تومان
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>

          {/* Recharge Button */}
          <button
            onClick={() => setShowRechargeModal(true)}
            className="btn btn-primary btn-sm w-full"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={2}
                d="M12 4v16m8-8H4"
              />
            </svg>
            شارژ کیف پول
          </button>
        </div>
      </div>

      {/* Recharge Modal */}
      {showRechargeModal && (
        <div className="modal modal-open">
          <div className="modal-box">
            <h3 className="font-bold text-lg mb-4">شارژ کیف پول</h3>
            <div className="mb-4">
              <label className="label">
                <span className="label-text">مبلغ شارژ (تومان)</span>
              </label>
              <input
                type="number"
                className="input input-bordered w-full"
                placeholder="مثلاً ۱۰۰۰۰"
                value={rechargeAmount}
                onChange={(e) => setRechargeAmount(e.target.value)}
                min="10000"
                max="2000000000"
              />
              {rechargeAmount && (
                <div className="mt-2 text-sm text-gray-600 rtl">
                  <span className="font-medium">مبلغ شارژ: </span>
                  <span className="text-primary font-semibold">
                    {formatAmountToPersianWords(parseFloat(rechargeAmount))}
                  </span>
                </div>
              )}
            </div>
            <div className="flex gap-2 justify-end">
              <button
                className="btn btn-ghost"
                onClick={() => {
                  setShowRechargeModal(false);
                  setRechargeAmount('');
                }}
              >
                انصراف
              </button>
              <button
                className="btn btn-primary"
                onClick={handleRecharge}
                disabled={
                  rechargeMutation.isPending ||
                  !rechargeAmount ||
                  parseFloat(rechargeAmount) < 10000 ||
                  parseFloat(rechargeAmount) > 2000000000
                }
              >
                {rechargeMutation.isPending ? (
                  <span className="loading loading-spinner loading-sm"></span>
                ) : (
                  'افزودن به کیف پول'
                )}
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}

export default WalletDropdown;

