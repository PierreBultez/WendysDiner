<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Order;
use App\Models\Expense;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

new #[Layout('components.layouts.admin')] #[Title('Tableau de Bord')] class extends Component {
    public $period = 'month'; // 'week', 'month', 'year', 'custom'
    public $customStart;
    public $customEnd;
    public $stats = [];

    public function mount()
    {
        // Default to current month
        $this->customStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->customEnd = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Set locale for French dates
        Carbon::setLocale('fr');

        $this->updateStats();
    }

    public function updatedPeriod()
    {
        $this->updateStats();
    }

    public function updatedCustomStart()
    {
        $this->updateStats();
    }

    public function updatedCustomEnd()
    {
        $this->updateStats();
    }

    public function updateStats()
    {
        $start = null;
        $end = null;
        $groupBy = 'day'; // 'day' or 'month'

        // 1. Determine Date Range
        if ($this->period === 'week') {
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
            $groupBy = 'day';
        } elseif ($this->period === 'month') {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $groupBy = 'day';
        } elseif ($this->period === 'year') {
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now()->endOfYear();
            $groupBy = 'month';
        } elseif ($this->period === 'custom') {
            $start = Carbon::parse($this->customStart)->startOfDay();
            $end = Carbon::parse($this->customEnd)->endOfDay();

            // Adapt granularity based on duration
            // If > 60 days, switch to monthly view for better readability
            if ($start->diffInDays($end) > 60) {
                $groupBy = 'month';
            } else {
                $groupBy = 'day';
            }
        }

        // 2. Fetch Orders (Revenue)
        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'annulée')
            ->get();

        // 3. Fetch Expenses
        $expenses = Expense::whereBetween('expense_date', [$start, $end])->get();

        // 4. Process Data for Chart
        $labels = [];
        $revenueValues = [];
        $expenseValues = [];

        if ($groupBy === 'month') {
            // Group by Year-Month
            $groupedOrders = $orders->groupBy(fn($order) => $order->created_at->format('Y-m'));
            $groupedExpenses = $expenses->groupBy(fn($expense) => $expense->expense_date->format('Y-m'));

            // Period with 1 month interval
            $periodRange = CarbonPeriod::create($start, '1 month', $end);

            foreach ($periodRange as $date) {
                $key = $date->format('Y-m');
                // Label ex: "Janv", "Févr"
                $labels[] = ucfirst($date->translatedFormat('M'));
                $revenueValues[] = $groupedOrders->has($key) ? $groupedOrders->get($key)->sum('total_amount') : 0;
                $expenseValues[] = $groupedExpenses->has($key) ? $groupedExpenses->get($key)->sum('amount') : 0;
            }
        } else {
            // Group by Day (Year-Month-Day)
            $groupedOrders = $orders->groupBy(fn($order) => $order->created_at->format('Y-m-d'));
            $groupedExpenses = $expenses->groupBy(fn($expense) => $expense->expense_date->format('Y-m-d'));

            $periodRange = CarbonPeriod::create($start, $end);

            foreach ($periodRange as $date) {
                $key = $date->format('Y-m-d');
                // Label ex: "10/01"
                $labels[] = $date->format('d/m');
                $revenueValues[] = $groupedOrders->has($key) ? $groupedOrders->get($key)->sum('total_amount') : 0;
                $expenseValues[] = $groupedExpenses->has($key) ? $groupedExpenses->get($key)->sum('amount') : 0;
            }
        }

        $totalRevenue = array_sum($revenueValues);
        $totalExpenses = array_sum($expenseValues);

        $this->stats = [
            'labels' => $labels,
            'revenue_values' => $revenueValues,
            'expense_values' => $expenseValues,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $totalRevenue - $totalExpenses,
        ];

        $this->dispatch('stats-updated', stats: $this->stats);
    }
}; ?>

<div class="space-y-8">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">Tableau de Bord</h1>
            <p class="text-zinc-500 dark:text-zinc-400">Aperçu de l'activité et des performances.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
             <flux:select wire:model.live="period" class="w-full sm:w-40">
                <flux:select.option value="week">Cette semaine</flux:select.option>
                <flux:select.option value="month">Ce mois ci</flux:select.option>
                <flux:select.option value="year">Cette année</flux:select.option>
                <flux:select.option value="custom">Personnalisé</flux:select.option>
            </flux:select>

            @if($period === 'custom')
                <div class="flex gap-2">
                    <flux:input type="date" wire:model.live="customStart" />
                    <flux:input type="date" wire:model.live="customEnd" />
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-card class="p-6">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Chiffre d'Affaires</h3>
            <p class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ number_format($this->stats['total_revenue'], 2, ',', ' ') }} €
            </p>
        </x-card>

        <x-card class="p-6">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Dépenses Totales</h3>
            <p class="mt-2 text-3xl font-semibold text-red-600 dark:text-red-400">
                {{ number_format($this->stats['total_expenses'], 2, ',', ' ') }} €
            </p>
        </x-card>

        <x-card class="p-6">
            <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Bénéfice Net</h3>
            <p @class(['mt-2 text-3xl font-semibold', 'text-green-600 dark:text-green-400' => $this->stats['net_profit'] >= 0, 'text-red-600 dark:text-red-400' => $this->stats['net_profit'] < 0])>
                {{ number_format($this->stats['net_profit'], 2, ',', ' ') }} €
            </p>
        </x-card>
    </div>

    <!-- Chart -->
    <x-card class="p-6">
        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-6">Évolution Financière</h3>
        <div
            class="relative h-80 w-full"
            x-data="{
                chart: null,
                init() {
                    this.renderChart(@js($this->stats));
                    Livewire.on('stats-updated', ({ stats }) => {
                        if (stats) {
                            this.renderChart(stats);
                        }
                    });
                },
                renderChart(data) {
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    const ctx = this.$refs.canvas.getContext('2d');

                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Chiffre d\'Affaires (€)',
                                    data: data.revenue_values,
                                    borderColor: '#10B981', // Green
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3
                                },
                                {
                                    label: 'Dépenses (€)',
                                    data: data.expense_values,
                                    borderColor: '#EF4444', // Red
                                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(161, 161, 170, 0.1)' }
                                },
                                x: {
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                }
            }"
            wire:ignore
        >
            <canvas x-ref="canvas"></canvas>
        </div>
    </x-card>
</div>
