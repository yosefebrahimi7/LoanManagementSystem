import { useUsers, useToggleUserStatus } from "../hooks/useUsers";

function Users() {
  const { data: users, isLoading, isError, error } = useUsers();
  const toggleStatus = useToggleUserStatus();

  if (isLoading) {
    return (
      <div className="container mx-auto p-8">
        <div className="flex justify-center items-center min-h-[400px]">
          <span className="loading loading-spinner loading-lg text-primary"></span>
        </div>
      </div>
    );
  }

  if (isError) {
    return (
      <div className="container mx-auto p-8">
        <div className="alert alert-error shadow-lg">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="stroke-current shrink-0 h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
          >
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <span>خطا در دریافت اطلاعات: {error.message}</span>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold mb-2">مدیریت کاربران</h1>
          <p className="text-base-content/60">
            {users?.length || 0} کاربر ثبت شده
          </p>
        </div>
      </div>

      {users && users.length > 0 ? (
        <div className="card bg-base-100 shadow-xl">
          <div className="overflow-x-auto">
            <table className="table">
              <thead>
                <tr className="border-b border-base-300">
                  <th className="bg-base-200">شناسه</th>
                  <th className="bg-base-200">نام و نام خانوادگی</th>
                  <th className="bg-base-200">ایمیل</th>
                  <th className="bg-base-200">وضعیت</th>
                  <th className="bg-base-200">تاریخ عضویت</th>
                  <th className="bg-base-200">آخرین بروزرسانی</th>
                  <th className="bg-base-200">عملیات</th>
                </tr>
              </thead>
              <tbody>
                {users.map((user) => (
                  <tr
                    key={user.id}
                    className="hover:bg-base-200 transition-colors"
                  >
                    <td>
                      <div className="badge badge-ghost badge-lg">
                        #{user.id}
                      </div>
                    </td>
                    <td>
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-primary-content font-bold shadow">
                          {user.firstName?.charAt(0)}
                        </div>
                        <div>
                          <div className="font-semibold">
                            {user.firstName} {user.lastName}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div className="text-sm" dir="ltr">
                        {user.email}
                      </div>
                    </td>
                    <td>
                      <div className="flex justify-center">
                        {user.isActive ? (
                          <div className="tooltip" data-tip="فعال">
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              className="h-6 w-6 text-success"
                              fill="none"
                              viewBox="0 0 24 24"
                              stroke="currentColor"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                              />
                            </svg>
                          </div>
                        ) : (
                          <div className="tooltip" data-tip="غیرفعال">
                            <svg
                              xmlns="http://www.w3.org/2000/svg"
                              className="h-6 w-6 text-error"
                              fill="none"
                              viewBox="0 0 24 24"
                              stroke="currentColor"
                            >
                              <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                              />
                            </svg>
                          </div>
                        )}
                      </div>
                    </td>
                    <td>
                      <div className="text-sm" dir="ltr">
                        {new Date(user.createdAt).toLocaleString("fa-IR", {
                          year: "numeric",
                          month: "2-digit",
                          day: "2-digit",
                          hour: "2-digit",
                          minute: "2-digit",
                          second: "2-digit",
                        })}
                      </div>
                    </td>
                    <td>
                      <div className="text-sm" dir="ltr">
                        {new Date(user.updatedAt).toLocaleString("fa-IR", {
                          year: "numeric",
                          month: "2-digit",
                          day: "2-digit",
                          hour: "2-digit",
                          minute: "2-digit",
                          second: "2-digit",
                        })}
                      </div>
                    </td>
                    <td>
                      <button
                        onClick={() => toggleStatus.mutate(user.id)}
                        disabled={toggleStatus.isPending}
                        className={`btn btn-sm gap-2 min-w-[95px] ${
                          user.isActive ? "btn-error" : "btn-success"
                        }`}
                      >
                        {toggleStatus.isPending ? (
                          <span className="loading loading-spinner loading-xs"></span>
                        ) : user.isActive ? (
                          <>
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
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                              />
                            </svg>
                            غیرفعال
                          </>
                        ) : (
                          <>
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
                                d="M5 13l4 4L19 7"
                              />
                            </svg>
                            فعال
                          </>
                        )}
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      ) : (
        <div className="card bg-base-100 shadow-xl">
          <div className="card-body items-center text-center py-16">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              className="h-24 w-24 text-base-300 mb-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={1.5}
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
            <h3 className="text-2xl font-bold mb-2">هیچ کاربری یافت نشد</h3>
            <p className="text-base-content/60">اولین کاربر سیستم باشید</p>
          </div>
        </div>
      )}
    </div>
  );
}

export default Users;
