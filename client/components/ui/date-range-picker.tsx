import * as React from "react"
import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Calendar } from "lucide-react"

interface DateRange {
  startDate: string;
  endDate: string;
}

interface DatePickerWithRangeProps {
  className?: string;
  onDateRangeChange?: (range: DateRange) => void;
  value?: DateRange;
}

export function DatePickerWithRange({ className, onDateRangeChange, value }: DatePickerWithRangeProps) {
  const [isOpen, setIsOpen] = useState(false);
  const [startDate, setStartDate] = useState(value?.startDate || new Date().toISOString().split('T')[0]);
  const [endDate, setEndDate] = useState(value?.endDate || new Date().toISOString().split('T')[0]);

  const handleApply = () => {
    if (onDateRangeChange) {
      onDateRangeChange({ startDate, endDate });
    }
    setIsOpen(false);
  };

  const formatDateRange = () => {
    if (value?.startDate && value?.endDate) {
      const start = new Date(value.startDate).toLocaleDateString();
      const end = new Date(value.endDate).toLocaleDateString();
      return start === end ? start : `${start} - ${end}`;
    }
    return "Select date range";
  };

  return (
    <Dialog open={isOpen} onOpenChange={setIsOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" className={className}>
          <Calendar className="mr-2 h-4 w-4" />
          {formatDateRange()}
        </Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Select Date Range</DialogTitle>
        </DialogHeader>
        <div className="space-y-4">
          <div>
            <Label htmlFor="start-date">Start Date</Label>
            <Input
              id="start-date"
              type="date"
              value={startDate}
              onChange={(e) => setStartDate(e.target.value)}
            />
          </div>
          <div>
            <Label htmlFor="end-date">End Date</Label>
            <Input
              id="end-date"
              type="date"
              value={endDate}
              onChange={(e) => setEndDate(e.target.value)}
              min={startDate}
            />
          </div>
          <div className="flex gap-2">
            <Button onClick={handleApply} className="flex-1">
              Apply Range
            </Button>
            <Button variant="outline" onClick={() => setIsOpen(false)} className="flex-1">
              Cancel
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  )
}
