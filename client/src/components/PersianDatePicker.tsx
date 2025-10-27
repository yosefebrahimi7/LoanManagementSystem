import { useState, useRef, useEffect } from 'react';
import { DayPicker } from 'react-day-picker';
import { faIR } from 'date-fns-jalali/locale';
import moment from 'moment-jalaali';
import 'react-day-picker/style.css';

// Custom Persian locale with full names
const persianLocale: any = {
  ...faIR,
  localize: {
    ...faIR.localize,
    month: (monthIndex: number) => {
      const months = [
        'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
        'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
      ];
      return months[monthIndex];
    },
    day: (dayIndex: number) => {
      const days = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
      return days[dayIndex];
    },
  },
  formatLong: {
    ...faIR.formatLong,
  },
  options: {
    ...faIR.options,
    weekStartsOn: 7, // Saturday (شنبه)
  },
};

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
  placeholder = 'تاریخ را انتخاب کنید',
  className = '',
}: PersianDatePickerProps) {
  const [showPicker, setShowPicker] = useState(false);
  const pickerRef = useRef<HTMLDivElement>(null);
  const [selectedDate, setSelectedDate] = useState<Date | undefined>(
    value ? moment(value, 'YYYY-MM-DD').toDate() : undefined
  );

  useEffect(() => {
    if (value) {
      const m = moment(value, 'YYYY-MM-DD');
      if (m.isValid()) {
        setSelectedDate(m.toDate());
      }
    }
  }, [value]);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (pickerRef.current && !pickerRef.current.contains(event.target as Node)) {
        setShowPicker(false);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSelect = (date: Date | undefined) => {
    setSelectedDate(date);
    if (date) {
      // Convert to Gregorian format for server
      const gregorianDate = moment(date).format('YYYY-MM-DD');
      onChange(gregorianDate);
    } else {
      onChange('');
    }
    setShowPicker(false);
  };

  const getDisplayValue = () => {
    if (!selectedDate) return '';
    
    const m = moment(selectedDate);
    const monthNames = [
      'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
      'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    ];
    
    const day = m.jDate();
    const month = m.jMonth();
    const year = m.jYear();
    
    // Convert numbers to Persian digits
    const toPersianDigits = (num: number) => {
      const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
      return num.toString().split('').map(d => persianDigits[parseInt(d)]).join('');
    };
    
    return `${toPersianDigits(day)} ${monthNames[month]} ${toPersianDigits(year)}`;
  };

  const displayValue = getDisplayValue();

  const minDateObj = minDate ? moment(minDate, 'YYYY-MM-DD').toDate() : undefined;

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
        <div className="absolute bottom-full left-0 mb-2 bg-base-100 border-2 border-base-300 rounded-lg shadow-lg p-4 z-50">
          <div className="react-day-picker rtl">
            <DayPicker
              mode="single"
              selected={selectedDate}
              onSelect={handleSelect}
              locale={persianLocale}
              className="rdp"
              dir="rtl"
            />
          </div>
        </div>
      )}
    </div>
  );
}
