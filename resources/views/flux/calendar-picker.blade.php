@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'label' => null,
    'description' => null,
    'placeholder' => 'Select date & time...',
    'required' => false,
    'value' => null,
    'size' => null,
])

@php
    $wireModel = $attributes->wire('model');
    $wireModelName = $wireModel?->value();
@endphp

<flux:with-field :$attributes :$name :$label :$description>
    <div
        x-data="{
            open: false,
            wireModel: '{{ $wireModelName ?? '' }}',
            selectedDate: null,
            hour: '06',
            minute: '00',
            period: 'PM',
            viewYear: new Date().getFullYear(),
            viewMonth: new Date().getMonth(),
            isoValue: '',
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            dayNames: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

            init() {
                let initial = '{{ $value ?? '' }}';
                if (this.wireModel && typeof $wire !== 'undefined') {
                    let wVal = $wire.get(this.wireModel);
                    if (wVal) initial = wVal;
                }
                if (initial) {
                    this.parseValue(initial);
                }

                if (this.wireModel && typeof $wire !== 'undefined') {
                    this.$watch('$wire.' + this.wireModel, (newVal) => {
                        if (newVal !== this.isoValue) {
                            this.parseValue(newVal);
                        }
                    });
                }
            },

            parseValue(val) {
                if (!val || typeof val !== 'string') {
                    this.selectedDate = null;
                    this.isoValue = '';
                    return;
                }
                const dateMatch = val.match(/^(\d{4})-(\d{2})-(\d{2})/);
                if (!dateMatch) return;

                const y = parseInt(dateMatch[1], 10);
                const m = parseInt(dateMatch[2], 10) - 1;
                const d = parseInt(dateMatch[3], 10);

                this.selectedDate = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                this.viewYear = y;
                this.viewMonth = m;

                const timeMatch = val.match(/[T\s](\d{1,2}):(\d{2})/);
                if (timeMatch) {
                    let h24 = parseInt(timeMatch[1], 10);
                    let min = parseInt(timeMatch[2], 10);
                    this.period = h24 >= 12 ? 'PM' : 'AM';
                    let h12 = h24 % 12;
                    this.hour = String(h12 === 0 ? 12 : h12).padStart(2, '0');
                    this.minute = String(min).padStart(2, '0');
                }
                this.updateIso();
            },

            updateIso() {
                if (!this.selectedDate) {
                    this.isoValue = '';
                    return;
                }
                let h = parseInt(this.hour, 10);
                if (this.period === 'AM') {
                    h = h === 12 ? 0 : h;
                } else {
                    h = h === 12 ? 12 : h + 12;
                }
                const hStr = String(h).padStart(2, '0');
                const mStr = String(this.minute).padStart(2, '0');
                this.isoValue = `${this.selectedDate}T${hStr}:${mStr}`;
            },

            updateValue() {
                this.updateIso();
                if (this.wireModel && typeof $wire !== 'undefined') {
                    $wire.set(this.wireModel, this.isoValue);
                }
                if (this.$refs.realInput) {
                    this.$refs.realInput.value = this.isoValue;
                    this.$refs.realInput.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.realInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            },

            selectDay(day, month, year) {
                this.selectedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                this.viewMonth = month;
                this.viewYear = year;
                this.updateValue();
            },

            setTimePreset(h, m, p) {
                this.hour = String(h).padStart(2, '0');
                this.minute = String(m).padStart(2, '0');
                this.period = p;
                if (!this.selectedDate) {
                    const today = new Date();
                    this.selectedDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
                }
                this.updateValue();
            },

            prevMonth() {
                if (this.viewMonth === 0) {
                    this.viewMonth = 11;
                    this.viewYear--;
                } else {
                    this.viewMonth--;
                }
            },

            nextMonth() {
                if (this.viewMonth === 11) {
                    this.viewMonth = 0;
                    this.viewYear++;
                } else {
                    this.viewMonth++;
                }
            },

            goToToday() {
                const today = new Date();
                this.viewYear = today.getFullYear();
                this.viewMonth = today.getMonth();
                this.selectDay(today.getDate(), today.getMonth(), today.getFullYear());
            },

            clear() {
                this.selectedDate = null;
                this.isoValue = '';
                if (this.wireModel && typeof $wire !== 'undefined') {
                    $wire.set(this.wireModel, '');
                }
                if (this.$refs.realInput) {
                    this.$refs.realInput.value = '';
                    this.$refs.realInput.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.realInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            },

            get displayValue() {
                if (!this.selectedDate) return '';
                const parts = this.selectedDate.split('-');
                const y = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10) - 1;
                const d = parseInt(parts[2], 10);
                const dt = new Date(y, m, d);
                const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                const dayName = days[dt.getDay()];
                const monthName = months[m];
                return `${dayName}, ${monthName} ${d}, ${y} at ${parseInt(this.hour, 10)}:${this.minute} ${this.period}`;
            },

            get timeDisplay() {
                return `${parseInt(this.hour, 10)}:${this.minute} ${this.period}`;
            },

            get calendarDays() {
                const year = this.viewYear;
                const month = this.viewMonth;
                const firstDayIndex = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                const prevMonthDays = new Date(year, month, 0).getDate();

                const today = new Date();
                const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

                const days = [];

                // Prev month padding
                for (let i = firstDayIndex - 1; i >= 0; i--) {
                    const d = prevMonthDays - i;
                    const prevMonth = month === 0 ? 11 : month - 1;
                    const prevYear = month === 0 ? year - 1 : year;
                    const dateStr = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    days.push({
                        day: d,
                        month: prevMonth,
                        year: prevYear,
                        isCurrentMonth: false,
                        isToday: dateStr === todayStr,
                        isSelected: dateStr === this.selectedDate,
                        dateStr: dateStr
                    });
                }

                // Current month days
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    days.push({
                        day: d,
                        month: month,
                        year: year,
                        isCurrentMonth: true,
                        isToday: dateStr === todayStr,
                        isSelected: dateStr === this.selectedDate,
                        dateStr: dateStr
                    });
                }

                // Next month padding
                const totalCells = days.length > 35 ? 42 : 35;
                const remaining = totalCells - days.length;
                for (let d = 1; d <= remaining; d++) {
                    const nextMonth = month === 11 ? 0 : month + 1;
                    const nextYear = month === 11 ? year + 1 : year;
                    const dateStr = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    days.push({
                        day: d,
                        month: nextMonth,
                        year: nextYear,
                        isCurrentMonth: false,
                        isToday: dateStr === todayStr,
                        isSelected: dateStr === this.selectedDate,
                        dateStr: dateStr
                    });
                }

                return days;
            }
        }"
        class="relative w-full"
        style="position: relative;"
        @keydown.escape.window="open = false"
    >
        {{-- Hidden Input for form submission & livewire bindings --}}
        <input
            type="text"
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
            x-ref="realInput"
            @isset($name) name="{{ $name }}" @endisset
            :value="isoValue"
        />

        {{-- Dropdown Trigger (Flux styled input button) --}}
        <button
            type="button"
            @click="open = !open"
            class="w-full h-10 px-3 flex items-center justify-between gap-2 rounded-lg border border-zinc-200 border-b-zinc-300/80 dark:border-white/10 bg-white dark:bg-white/10 text-left shadow-xs transition hover:border-zinc-300 dark:hover:border-white/20 focus:outline-none focus:ring-2 focus:ring-red-600/50 cursor-pointer"
            :class="{ 'ring-2 ring-red-600/50 border-red-500': open }"
        >
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <flux:icon.calendar-days class="size-4 shrink-0 text-red-600 dark:text-red-400" />
                <span
                    x-show="displayValue"
                    x-text="displayValue"
                    class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100"
                ></span>
                <span
                    x-show="!displayValue"
                    class="truncate text-sm text-zinc-400 dark:text-zinc-500"
                >
                    {{ $placeholder }}
                </span>
            </div>

            <div class="flex items-center gap-1 shrink-0">
                <span
                    role="button"
                    tabindex="0"
                    x-show="selectedDate"
                    @click.stop="clear()"
                    title="Clear date"
                    class="p-1 rounded-md text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition cursor-pointer inline-flex items-center justify-center"
                >
                    <flux:icon.x-mark class="size-3.5" />
                </span>
                <flux:icon.chevron-up-down class="size-4 text-zinc-400" />
            </div>
        </button>

        {{-- Calendar Popover Dropdown --}}
        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-95"
            class="absolute top-full left-0 mt-2 z-50 w-full sm:w-[360px] rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-4 shadow-2xl ring-1 ring-black/5 dark:ring-white/10"
            style="position: absolute; top: calc(100% + 0.5rem); left: 0; z-index: 50;"
        >
            {{-- Month & Year Navigation Header --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-1.5">
                    <span
                        class="text-sm font-bold text-zinc-900 dark:text-white"
                        x-text="monthNames[viewMonth] + ' ' + viewYear"
                    ></span>
                </div>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        @click="goToToday()"
                        class="px-2 py-1 text-xs font-semibold rounded-md text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition mr-1"
                    >
                        Today
                    </button>
                    <button
                        type="button"
                        @click="prevMonth()"
                        class="size-7 flex items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                        title="Previous Month"
                    >
                        <flux:icon.chevron-left class="size-4" />
                    </button>
                    <button
                        type="button"
                        @click="nextMonth()"
                        class="size-7 flex items-center justify-center rounded-lg text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition"
                        title="Next Month"
                    >
                        <flux:icon.chevron-right class="size-4" />
                    </button>
                </div>
            </div>

            {{-- Day of Week Labels --}}
            <div class="grid grid-cols-7 mb-1 text-center">
                <template x-for="name in dayNames" :key="name">
                    <div class="text-[11px] font-bold text-zinc-400 uppercase py-1" x-text="name"></div>
                </template>
            </div>

            {{-- Days Grid --}}
            <div class="grid grid-cols-7 gap-1">
                <template x-for="(cell, idx) in calendarDays" :key="cell.dateStr + '_' + idx">
                    <button
                        type="button"
                        @click="selectDay(cell.day, cell.month, cell.year)"
                        class="h-9 w-full flex items-center justify-center text-xs font-semibold rounded-lg transition relative"
                        :class="{
                            'bg-red-700 text-white font-bold shadow-sm hover:bg-red-800 ring-2 ring-red-500/40': cell.isSelected,
                            'border border-amber-500 text-amber-600 dark:text-amber-400 font-bold': cell.isToday && !cell.isSelected,
                            'text-zinc-300 dark:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800/40': !cell.isCurrentMonth && !cell.isSelected,
                            'text-zinc-800 dark:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800': cell.isCurrentMonth && !cell.isSelected && !cell.isToday
                        }"
                    >
                        <span x-text="cell.day"></span>
                    </button>
                </template>
            </div>

            {{-- Divider --}}
            <div class="my-3 border-t border-zinc-200 dark:border-zinc-800"></div>

            {{-- Time Selection Section --}}
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 flex items-center gap-1.5">
                        <flux:icon.clock class="size-3.5 text-zinc-400" />
                        Event Time
                    </span>
                    <span class="text-xs font-bold text-red-600 dark:text-red-400" x-text="timeDisplay"></span>
                </div>

                {{-- Time Steppers / Selects --}}
                <div class="flex items-center gap-2">
                    {{-- Hour --}}
                    <div class="flex-1">
                        <select
                            x-model="hour"
                            @change="updateValue()"
                            class="w-full h-8 px-2 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-red-600"
                        >
                            @foreach (range(1, 12) as $h)
                                <option value="{{ sprintf('%02d', $h) }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>

                    <span class="text-zinc-400 font-bold">:</span>

                    {{-- Minute --}}
                    <div class="flex-1">
                        <select
                            x-model="minute"
                            @change="updateValue()"
                            class="w-full h-8 px-2 text-xs font-medium rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-red-600"
                        >
                            @foreach (['00', '05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55'] as $m)
                                <option value="{{ $m }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- AM / PM Segmented Control --}}
                    <div class="flex rounded-lg border border-zinc-200 dark:border-zinc-700 p-0.5 bg-zinc-100 dark:bg-zinc-800/80">
                        <button
                            type="button"
                            @click="period = 'AM'; updateValue()"
                            class="px-2.5 py-1 text-xs font-bold rounded-md transition"
                            :class="period === 'AM' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'"
                        >
                            AM
                        </button>
                        <button
                            type="button"
                            @click="period = 'PM'; updateValue()"
                            class="px-2.5 py-1 text-xs font-bold rounded-md transition"
                            :class="period === 'PM' ? 'bg-red-700 text-white shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white'"
                        >
                            PM
                        </button>
                    </div>
                </div>

                {{-- Quick Presets --}}
                <div class="flex items-center gap-1.5 flex-wrap pt-1">
                    <span class="text-[10px] uppercase font-bold text-zinc-400 mr-1">Presets:</span>
                    <button
                        type="button"
                        @click="setTimePreset(10, '00', 'AM')"
                        class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                    >
                        10:00 AM
                    </button>
                    <button
                        type="button"
                        @click="setTimePreset(12, '00', 'PM')"
                        class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                    >
                        12:00 PM
                    </button>
                    <button
                        type="button"
                        @click="setTimePreset(6, '00', 'PM')"
                        class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                    >
                        6:00 PM
                    </button>
                    <button
                        type="button"
                        @click="setTimePreset(7, '00', 'PM')"
                        class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition"
                    >
                        7:00 PM
                    </button>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mt-4 pt-3 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                <button
                    type="button"
                    @click="clear()"
                    class="text-xs font-semibold text-zinc-500 hover:text-red-600 dark:hover:text-red-400 transition"
                >
                    Clear
                </button>
                <button
                    type="button"
                    @click="open = false"
                    class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-600 text-white text-xs font-bold shadow-xs transition"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</flux:with-field>
