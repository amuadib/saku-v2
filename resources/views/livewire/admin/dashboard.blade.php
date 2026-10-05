<div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <!-- Saldo Kas -->
        <flux:card>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Saldo Kas</h3>
                <flux:icon name="funnel" class="w-5 h-5 text-gray-500" />
            </div>
            <div class="relative h-64 w-full">
                <canvas id="kasChart"></canvas>
            </div>
        </flux:card>

        <!-- Tagihan -->
        <flux:card>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tagihan</h3>
                <flux:icon name="funnel" class="w-5 h-5 text-gray-500" />
            </div>
            <div class="relative h-64 w-full">
                <canvas id="tagihanChart"></canvas>
            </div>
        </flux:card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Grafik Transaksi Mingguan -->
        <flux:card>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Grafik Transaksi Mingguan</h3>
            <div class="relative h-64 w-full">
                <canvas id="transaksiChart"></canvas>
            </div>
        </flux:card>

        <!-- Rekap Transaksi Mingguan -->
        <flux:card>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Rekap Transaksi Mingguan</h3>
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Tanggal</flux:table.column>
                        <flux:table.column>Masuk</flux:table.column>
                        <flux:table.column>Keluar</flux:table.column>
                        <flux:table.column>Saldo</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @php
                            $totalMasuk = 0;
                            $totalKeluar = 0;
                            $totalSaldo = 0;
                        @endphp
                        @forelse($rekapTable as $row)
                            @php
                                $totalMasuk += $row['masuk'];
                                $totalKeluar += $row['keluar'];
                                $totalSaldo += $row['saldo'];
                            @endphp
                            <flux:table.row>
                                <flux:table.cell>{{ $row['tanggal'] }}</flux:table.cell>
                                <flux:table.cell>Rp {{ number_format($row['masuk'], 0, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>Rp {{ number_format($row['keluar'], 0, ',', '.') }}</flux:table.cell>
                                <flux:table.cell>Rp {{ number_format($row['saldo'], 0, ',', '.') }}</flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center">Belum ada data</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                        
                        <!-- Totals Row -->
                        <flux:table.row class="bg-gray-50 dark:bg-gray-800 font-semibold">
                            <flux:table.cell>Total</flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($totalMasuk, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($totalKeluar, 0, ',', '.') }}</flux:table.cell>
                            <flux:table.cell>Rp {{ number_format($totalSaldo, 0, ',', '.') }}</flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                </flux:table>
            </div>
        </flux:card>
    </div>

    @script
    <script>
        const initCharts = () => {
            const kasCanvas = document.getElementById('kasChart');
            if (!kasCanvas) return;

            const isAdmin = @json(auth()->user()->isAdmin());

            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            
            Chart.defaults.color = textColor;

            // Chart Colors
            const colors = [
                '#3b82f6', '#ec4899', '#10b981', '#f59e0b', '#ef4444', 
                '#8b5cf6', '#06b6d4', '#84cc16', '#64748b'
            ];

            // Helper to destroy existing chart instances before re-rendering
            const destroyChart = (id) => {
                const chart = Chart.getChart(id);
                if (chart) chart.destroy();
            };

            destroyChart('kasChart');
            new Chart(kasCanvas.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: @json($kasLabels),
                    datasets: [{
                        data: @json($kasValues),
                        backgroundColor: colors,
                        borderWidth: 1,
                        borderColor: isDarkMode ? '#1f2937' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            enabled: isAdmin,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) label += ': ';
                                    if (context.raw !== null) {
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            destroyChart('tagihanChart');
            new Chart(document.getElementById('tagihanChart').getContext('2d'), {
                type: 'pie',
                data: {
                    labels: @json($tagihanLabels),
                    datasets: [{
                        data: @json($tagihanValues),
                        backgroundColor: ['#10b981', '#8b5cf6', '#f59e0b', '#ef4444'],
                        borderWidth: 1,
                        borderColor: isDarkMode ? '#1f2937' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) label += ': ';
                                    if (context.raw !== null) {
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });

            destroyChart('transaksiChart');
            new Chart(document.getElementById('transaksiChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($chartDates),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: @json($chartMasuk),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Pengeluaran',
                            data: @json($chartKeluar),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) label += ': ';
                                    if (context.raw !== null) {
                                        label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: isDarkMode ? '#374151' : '#e5e7eb' },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        };

        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            script.onload = initCharts;
            document.head.appendChild(script);
        } else {
            initCharts();
        }
    </script>
    @endscript
</div>
