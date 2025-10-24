import useAuth from "../stores/auth";

function Profile() {
  const { user } = useAuth();

  return (
    <div className="container mx-auto p-8 max-w-2xl">
      <div className="card bg-base-100 shadow-xl">
        <div className="card-body">
          <div className="flex items-center gap-6 mb-6 pb-6 border-b border-base-300">
            <div className="w-20 h-20 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-primary-content text-3xl font-bold shadow-xl">
              {user?.firstName?.charAt(0)}
            </div>
            <div>
              <h1 className="text-3xl font-bold">
                {user?.firstName} {user?.lastName}
              </h1>
              <p className="text-base-content/60">{user?.email}</p>
            </div>
          </div>

          <div className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">نام</span>
                </label>
                <input
                  type="text"
                  value={user?.firstName || ""}
                  className="input input-bordered"
                  readOnly
                />
              </div>

              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">نام خانوادگی</span>
                </label>
                <input
                  type="text"
                  value={user?.lastName || ""}
                  className="input input-bordered"
                  readOnly
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">ایمیل</span>
                </label>
                <input
                  type="email"
                  value={user?.email || ""}
                  className="input input-bordered"
                  readOnly
                  dir="ltr"
                />
              </div>

              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">وضعیت حساب</span>
                </label>
                <div className="flex items-center gap-2 px-4 py-3 rounded-lg bg-base-200">
                  {user?.isActive ? (
                    <>
                      <div className="w-3 h-3 rounded-full bg-success animate-pulse"></div>
                      <span className="text-success font-medium">فعال</span>
                    </>
                  ) : (
                    <>
                      <div className="w-3 h-3 rounded-full bg-error"></div>
                      <span className="text-error font-medium">غیرفعال</span>
                    </>
                  )}
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">تاریخ عضویت</span>
                </label>
                <input
                  type="text"
                  value={
                    user?.createdAt
                      ? new Date(user.createdAt).toLocaleString("fa-IR", {
                          year: "numeric",
                          month: "2-digit",
                          day: "2-digit",
                          hour: "2-digit",
                          minute: "2-digit",
                          second: "2-digit",
                        })
                      : ""
                  }
                  className="input input-bordered"
                  readOnly
                  dir="ltr"
                />
              </div>

              <div className="form-control">
                <label className="label">
                  <span className="label-text font-semibold">
                    آخرین بروزرسانی
                  </span>
                </label>
                <input
                  type="text"
                  value={
                    user?.updatedAt
                      ? new Date(user.updatedAt).toLocaleString("fa-IR", {
                          year: "numeric",
                          month: "2-digit",
                          day: "2-digit",
                          hour: "2-digit",
                          minute: "2-digit",
                          second: "2-digit",
                        })
                      : ""
                  }
                  className="input input-bordered"
                  dir="ltr"
                  readOnly
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

export default Profile;
