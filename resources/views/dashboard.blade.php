<x-app-layout>
    @push('styles')
        <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet" type="text/css">
    @endpush

    <x-slot name="breadcrumbs">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-item active">Dashboard</span>
    </x-slot>

    <!-- Stats Section -->
    <div class="row mb-4">
        <!-- Total Users -->
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="dashboard-card">
                <div class="stats-icon blue">
                    <i class="ph-users"></i>
                </div>
                <h6 class="stats-title">Total Users</h6>
                <div class="stats-value">{{ number_format($totalUsers ?? 0) }}</div>
            </div>
        </div>
        
        <!-- Active Users -->
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="dashboard-card">
                <div class="stats-icon green">
                    <i class="ph-check-circle"></i>
                </div>
                <h6 class="stats-title">Active Users</h6>
                <div class="stats-value">{{ number_format($activeUsers ?? 0) }}</div>
            </div>
        </div>

        <!-- New Users Today -->
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="dashboard-card">
                <div class="stats-icon purple">
                    <i class="ph-user-plus"></i>
                </div>
                <h6 class="stats-title">New Users (Today)</h6>
                <div class="stats-value">{{ number_format($newUsersToday ?? 0) }}</div>
            </div>
        </div>

        <!-- System Logs/Alerts (Placeholder) -->
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card">
                <div class="stats-icon orange">
                    <i class="ph-bell"></i>
                </div>
                <h6 class="stats-title">Recent Activities</h6>
                <div class="stats-value">{{ isset($recentActivities) ? $recentActivities->count() : 0 }}</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-lg-8">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Growth Trends</h5>
                    <!-- Optional dropdown or filter -->
                </div>
                <div id="growthChart" style="height: 350px;"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">User Distribution</h5>
                </div>
                <div id="distributionChart" style="height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Recent Data Section -->
    <div class="row">
        <div class="col-lg-12">
            <div class="dashboard-card">
                <div class="chart-header">
                    <h5 class="chart-title">Recent Registered Users</h5>
                </div>
                <div class="table-responsive">
                    <table class="table recent-users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentUsers) && $recentUsers->isNotEmpty())
                                @foreach($recentUsers as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $user->image }}" class="user-avatar-small" alt="">
                                            <span class="fw-semibold">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->roles->pluck('name')->join(', ') }}</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="status-badge status-active">Active</span>
                                        @else
                                            <span class="status-badge status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No recent new users found.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('assets/js/vendor/visualization/echarts/echarts.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                /* ---------------- Growth Chart ---------------- */
                const growthChart = echarts.init(document.getElementById('growthChart'));

                growthChart.setOption({
                    tooltip: { trigger: 'axis' },
                    grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: @json($growthLabels)
                    },
                    yAxis: { type: 'value' },
                    series: [{
                        name: 'New Users',
                        type: 'line',
                        smooth: true,
                        itemStyle: { color: '#0d6efd' },
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                                { offset: 0, color: 'rgba(13, 110, 253, 0.5)' },
                                { offset: 1, color: 'rgba(13, 110, 253, 0.0)' }
                            ])
                        },
                        data: @json($growthData)
                    }]
                });

                /* ---------------- Distribution Chart ---------------- */
                const distributionChart = echarts.init(document.getElementById('distributionChart'));

                @php
                    $chartData = $rolesStats->map(fn($role) => ['value' => $role->users_count, 'name' => ucfirst($role->name)]);
                @endphp
                distributionChart.setOption({
                    tooltip: { trigger: 'item' },
                    legend: { bottom: '5%', left: 'center' },
                    series: [{
                        name: 'Roles',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: {
                            borderRadius: 10,
                            borderColor: '#fff',
                            borderWidth: 2
                        },
                        label: { show: false },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: 18,
                                fontWeight: 'bold'
                            }
                        },
                        labelLine: { show: false },
                        data: @json($chartData)
                    }]
                });

                /* ---------------- Resize ---------------- */
                window.addEventListener('resize', () => {
                    growthChart.resize();
                    distributionChart.resize();
                });
            });
        </script>
    @endpush
</x-app-layout>
