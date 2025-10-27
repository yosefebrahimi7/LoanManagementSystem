import { useState, useRef, useEffect } from "react";
import moment from "moment-jalaali";

interface PersianDatePickerProps {
  value: string;
  onChange: (date: string) => void;
  minDate?: string;
  placeholder?: string;
  className?: string;
}

export default function PersianDatePicker({
  value,
  onChange,
  minDate,
  placeholder = "تاریخ را انتخاب کنید",
  className = "",
}: PersianDatePickerProps) {
  const [showPicker, setShowPicker] = useState(false);
  const pickerRef = useRef<HTMLDivElement>(null);
  const [calendar, setCalendar] = useState<{
    year: number;
    month: number;
  }>({
    year: moment().jYear(),
    month: moment().jMonth() + 1,
  });
  const [selectedDate, setSelectedDate] = useState<{
    year: number;
    month: number;
    day: number;
  } | null>(null);

  useEffect(() => {
    if (value) {
      const m = moment(value, "YYYY-MM-DD");
      if (m.isValid()) {
        setSelectedDate({
          year: m.jYear(),
          month: m.jMonth() + 1,
          day: m.jDate(),
        });
        setCalendar({
          year: m.jYear(),
          month: m.jMonth() + 1,
        });
      }
    }
  }, [value]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (
        pickerRef.current &&
        !pickerRef.current.contains(event.target as Node)
      ) {
        setShowPicker(false);
      }
    };

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, []);

  const getMonthDays = () => {
    const daysInMonth = moment.jDaysInMonth(calendar.year, calendar.month);
    const monthStartDay = moment(
      `${calendar.year}/${calendar.month}/1`,
      "jYYYY/jMM/jDD"
    ).day();

    const days = [];

    for (let i = 0; i < monthStartDay; i++) {
      days.push(null);
    }

    for (let day = 1; day <= daysInMonth; day++) {
      days.push(day);
    }

    return days;
  };

  const handleDayClick = (day: number) => {
    const newSelectedDate = {
      year: calendar.year,
      month: calendar.month,
      day,
    };

    const jalaliMoment = moment(
      `${calendar.year}/${calendar.month}/${day}`,
      "jYYYY/jMM/jDD"
    );
    const gregorianDate = jalaliMoment.format("YYYY-MM-DD");

    if (minDate && gregorianDate < minDate) {
      return;
    }

    setSelectedDate(newSelectedDate);
    onChange(gregorianDate);
    setShowPicker(false);
  };

  const navigateMonth = (direction: "prev" | "next") => {
    setCalendar((prev) => {
      if (direction === "prev") {
        if (prev.month === 1) {
          return { year: prev.year - 1, month: 12 };
        }
        return { year: prev.year, month: prev.month - 1 };
      } else {
        if (prev.month === 12) {
          return { year: prev.year + 1, month: 1 };
        }
        return { year: prev.year, month: prev.month + 1 };
      }
    });
  };

  const monthNames = [
    "فروردین",
    "اردیبهشت",
    "خرداد",
    "تیر",
    "مرداد",
    "شهریور",
    "مهر",
    "آبان",
    "آذر",
    "دی",
    "بهمن",
    "اسفند",
  ];

  const weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"];

  const displayValue = selectedDate
    ? `${selectedDate.day} ${monthNames[selectedDate.month - 1]} ${selectedDate.year}`
    : "";

  const isDateDisabled = (day: number) => {
    if (!minDate) return false;
    const jalaliMoment = moment(
      `${calendar.year}/${calendar.month}/${day}`,
      "jYYYY/jMM/jDD"
    );
    const gregorianDate = jalaliMoment.format("YYYY-MM-DD");
    return gregorianDate < minDate;
  };

  const days = getMonthDays();
  const rows = [];
  for (let i = 0; i < days.length; i += 7) {
    rows.push(days.slice(i, i + 7));
  }

  return (
    <div className={`relative w-full ${className}`} ref={pickerRef}>
      <input
        type="text"
        value={displayValue}
        placeholder={placeholder}
        readOnly
        onClick={() => setShowPicker(!showPicker)}
        className="input input-bordered w-full cursor-pointer"
      />

      {showPicker && (
        <div className="absolute bottom-full left-0 mb-2 bg-base-100 border-2 border-base-300 rounded-lg shadow-lg p-4 z-50 w-80 rtl">
          <div className="flex items-center justify-between mb-4">
            <button
              type="button"
              onClick={() => navigateMonth("next")}
              className="btn btn-sm btn-circle btn-ghost"
            >
              <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M15 19l-7-7 7-7"
                />
              </svg>
            </button>
            <span className="font-bold text-lg">
              {monthNames[calendar.month - 1]} {calendar.year}
            </span>
            <button
              type="button"
              onClick={() => navigateMonth("prev")}
              className="btn btn-sm btn-circle btn-ghost"
            >
              <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </button>
          </div>

          <div className="grid grid-cols-7 gap-1 mb-2">
            {weekDays.map((day) => (
              <div
                key={day}
                className="text-center text-sm font-bold opacity-70 py-1"
              >
                {day}
              </div>
            ))}
          </div>

          <div className="grid grid-cols-7 gap-1">
            {rows.map((row, rowIndex) =>
              row.map((day, dayIndex) => {
                if (day === null) {
                  return <div key={`${rowIndex}-${dayIndex}`}></div>;
                }

                const isSelected =
                  selectedDate &&
                  selectedDate.year === calendar.year &&
                  selectedDate.month === calendar.month &&
                  selectedDate.day === day;

                const disabled = isDateDisabled(day);

                return (
                  <button
                    key={day}
                    type="button"
                    onClick={() => !disabled && handleDayClick(day)}
                    disabled={disabled}
                    className={`
                      btn btn-sm btn-circle
                      ${isSelected ? "btn-primary" : "btn-ghost"}
                      ${disabled ? "text-base-content/30 cursor-not-allowed" : ""}
                    `}
                  >
                    {day}
                  </button>
                );
              })
            )}
          </div>
        </div>
      )}
    </div>
  );
}
